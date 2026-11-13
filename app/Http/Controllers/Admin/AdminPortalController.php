<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, AppNotification, LogisticsProvider, Order, Product, Rider, Seller, SellerOrder, User};
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Log, Mail};
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminPortalController extends Controller
{
    private function audit(string $action, string $description): void
    {
        DB::table('admin_audit_logs')->insert([
            'user_id' => Auth::id(), 'action' => $action, 'description' => $description,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function earnedOrders()
    {
        return SellerOrder::whereIn('status', ['delivered', 'completed'])
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'));
    }

    private function applicants()
    {
        return User::where('role', '!=', User::ROLE_ADMIN)
            ->where(fn ($q) => $q->whereNotNull('id_photo')->orWhere('status', 'pending')
                ->orWhereIn('id_status', ['pending', 'verified', 'rejected']));
    }

    public function dashboard(Request $request)
    {
        $stats = [
            'sales' => (clone $this->earnedOrders())->sum('subtotal_minor') / 100,
            'commission' => (clone $this->earnedOrders())->sum('commission_minor') / 100,
            'orders' => Order::count(),
            'pending' => $this->applicants()->where(fn ($q) => $q->where('id_status', 'pending')
                ->orWhere(fn ($q) => $q->where('status', 'pending')->where('id_status', '!=', 'rejected')))->count(),
            'pending_products' => Product::where('approval_status', Product::APPROVAL_PENDING)->count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'sellers' => $this->registeredStores()->count(),
            'couriers' => User::where('role', 'courier')->count(),
            'products' => Product::count(),
        ];
        $stores = $this->registeredStores()->with(['user', 'pickupAddress'])->withCount('products')->latest('id')->paginate(10)->withQueryString();
        $activity = DB::table('admin_audit_logs')->latest('id')->limit(8)->get();
        return view('admin.dashboard', compact('stats', 'stores', 'activity'));
    }

    private function registeredStores()
    {
        return Seller::where('status', 'approved')->whereHas('user', fn ($q) => $q
            ->where('role', 'seller')->whereIn('status', ['active', 'suspended', 'deactivated']));
    }

    public function stores(Request $request)
    {
        $query = $this->registeredStores()->with('user')->withCount('products');
        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim()->toString().'%';
            $query->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('slug', 'like', $search)
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search)->orWhere('email', 'like', $search)));
        }
        if (in_array($request->query('status'), ['active', 'suspended', 'deactivated'], true)) {
            $query->whereHas('user', fn ($q) => $q->where('status', $request->query('status')));
        }
        $stores = $query->latest('id')->paginate(15)->withQueryString();

        return view('admin.stores', compact('stores'));
    }

    public function updateStoreStatus(Request $request, int $id)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'suspended', 'deactivated'])]]);

        return DB::transaction(function () use ($request, $id, $data) {
            $store = $this->registeredStores()->findOrFail($id);
            $user = User::lockForUpdate()->findOrFail($store->user_id);
            $allowed = $data['status'] === 'active' ? ['suspended', 'deactivated'] : ['active'];
            abort_unless(in_array($user->status, $allowed, true), 422, 'This store status has changed. Refresh and try again.');

            return $this->updateUserStatus($request, $user->id);
        });
    }

    public function archiveStore(int $id)
    {
        return DB::transaction(function () use ($id) {
            $store = $this->registeredStores()->findOrFail($id);
            $user = User::lockForUpdate()->findOrFail($store->user_id);
            abort_unless(in_array($user->status, ['suspended', 'deactivated'], true), 422, 'Suspend or deactivate this store before archiving it.');

            return $this->archiveUser($user->id);
        });
    }

    public function removeStore(Request $request, int $id)
    {
        $data = $request->validate([
            'removal_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        return DB::transaction(function () use ($request, $id, $data) {
            $store = Seller::findOrFail($id);
            $user = User::lockForUpdate()->findOrFail($store->user_id);
            abort_if($user->isAdmin(), 422, 'Administrator access cannot be changed here.');

            $reason = trim($data['removal_reason'] ?? '') ?: 'Store removed by platform administration.';

            $user->update([
                'status' => 'removed',
                'is_suspended' => true,
                'removal_reason' => $reason,
                'removed_at' => now(),
            ]);

            $store->update(['status' => 'rejected', 'rejection_reason' => $reason]);
            Product::where('seller_id', $store->id)->update(['is_active' => false]);

            try {
                NotificationService::notify(
                    $user->id,
                    'account_removed',
                    'Store Account Removed',
                    "Your store \"{$store->name}\" and seller account have been removed by platform administration. Reason: {$reason}",
                    url('/login')
                );
            } catch (\Throwable $e) {
                Log::info('Store remove notification error: ' . $e->getMessage());
            }

            $this->audit('Store removed', "Store '{$store->name}' (#{$store->id}) and seller '{$user->name}' (#{$user->id}) were permanently removed. Reason: {$reason}");

            return back()->with('success', "Store \"{$store->name}\" has been permanently removed.");
        });
    }

    public function products(Request $request)
    {
        $statusCounts = [
            'all' => Product::count(),
            'pending' => Product::where('approval_status', Product::APPROVAL_PENDING)->count(),
            'approved' => Product::where('approval_status', Product::APPROVAL_APPROVED)->count(),
            'rejected' => Product::where('approval_status', Product::APPROVAL_REJECTED)->count(),
        ];

        $query = Product::with([
            'seller.user',
            'category',
            'categories',
            'coverImage',
            'images',
            'variants',
            'reviewer',
        ])->latest('id');

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if (in_array($status, [Product::APPROVAL_PENDING, Product::APPROVAL_APPROVED, Product::APPROVAL_REJECTED], true)) {
                $query->where('approval_status', $status);
            }
        }

        if ($request->filled('category')) {
            $catId = (int) $request->input('category');
            $query->where(fn ($q) => $q->where('category_id', $catId)->orWhereHas('categories', fn ($c) => $c->where('categories.id', $catId)));
        }

        if ($request->filled('seller')) {
            $query->where('seller_id', (int) $request->input('seller'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim()->toString().'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', $search))
                    ->orWhereHas('seller', fn ($s) => $s->where('name', 'like', $search)->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search)->orWhere('email', 'like', $search)));
            });
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        $sellers = Seller::with('user')->orderBy('name')->get();

        return view('admin.products', compact('products', 'statusCounts', 'categories', 'sellers'));
    }

    public function approveProduct(Request $request, int $id)
    {
        $product = Product::with('seller.user')->findOrFail($id);

        DB::transaction(function () use ($product) {
            $product->update([
                'approval_status' => Product::APPROVAL_APPROVED,
                'rejection_reason' => null,
                'reviewed_at' => now(),
                'reviewed_by' => Auth::id(),
                'is_active' => true,
            ]);

            $this->audit('Product approved', "Product #{$product->id} \"{$product->name}\" for store \"{$product->seller?->name}\" was approved.");

            if ($product->seller?->user_id) {
                try {
                    NotificationService::notify(
                        $product->seller->user_id,
                        'product_approved',
                        'Product Approved',
                        "Your product \"{$product->name}\" has been approved and is now live on the marketplace.",
                        route('seller.products', ['search' => $product->name])
                    );
                } catch (\Throwable $e) {
                    Log::info('Product approval notification error: ' . $e->getMessage());
                }
            }
        });

        return back()->with('success', "Product \"{$product->name}\" has been approved and published to the marketplace.");
    }

    public function rejectProduct(Request $request, int $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $product = Product::with('seller.user')->findOrFail($id);

        DB::transaction(function () use ($product, $data) {
            $product->update([
                'approval_status' => Product::APPROVAL_REJECTED,
                'rejection_reason' => $data['reason'],
                'reviewed_at' => now(),
                'reviewed_by' => Auth::id(),
                'is_active' => false,
            ]);

            $this->audit('Product rejected', "Product #{$product->id} \"{$product->name}\" for store \"{$product->seller?->name}\" was rejected. Reason: {$data['reason']}");

            if ($product->seller?->user_id) {
                try {
                    NotificationService::notify(
                        $product->seller->user_id,
                        'product_rejected',
                        'Product Review Notice',
                        "Your product \"{$product->name}\" was not approved. Reason: {$data['reason']}",
                        route('seller.products.edit', $product->id)
                    );
                } catch (\Throwable $e) {
                    Log::info('Product rejection notification error: ' . $e->getMessage());
                }
            }
        });

        return back()->with('success', "Product \"{$product->name}\" was rejected and removed from storefront.");
    }

    /**
     * Workspace 2b: Merchant & Partner Account Registrations (Seller, Courier, Logistics Onboarding)
     */
    public function registrations(Request $request)
    {
        // All pending accounts go to registrations section since they are not yet registered
        $baseQuery = User::where(function ($q) {
            $q->where('status', 'pending')
              ->orWhereIn('role', ['seller', 'logistics', 'courier']);
        });

        $statusCounts = [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $query = (clone $baseQuery)->with(['seller', 'logisticsProvider'])->latest('id');

        if ($request->filled('role')) {
            $query->where('role', $request->string('role')->toString());
        }

        if ($request->filled('seller_type')) {
            $query->where('seller_type', $request->string('seller_type')->toString());
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if (in_array($status, ['pending', 'active', 'rejected'], true)) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim()->toString().'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('business_name', 'like', $search)
                    ->orWhere('tin_number', 'like', $search);
            });
        }

        $registrations = $query->paginate(15)->withQueryString();

        return view('admin.registrations', compact('registrations', 'statusCounts'));
    }

    /**
     * Workspace 2c: Buyer Government ID Checks & Verification (Buyers Only)
     */
    public function idChecks(Request $request)
    {
        $tab = $request->query('tab', 'to_review');
        if (!in_array($tab, ['to_review', 'approved', 'rejected'], true)) {
            $tab = 'to_review';
        }

        $baseQuery = User::where('role', User::ROLE_BUYER)
            ->where(fn ($q) => $q->whereNotNull('id_photo')->orWhereIn('id_status', ['pending', 'verified', 'rejected']));

        $counts = [
            'to_review' => (clone $baseQuery)->where('id_status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('id_status', 'verified')->count(),
            'rejected' => (clone $baseQuery)->where('id_status', 'rejected')->count(),
        ];

        $query = (clone $baseQuery)->with(['buyer'])->latest('id');

        if ($tab === 'to_review') {
            $query->where('id_status', 'pending');
        } elseif ($tab === 'approved') {
            $query->where('id_status', 'verified');
        } elseif ($tab === 'rejected') {
            $query->where('id_status', 'rejected');
        }

        $idChecks = $query->paginate(15)->appends(['tab' => $tab]);

        return view('admin.id-checks', compact('idChecks', 'counts', 'tab'));
    }

    /**
     * Update Buyer Government ID Verification Status (Approve or Reject KYC)
     */
    public function updateIdStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $id) {
            $user = User::where('role', User::ROLE_BUYER)->lockForUpdate()->findOrFail($id);
            $approved = $data['status'] === 'approved';
            abort_if($approved && !$user->id_photo, 422, 'An uploaded identity document is required before verification.');

            $user->update([
                'id_status' => $approved ? 'verified' : 'rejected',
                'id_verified_at' => $approved ? now() : null,
                'id_rejection_reason' => $approved ? null : $data['reason'],
            ]);

            if ($user->buyer) {
                $user->buyer()->update([
                    'id_status' => $approved ? 'verified' : 'rejected',
                    'id_verified_at' => $approved ? now() : null,
                    'id_rejection_reason' => $approved ? null : $data['reason'],
                ]);
            }

            $this->audit('Buyer Government ID reviewed', "{$user->name} (#{$user->id}) — {$data['status']}");

            // In-app notification
            try {
                NotificationService::notify(
                    $user->id,
                    $approved ? 'id_verified' : 'id_rejected',
                    $approved ? 'Government ID Verified' : 'Identity Verification Disapproved',
                    $approved
                        ? 'Congratulations! Your government identity document has been verified. You can now proceed to checkout your cart.'
                        : 'Your identity document was not approved. Reason: ' . ($data['reason'] ?? 'Submitted ID did not meet verification criteria. Please upload a clear valid ID.'),
                    url('/cart')
                );
            } catch (\Throwable $e) {
                Log::info('Buyer ID notification skipped: ' . $e->getMessage());
            }
        });

        return back()->with('success', 'Buyer identity verification decision saved and buyer notified.');
    }

    /**
     * Workspace 2d: Logistics Partners & Fleet Management
     */
    public function logistics(Request $request)
    {
        $baseQuery = LogisticsProvider::query();

        $counts = [
            'all' => (clone $baseQuery)->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'suspended' => (clone $baseQuery)->where('status', 'suspended')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $status = $request->query('status');
        $query = LogisticsProvider::with(['user', 'riders.user'])->withCount([
            'riders',
            'riders as pending_riders_count' => function ($q) {
                $q->where('status', 'pending');
            }
        ])->latest('id');

        if ($status && in_array($status, ['approved', 'pending', 'suspended', 'rejected'], true)) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->string('search')->trim() . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('contact_phone', 'like', $search)
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', $search)
                           ->orWhere('email', 'like', $search)
                           ->orWhere('phone', 'like', $search)
                           ->orWhere('city', 'like', $search)
                           ->orWhere('province', 'like', $search);
                    });
            });
        }

        $providers = $query->paginate(15)->withQueryString();

        return view('admin.logistics', compact('providers', 'counts', 'status'));
    }

    /**
     * Update Logistics Partner Status (Approve, Suspend, Deactivate / Reject, Unsuspend)
     */
    public function updateLogisticsStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'suspended', 'rejected', 'pending'])],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $id) {
            $provider = LogisticsProvider::with('user')->lockForUpdate()->findOrFail($id);
            $newStatus = $data['status'];

            $provider->update([
                'status' => $newStatus,
                'rejection_reason' => in_array($newStatus, ['rejected', 'suspended'], true) ? ($data['reason'] ?? $provider->rejection_reason) : null,
            ]);

            // Sync with associated user account
            if ($provider->user) {
                $userStatus = match ($newStatus) {
                    'approved' => 'active',
                    'suspended' => 'suspended',
                    'rejected' => 'rejected',
                    default => 'pending',
                };
                $provider->user->update([
                    'status' => $userStatus,
                    'is_suspended' => $newStatus === 'suspended',
                ]);
            }

            $actionTitle = match ($newStatus) {
                'approved' => 'Logistics Partner Approved',
                'suspended' => 'Logistics Partner Suspended',
                'rejected' => 'Logistics Partner Deactivated/Rejected',
                default => 'Logistics Partner Status Updated',
            };

            $this->audit('Logistics Partner status updated', "{$provider->name} (#{$provider->id}) -> {$newStatus}");

            // In-app notification
            if ($provider->user) {
                try {
                    $message = match ($newStatus) {
                        'approved' => 'Congratulations! Your company is now an approved logistics partner on Cartzy. You can now onboard riders and dispatch deliveries.',
                        'suspended' => 'Notice: Your logistics partner account has been suspended by administration. ' . ($data['reason'] ?? ''),
                        'rejected' => 'Notice: Your logistics partner application has been declined or deactivated. Reason: ' . ($data['reason'] ?? 'Requirements not met.'),
                        default => 'Your logistics partner application is under review.',
                    };

                    NotificationService::notify(
                        $provider->user->id,
                        'logistics_status_' . $newStatus,
                        $actionTitle,
                        $message,
                        url('/')
                    );
                } catch (\Throwable $e) {
                    Log::info('Logistics notification skipped: ' . $e->getMessage());
                }
            }
        });

        return back()->with('success', 'Logistics partner status has been successfully updated.');
    }

    private function filterUsers($query, Request $request, bool $kyc = false): void
    {
        if ($request->filled('role')) {
            $query->where('role', $request->string('role')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim()->toString().'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('business_name', 'like', $search);
            });
        }
    }

    public function updateRegistrationStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:2000'],
        ]);
        DB::transaction(function () use ($data, $id) {
            $user = User::where('role', '!=', 'admin')->lockForUpdate()->findOrFail($id);
            $approved = $data['status'] === 'approved';
            abort_if($approved && !$user->id_photo, 422, 'An uploaded identity document is required before approval.');
            $user->update([
                'status' => $approved ? 'active' : 'rejected',
                'is_suspended' => false,
                'id_status' => $approved ? 'verified' : 'rejected',
                'id_verified_at' => $approved ? now() : null,
                'id_rejection_reason' => $approved ? null : $data['reason'],
            ]);
            foreach (['seller', 'logisticsProvider'] as $relation) {
                $user->$relation()->update(['status' => $approved ? 'approved' : 'rejected', 'rejection_reason' => $approved ? null : $data['reason']]);
            }
            if ($user->buyer) {
                $user->buyer()->update([
                    'id_status' => $approved ? 'verified' : 'rejected',
                    'id_verified_at' => $approved ? now() : null,
                    'id_rejection_reason' => $approved ? null : $data['reason'],
                ]);
            }
            $this->audit('Registration reviewed', $user->name.' — '.$data['status']);

            // In-app notification
            try {
                NotificationService::notify(
                    $user->id,
                    $approved ? 'registration_approved' : 'registration_rejected',
                    $approved ? 'Registration & Identity Verified' : 'Application Status Update',
                    $approved
                        ? 'Congratulations! Your account registration and documents have been approved by the administration team.'
                        : 'Your registration application was not approved. Reason: ' . ($data['reason'] ?? 'Submitted requirements did not meet verification criteria.'),
                    url('/login')
                );
            } catch (\Throwable $e) {
                Log::info('Registration in-app notification skipped: ' . $e->getMessage());
            }

            // Email notification to applicant
            try {
                if ($user->email) {
                    Mail::to($user->email)->send(new \App\Mail\RegistrationDecisionMail($user, $data['status'], $data['reason'] ?? null));
                }
            } catch (\Throwable $e) {
                Log::info('Registration email delivery skipped for ' . $user->email . ': ' . $e->getMessage());
            }
        });
        return back()->with('success', 'Registration decision saved and applicant notified.');
    }

    public function users(Request $request)
    {
        // Pending accounts are managed under Registrations. Archived and removed accounts are in Archive.
        $query = User::whereNotIn('status', ['pending', 'archived', 'removed']);
        $this->filterUsers($query, $request);
        $users = $query->latest()->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function updateUserStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended', 'deactivated'])],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $user = User::lockForUpdate()->findOrFail($id);
            abort_if($user->isAdmin(), 422, 'Administrator access cannot be changed here.');
            abort_if($data['status'] === 'active' && in_array($user->status, ['pending', 'rejected']), 422, 'Review the registration before activating this account.');

            $reason = trim((string) ($data['reason'] ?? ''));

            $updateData = [
                'status' => $data['status'],
                'is_suspended' => $data['status'] !== 'active',
            ];

            if ($data['status'] !== 'active' && !empty($reason)) {
                $updateData['status_reason'] = $reason;
            } elseif ($data['status'] === 'active') {
                $updateData['status_reason'] = null;
            }

            $user->update($updateData);
            $user->rider()->update(['is_active' => $data['status'] === 'active']);
            $user->buyer()->update(['status' => $data['status']]);

            // Notify user of the status change and reason
            if (in_array($data['status'], ['suspended', 'deactivated'], true)) {
                $statusTitle = ucfirst($data['status']);
                $noticeText = !empty($reason)
                    ? "Your account has been {$data['status']} by Cartzy administration. Reason: {$reason}"
                    : "Your account has been {$data['status']} by Cartzy administration.";

                try {
                    NotificationService::notify(
                        $user->id,
                        'account_status_changed',
                        "Account {$statusTitle}",
                        $noticeText,
                        url('/login')
                    );
                } catch (\Throwable $e) {
                    Log::info('Status notification failed: ' . $e->getMessage());
                }
            } elseif ($data['status'] === 'active') {
                try {
                    NotificationService::notify(
                        $user->id,
                        'account_restored',
                        'Account Reactivated',
                        'Your account has been reactivated and restored to active status by Cartzy administration.',
                        url('/login')
                    );
                } catch (\Throwable $e) {
                    Log::info('Reactivate notification failed: ' . $e->getMessage());
                }
            }

            $this->audit('Account status changed', $user->name.' — '.$data['status'].(!empty($reason) ? ' (Reason: '.$reason.')' : ''));
        });

        $actionWord = $data['status'] === 'active' ? 'reactivated' : $data['status'];
        return back()->with('success', "Account has been {$actionWord} successfully.");
    }

    public function archiveUser(int $id)
    {
        DB::transaction(function () use ($id) {
            $user = User::lockForUpdate()->findOrFail($id);
            abort_if($user->isAdmin(), 422, 'Administrator access cannot be changed here.');

            $user->update([
                'status' => 'archived',
                'is_suspended' => true,
            ]);
            $user->rider()->update(['is_active' => false]);
            $user->buyer()->update(['status' => 'archived']);
            $user->seller()->update(['status' => 'suspended']);
            if ($user->seller) {
                Product::where('seller_id', $user->seller->id)->update(['is_active' => false]);
            }

            try {
                NotificationService::notify(
                    $user->id,
                    'account_archived',
                    'Account Archived',
                    'Your account has been archived by Cartzy administration.',
                    url('/login')
                );
            } catch (\Throwable $e) {
                Log::info('Archive notification failed: ' . $e->getMessage());
            }

            $this->audit('Account archived', "User '{$user->name}' (#{$user->id}) was archived and moved to the archive section.");
        });

        return back()->with('success', 'Account has been archived and moved to the Archive section.');
    }

    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user:id,name,email,role');
        if (in_array($request->query('event'), [ActivityLog::LOGIN, ActivityLog::LOGOUT, ActivityLog::FAILED_LOGIN], true)) {
            $query->where('event', $request->query('event'));
        }
        if ($request->filled('role')) $query->where('role', $request->string('role')->toString());
        if ($request->filled('from')) $query->whereDate('created_at', '>=', $request->date('from'));
        if ($request->filled('to')) $query->whereDate('created_at', '<=', $request->date('to'));
        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim()->toString().'%';
            $query->where(fn ($q) => $q->where('email', 'like', $search)->orWhere('ip_address', 'like', $search)
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search)));
        }
        $logs = $query->latest('created_at')->latest('id')->paginate(25)->withQueryString();
        $today = ActivityLog::whereDate('created_at', today())->selectRaw('event, count(*) as total')->groupBy('event')->pluck('total', 'event');
        return view('admin.activity-logs', compact('logs', 'today'));
    }

    public function compliance(Request $request)
    {
        $query = Product::with(['seller.user', 'category']);
        if (in_array($request->query('status'), ['active', 'inactive'])) $query->where('is_active', $request->query('status') === 'active');
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->string('search')->trim().'%');
        $products = $query->latest()->paginate(15)->withQueryString();
        return view('admin.compliance', compact('products'));
    }

    public function handleComplianceAction(Request $request, int $id)
    {
        $data = $request->validate(['action' => ['required', Rule::in(['activate', 'suspend_product'])]]);
        DB::transaction(function () use ($id, $data) {
            $product = Product::findOrFail($id);
            $product->update(['is_active' => $data['action'] === 'activate']);
            if ($product->seller?->user_id) {
                NotificationService::notify($product->seller->user_id, 'product_update', 'Product visibility updated by admin',
                    $product->name.($product->is_active ? ' was activated by admin.' : ' was suspended by admin.'),
                    route('seller.products', ['tab' => $product->is_active ? 'active' : 'archived', 'search' => $product->name]));
            }
            $this->audit('Product visibility changed', $product->name.' — '.($product->is_active ? 'active' : 'inactive'));
        });
        return back()->with('success', 'Product visibility saved.');
    }

    public function issueSellerWarning(Request $request, int $id)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);
        $seller = Seller::with('user')->findOrFail($id);
        DB::transaction(function () use ($seller, $data) {
            if ($seller->user) {
                NotificationService::notify(
                    $seller->user->id,
                    'seller_warning',
                    'Compliance Warning: Store Policy Violation',
                    $data['reason'],
                    url('/seller/dashboard')
                );
                try {
                    if ($seller->user->email) {
                        Mail::raw("Dear {$seller->name},\n\nYou have received a compliance notice from Cartzy Administration regarding your store products or category guidelines:\n\n{$data['reason']}\n\nPlease update your listings to comply with platform standards.\n\nCartzy Administration", function ($message) use ($seller) {
                            $message->to($seller->user->email, $seller->name)
                                ->subject('Compliance Notice: Store Policy Warning · Cartzy');
                        });
                    }
                } catch (\Throwable $e) {
                    Log::info('Seller warning email skipped: ' . $e->getMessage());
                }
            }
            $this->audit('Seller warning issued', "Seller {$seller->name} (#{$seller->id}) warned: {$data['reason']}");
        });
        return back()->with('success', "Compliance warning issued to {$seller->name}.");
    }

    public function suspendSeller(Request $request, int $id)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);
        $seller = Seller::with('user')->findOrFail($id);
        DB::transaction(function () use ($seller, $data) {
            if ($seller->user) {
                $seller->user->update([
                    'status' => 'suspended',
                    'is_suspended' => true,
                ]);
                NotificationService::notify(
                    $seller->user->id,
                    'seller_suspended',
                    'Store Account Suspended',
                    'Your seller account has been suspended for compliance violations' . (!empty($data['reason']) ? ': ' . $data['reason'] : '. Please contact platform support.'),
                    url('/login')
                );
            }
            $seller->products()->update(['is_active' => false]);
            $seller->update(['status' => 'suspended']);
            $this->audit('Seller suspended for violations', "Seller {$seller->name} (#{$seller->id}) suspended and product listings deactivated.");
        });
        return back()->with('success', "Seller {$seller->name} and all their product listings have been suspended.");
    }

    public function disputes(Request $request)
    {
        $query = DB::table('admin_disputes')->join('orders', 'orders.id', '=', 'admin_disputes.order_id')
            ->join('users', 'users.id', '=', 'orders.buyer_id')
            ->select('admin_disputes.*', 'orders.reference', 'orders.id as order_id', 'users.name as buyer_name');
        if (in_array($request->query('status'), ['open', 'resolved'])) $query->where('admin_disputes.status', $request->query('status'));
        $disputes = $query->orderByDesc('admin_disputes.id')->paginate(15)->withQueryString();
        
        $orderIds = collect($disputes->items())->pluck('order_id')->unique();
        $ordersMap = Order::whereIn('id', $orderIds)
            ->with([
                'buyer',
                'sellerOrders.seller.user',
                'sellerOrders.shipment.rider.user',
                'sellerOrders.shipment.logisticsProvider.user',
            ])
            ->get()
            ->keyBy('id');

        return view('admin.disputes', compact('disputes', 'ordersMap'));
    }

    public function storeDispute(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'exists:orders,reference'],
            'subject' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:5000'],
        ]);
        DB::transaction(function () use ($data) {
            $order = Order::where('reference', $data['reference'])->firstOrFail();
            DB::table('admin_disputes')->insert([
                'order_id' => $order->id, 'subject' => $data['subject'], 'description' => $data['description'],
                'status' => 'open', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->audit('Dispute recorded', $order->reference.' — '.$data['subject']);
        });
        return back()->with('success', 'Dispute recorded.');
    }

    public function resolveDispute(Request $request, int $id)
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:5000']]);
        DB::transaction(function () use ($id, $data) {
            $dispute = DB::table('admin_disputes')->lockForUpdate()->find($id);
            abort_unless($dispute, 404);
            abort_if($dispute->status === 'resolved', 422, 'This dispute is already resolved.');
            abort_if(\App\Models\ReturnCase::where('admin_dispute_id', $id)->exists(), 422,
                'Use Returns & refunds to complete this case and record its outcome.');
            DB::table('admin_disputes')->where('id', $id)->update([
                'status' => 'resolved', 'admin_notes' => $data['notes'], 'resolved_at' => now(), 'updated_at' => now(),
            ]);
            if ($dispute->reporter_id) {
                NotificationService::notify($dispute->reporter_id, 'dispute', 'Your report was resolved',
                    "Case #{$id} ({$dispute->subject}): {$data['notes']}", route('buyer.dashboard', ['tab' => 'orders']));
            }
            $this->audit('Dispute resolved', '#'.$id.' — '.$data['notes']);
        });
        return back()->with('success', 'Resolution recorded. No payment or refund was processed.');
    }

    public function commission()
    {
        $totals = [
            'sales' => $this->earnedOrders()->sum('subtotal_minor') / 100,
            'commission' => $this->earnedOrders()->sum('commission_minor') / 100,
        ];
        $transactions = $this->earnedOrders()->with(['order.buyer', 'seller'])->latest()->paginate(20);
        return view('admin.commission', compact('totals', 'transactions'));
    }

    public function reports(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'export' => ['nullable', Rule::in(['csv'])],
        ]);
        $from = $data['from'] ?? now()->startOfMonth()->toDateString();
        $to = $data['to'] ?? now()->toDateString();
        $query = SellerOrder::with(['order.buyer', 'seller'])->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
        if ($request->query('export') === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Order', 'Seller', 'Date', 'Status', 'Payment status', 'Merchandise PHP', 'Recorded commission PHP']);
                foreach ($query->orderBy('id')->lazy(200) as $row) {
                    $values = [$row->order->reference, $row->seller->name, $row->created_at->toDateString(), $row->status, $row->order->payment_status, $row->subtotal, $row->commission];
                    fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+@\-\t\r]/', $v) ? "'".$v : $v, $values));
                }
                fclose($out);
            }, 'orders-'.$from.'-to-'.$to.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }
        $earned = (clone $query)->whereIn('status', ['delivered', 'completed'])->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'));
        $summary = [
            'orders' => (clone $query)->count(),
            'sales' => (clone $earned)->sum('subtotal_minor') / 100,
            'commission' => (clone $earned)->sum('commission_minor') / 100,
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];
        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('admin.reports', compact('summary', 'orders', 'from', 'to'));
    }

    public function settings()
    {
        $announcements = DB::table('platform_announcements')->latest()->paginate(10);
        $policies = DB::table('platform_settings')->pluck('value', 'key');
        return view('admin.settings', compact('announcements', 'policies'));
    }

    public function saveAnnouncement(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'target' => ['required', Rule::in(['all', 'buyer', 'seller', 'courier', 'logistics'])],
            'content' => ['required', 'string', 'max:5000'],
        ]);
        DB::transaction(function () use ($data) {
            DB::table('platform_announcements')->insert($data + ['created_at' => now(), 'updated_at' => now()]);
            User::where('role', '!=', 'admin')->when($data['target'] !== 'all', fn ($q) => $q->where('role', $data['target']))
                ->select('id')->chunkById(200, function ($users) use ($data) {
                    foreach ($users as $user) AppNotification::create([
                        'user_id' => $user->id, 'type' => 'announcement', 'title' => $data['title'], 'message' => $data['content'],
                    ]);
                });
            $this->audit('Announcement published', $data['title']);
        });
        return back()->with('success', 'Announcement saved and added to recipient notifications.');
    }

    public function updatePolicies(Request $request)
    {
        $data = $request->validate([
            'terms_of_service' => ['nullable', 'string', 'max:20000'],
            'seller_guidelines' => ['nullable', 'string', 'max:20000'],
            'dispute_policy' => ['nullable', 'string', 'max:20000'],
        ]);
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) DB::table('platform_settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now()]);
            $this->audit('Policy notes updated', 'Terms, seller guidelines and dispute policy notes saved.');
        });
        return back()->with('success', 'Policy notes saved.');
    }

    public function chat(Request $request)
    {
        $contacts = User::where('role', '!=', 'admin')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->orderBy('name')->paginate(15)->withQueryString();
        $currentContact = $request->filled('contact') ? User::where('role', '!=', 'admin')->findOrFail($request->integer('contact')) : null;
        $messages = $currentContact
            ? AppNotification::where('user_id', $currentContact->id)->where('type', 'admin_message')->latest()->paginate(20, ['*'], 'messages_page')->withQueryString()
            : null;
        return view('admin.chat', compact('contacts', 'currentContact', 'messages'));
    }

    public function sendMessage(Request $request, int $contactId)
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        DB::transaction(function () use ($contactId, $data) {
            $user = User::where('role', '!=', 'admin')->findOrFail($contactId);
            AppNotification::create(['user_id' => $user->id, 'type' => 'admin_message', 'title' => 'Message from Cartzy administration', 'message' => $data['message']]);
            $this->audit('Message sent', 'Notification sent to '.$user->name);
        });
        return back()->with('success', 'Message saved to the recipient’s notifications.');
    }

    public function account()
    {
        $adminUser = Auth::user();
        $activity = DB::table('admin_audit_logs')->where('user_id', $adminUser->id)->latest()->limit(10)->get();
        return view('admin.account', compact('adminUser', 'activity'));
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();
        if ($request->input('section') === 'password') {
            $data = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Password::min(10)],
            ], [
                'current_password.current_password' => 'The provided current password is incorrect. If you forgot your password, please use the "Forgot password?" option to reset via OTP.',
            ]);
            $user->password = Hash::make($data['password']);
            $user->remember_token = \Illuminate\Support\Str::random(60);
        } else {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone' => ['nullable', 'string', 'max:30'],
            ]);
            $user->fill($data);
        }
        DB::transaction(function () use ($user) {
            $user->save();
            $this->audit('Admin account updated', 'Profile or security details updated.');
        });
        return back()->with('success', 'Account changes saved.');
    }

    public function archive(Request $request)
    {
        $tab = $request->query('tab', 'products');
        if (!in_array($tab, ['products', 'users', 'registrations', 'disputes'], true)) {
            $tab = 'products';
        }

        $counts = [
            'products' => Product::withTrashed()->where(fn ($q) => $q->where('is_active', false)->orWhereNotNull('deleted_at'))->count(),
            'users' => User::where('role', '!=', User::ROLE_ADMIN)->whereIn('status', ['archived', 'removed', 'suspended', 'deactivated'])->count(),
            'registrations' => User::where('role', '!=', User::ROLE_ADMIN)->where(fn ($q) => $q->where('id_status', 'rejected')->orWhere('status', 'rejected'))->count(),
            'disputes' => DB::table('admin_disputes')->where('status', 'resolved')->count(),
        ];

        $products = null;
        $users = null;
        $registrations = null;
        $disputes = null;
        $ordersMap = null;

        if ($tab === 'products') {
            $query = Product::withTrashed()
                ->where(fn ($q) => $q->where('is_active', false)->orWhereNotNull('deleted_at'))
                ->with(['seller.user', 'category', 'variants', 'coverImage']);

            if ($request->filled('status')) {
                if ($request->query('status') === 'trashed') {
                    $query->whereNotNull('deleted_at');
                } elseif ($request->query('status') === 'inactive') {
                    $query->whereNull('deleted_at')->where('is_active', false);
                }
            }

            if ($request->filled('search')) {
                $search = '%' . $request->string('search')->trim()->toString() . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('seller', fn ($s) => $s->where('name', 'like', $search))
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $search))
                        ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', $search));
                });
            }

            $products = $query->latest('updated_at')->paginate(15)->withQueryString();
        } elseif ($tab === 'users') {
            $query = User::where('role', '!=', User::ROLE_ADMIN)
                ->whereIn('status', ['archived', 'removed', 'suspended', 'deactivated']);

            if ($request->filled('role')) {
                $query->where('role', $request->string('role')->toString());
            }

            if ($request->filled('status')) {
                $query->where('status', $request->string('status')->toString());
            }

            if ($request->filled('search')) {
                $search = '%' . $request->string('search')->trim()->toString() . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('business_name', 'like', $search));
            }

            $users = $query->latest('updated_at')->paginate(15)->withQueryString();
        } elseif ($tab === 'registrations') {
            $query = User::where('role', '!=', User::ROLE_ADMIN)
                ->where(fn ($q) => $q->where('id_status', 'rejected')->orWhere('status', 'rejected'));

            if ($request->filled('role')) {
                $query->where('role', $request->string('role')->toString());
            }

            if ($request->filled('search')) {
                $search = '%' . $request->string('search')->trim()->toString() . '%';
                $query->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('business_name', 'like', $search));
            }

            $registrations = $query->latest('updated_at')->paginate(15)->withQueryString();
        } elseif ($tab === 'disputes') {
            $query = DB::table('admin_disputes')
                ->join('orders', 'orders.id', '=', 'admin_disputes.order_id')
                ->join('users', 'users.id', '=', 'orders.buyer_id')
                ->select('admin_disputes.*', 'orders.reference', 'orders.id as order_id', 'users.name as buyer_name')
                ->where('admin_disputes.status', 'resolved');

            if ($request->filled('search')) {
                $search = '%' . $request->string('search')->trim()->toString() . '%';
                $query->where(fn ($q) => $q->where('orders.reference', 'like', $search)->orWhere('admin_disputes.subject', 'like', $search)->orWhere('users.name', 'like', $search));
            }

            $disputes = $query->orderByDesc('admin_disputes.resolved_at')->paginate(15)->withQueryString();

            $orderIds = collect($disputes->items())->pluck('order_id')->unique();
            $ordersMap = Order::whereIn('id', $orderIds)
                ->with([
                    'buyer',
                    'sellerOrders.seller.user',
                    'sellerOrders.shipment.rider.user',
                ])
                ->get()
                ->keyBy('id');
        }

        return view('admin.archive', compact('tab', 'counts', 'products', 'users', 'registrations', 'disputes', 'ordersMap'));
    }

    public function restoreArchivedProduct(int $id)
    {
        DB::transaction(function () use ($id) {
            $product = Product::withTrashed()->findOrFail($id);
            if ($product->trashed()) {
                $product->restore();
            }
            $product->update(['is_active' => true]);

            if ($product->seller?->user_id) {
                try {
                    NotificationService::notify(
                        $product->seller->user_id,
                        'product_update',
                        'Product Restored by Administrator',
                        "Your listing '{$product->name}' has been restored to active status by marketplace administration.",
                        route('seller.products', ['tab' => 'active', 'search' => $product->name])
                    );
                } catch (\Throwable $e) {
                    Log::info('Product restore notification skipped: ' . $e->getMessage());
                }
            }

            $this->audit('Product restored from archive', "Product '{$product->name}' (#{$product->id}) restored to active catalogue.");
        });

        return back()->with('success', 'Product has been restored and made active.');
    }

    public function archiveProduct(int $id)
    {
        DB::transaction(function () use ($id) {
            $product = Product::findOrFail($id);
            $product->update(['is_active' => false]);

            if ($product->seller?->user_id) {
                try {
                    NotificationService::notify(
                        $product->seller->user_id,
                        'product_update',
                        'Product Archived by Administrator',
                        "Your listing '{$product->name}' was deactivated and moved to archive by administration.",
                        route('seller.products', ['tab' => 'archived', 'search' => $product->name])
                    );
                } catch (\Throwable $e) {
                    Log::info('Product archive notification skipped: ' . $e->getMessage());
                }
            }

            $this->audit('Product archived', "Product '{$product->name}' (#{$product->id}) moved to archive.");
        });

        return back()->with('success', 'Product has been moved to the archive.');
    }

    public function forceDeleteProduct(int $id)
    {
        DB::transaction(function () use ($id) {
            $product = Product::withTrashed()->findOrFail($id);
            $name = $product->name;
            $productId = $product->id;

            $product->variants()->withTrashed()->forceDelete();
            $product->forceDelete();

            $this->audit('Product permanently deleted', "Product '{$name}' (#{$productId}) was permanently deleted from archive.");
        });

        return back()->with('success', 'Product listing permanently deleted from archive.');
    }

    public function restoreArchivedUser(int $id)
    {
        DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);
            abort_if($user->isAdmin(), 422, 'Administrator access cannot be changed here.');

            $user->update([
                'status' => 'active',
                'is_suspended' => false,
                'status_reason' => null,
                'removal_reason' => null,
                'removed_at' => null,
            ]);

            $user->seller()->update(['status' => 'approved']);
            $user->rider()->update(['is_active' => true]);
            $user->buyer()->update(['status' => 'active']);

            try {
                NotificationService::notify(
                    $user->id,
                    'account_restored',
                    'Account Reactivated',
                    'Your account has been reviewed and restored to active status by Cartzy administration.',
                    url('/login')
                );
            } catch (\Throwable $e) {
                Log::info('Account restore notification skipped: ' . $e->getMessage());
            }

            $this->audit('Account restored from archive', "User '{$user->name}' (#{$user->id}, role: {$user->role}) reactivated.");
        });

        return back()->with('success', 'Account reactivated and restored to active status.');
    }

    public function removeArchivedUser(Request $request, int $id)
    {
        $data = $request->validate([
            'removal_reason' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $user = User::lockForUpdate()->findOrFail($id);
            abort_if($user->isAdmin(), 422, 'Administrator access cannot be changed here.');

            $reason = trim($data['removal_reason']);

            $user->update([
                'status' => 'removed',
                'is_suspended' => true,
                'removal_reason' => $reason,
                'removed_at' => now(),
            ]);

            $user->rider()->update(['is_active' => false]);
            $user->buyer()->update(['status' => 'removed']);
            $user->seller()->update(['status' => 'rejected']);
            if ($user->seller) {
                Product::where('seller_id', $user->seller->id)->update(['is_active' => false]);
            }

            try {
                NotificationService::notify(
                    $user->id,
                    'account_removed',
                    'Account Removed',
                    "Your account has been removed by platform administration. Reason: {$reason}",
                    url('/login')
                );
            } catch (\Throwable $e) {
                Log::info('Remove notification failed: ' . $e->getMessage());
            }

            $this->audit('Account removed', "User '{$user->name}' (#{$user->id}) was permanently marked as removed. Reason: {$reason}");
        });

        return back()->with('success', 'Account has been removed and removal reason saved.');
    }

    public function restoreArchivedRegistration(Request $request, int $id)
    {
        DB::transaction(function () use ($id) {
            $user = User::where('role', '!=', 'admin')->findOrFail($id);

            $user->update([
                'status' => 'pending',
                'id_status' => 'pending',
                'id_rejection_reason' => null,
            ]);

            $user->seller()->update(['status' => 'pending', 'rejection_reason' => null]);
            $user->logisticsProvider()->update(['status' => 'pending', 'rejection_reason' => null]);

            try {
                NotificationService::notify(
                    $user->id,
                    'registration_reopened',
                    'Registration Reopened for Review',
                    'Your registration application has been reopened for review by marketplace administration.',
                    url('/login')
                );
            } catch (\Throwable $e) {
                Log::info('Registration reopened notification skipped: ' . $e->getMessage());
            }

            $this->audit('Registration reopened from archive', "Application for '{$user->name}' (#{$user->id}) reopened for review.");
        });

        return back()->with('success', 'Registration application reopened and moved to active pending review.');
    }

    public function reopenArchivedDispute(int $id)
    {
        DB::transaction(function () use ($id) {
            $dispute = DB::table('admin_disputes')->where('id', $id)->first();
            abort_unless($dispute, 404);

            DB::table('admin_disputes')->where('id', $id)->update([
                'status' => 'open',
                'resolved_at' => null,
                'updated_at' => now(),
            ]);

            $this->audit('Dispute reopened from archive', "Dispute case #{$id} reopened for active review.");
        });

        return back()->with('success', 'Dispute case reopened and moved to active complaints.');
    }
}
