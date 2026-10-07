<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AppNotification, Order, Product, Seller, SellerOrder, User};
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

    public function dashboard()
    {
        $stats = [
            'sales' => (clone $this->earnedOrders())->sum('subtotal_minor') / 100,
            'commission' => (clone $this->earnedOrders())->sum('commission_minor') / 100,
            'orders' => Order::count(),
            'pending' => $this->applicants()->where(fn ($q) => $q->where('id_status', 'pending')
                ->orWhere(fn ($q) => $q->where('status', 'pending')->where('id_status', '!=', 'rejected')))->count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'sellers' => User::where('role', 'seller')->count(),
            'couriers' => User::where('role', 'courier')->count(),
            'products' => Product::count(),
        ];
        $registrations = $this->applicants()->latest()->limit(5)->get();
        $orders = SellerOrder::with(['order.buyer', 'seller'])->latest()->limit(6)->get();
        $activity = DB::table('admin_audit_logs')->latest()->limit(6)->get();
        return view('admin.dashboard', compact('stats', 'registrations', 'orders', 'activity'));
    }

    public function registrations(Request $request)
    {
        $query = $this->applicants();
        $this->filterUsers($query, $request, true);
        $registrations = $query->latest()->paginate(15)->withQueryString();
        return view('admin.registrations', compact('registrations'));
    }

    private function filterUsers($query, Request $request, bool $kyc = false): void
    {
        if ($request->filled('role')) $query->where('role', $request->string('role')->toString());
        if ($kyc && $request->query('status') === 'pending') {
            $query->where(fn ($q) => $q->where('id_status', 'pending')->orWhere(fn ($q) => $q->where('status', 'pending')->where('id_status', '!=', 'rejected')));
        } elseif ($request->filled('status')) {
            $query->where($kyc ? 'id_status' : 'status', $request->string('status')->toString());
        }
        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim()->toString().'%';
            $query->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('email', 'like', $search));
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
        $query = User::query();
        $this->filterUsers($query, $request);
        $users = $query->latest()->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function updateUserStatus(Request $request, int $id)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'suspended', 'deactivated'])]]);
        DB::transaction(function () use ($id, $data) {
            $user = User::lockForUpdate()->findOrFail($id);
            abort_if($user->isAdmin(), 422, 'Administrator access cannot be changed here.');
            abort_if($data['status'] === 'active' && in_array($user->status, ['pending', 'rejected']), 422, 'Review the registration before activating this account.');
            $user->update(['status' => $data['status'], 'is_suspended' => $data['status'] !== 'active']);
            $user->rider()->update(['is_active' => $data['status'] === 'active']);
            $this->audit('Account status changed', $user->name.' — '.$data['status']);
        });
        return back()->with('success', 'Account status saved.');
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
            DB::table('admin_disputes')->where('id', $id)->update([
                'status' => 'resolved', 'admin_notes' => $data['notes'], 'resolved_at' => now(), 'updated_at' => now(),
            ]);
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
            $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'confirmed', Password::min(10)]]);
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
}
