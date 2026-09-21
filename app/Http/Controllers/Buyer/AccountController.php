<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Get currently active user with fallback
     */
    private function getUser()
    {
        return Auth::user() ?? User::where('role', 'buyer')->first() ?? new User([
            'name'           => 'Jess Pambago',
            'middle_initial' => 'B.',
            'email'          => 'jessicapambago27@gmail.com',
            'phone'          => '09773587409',
            'sex'            => 'Female',
            'birthday'       => '2005-12-05',
            'age'            => 20,
            'street_address' => '1011, Purok 4',
            'barangay'       => 'Masapang',
            'city'           => 'Victoria',
            'province'       => 'Laguna',
            'region'         => 'IV-A',
            'postal_code'    => '4011',
            'address'        => '1011, Purok 4, Brgy. Masapang, Victoria, Laguna, IV-A, 4011',
            'role'           => 'buyer',
            'status'         => 'active',
        ]);
    }

    /**
     * Main Account Portal (Default: Profile tab)
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'profile');
        $user = $this->getUser();

        // Sample / Live data for related tabs
        $orders = $this->getPurchasesData();
        $cards = $this->getCardsData();
        $addresses = $this->getAddressesData($user);
        $vouchers = $this->getVouchersData();
        $coins = $this->getCoinsData();
        $notifications = $this->getNotificationsData();

        return view('buyer.account', compact(
            'user',
            'tab',
            'orders',
            'cards',
            'addresses',
            'vouchers',
            'coins',
            'notifications'
        ));
    }

    /**
     * Direct Profile Tab
     */
    public function profile()
    {
        return redirect()->route('account.index', ['tab' => 'profile']);
    }

    /**
     * Direct Purchases Tab
     */
    public function purchases(Request $request)
    {
        $status = $request->query('status', 'all');
        return redirect()->route('account.index', ['tab' => 'purchases', 'status' => $status]);
    }

    /**
     * Direct Addresses Tab
     */
    public function addresses()
    {
        return redirect()->route('account.index', ['tab' => 'addresses']);
    }

    /**
     * Direct Cards Tab
     */
    public function cards()
    {
        return redirect()->route('account.index', ['tab' => 'cards']);
    }

    /**
     * Update Profile Details
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'sex'            => ['nullable', 'string', 'in:Male,Female,Prefer not to say,Other'],
            'birthday'       => ['nullable', 'date', 'before:today'],
            'id_type'        => ['nullable', 'string', 'max:100'],
            'id_number'      => ['nullable', 'string', 'max:100'],
            'id_photo'       => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
        ]);

        $age = null;
        if (!empty($validated['birthday'])) {
            $age = max(0, (int) Carbon::parse($validated['birthday'])->age);
        }

        if ($user) {
            $updateData = [
                'name'           => $validated['name'],
                'middle_initial' => $validated['middle_initial'] ?? null,
                'phone'          => $validated['phone'] ?? null,
                'sex'            => $validated['sex'] ?? null,
                'birthday'       => $validated['birthday'] ?? null,
                'age'            => $age,
            ];

            if (!empty($validated['id_type'])) {
                $updateData['id_type'] = $validated['id_type'];
            }
            if (isset($validated['id_number'])) {
                $updateData['id_number'] = $validated['id_number'];
            }
            if ($request->hasFile('id_photo')) {
                $path = $request->file('id_photo')->store('id_documents', 'public');
                $updateData['id_photo'] = $path;
                $updateData['id_status'] = 'pending';
                $updateData['id_rejection_reason'] = null;
            }

            $user->update($updateData);

            // Sync database dump
            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
        }

        $idMsg = $request->hasFile('id_photo') ? ' Your ID document was also submitted for verification!' : '';
        return back()->with('success', 'Your profile has been updated successfully!' . $idMsg);
    }

    /**
     * Update Profile Avatar Photo
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $user = Auth::user();
        if ($user && $request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();

            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
        }

        return back()->with('success', 'Profile photo updated successfully!');
    }

    /**
     * Submit Government ID for Verification
     */
    public function submitIdVerification(Request $request)
    {
        $validated = $request->validate([
            'id_type'   => ['required', 'string', 'max:100'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'id_photo'  => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
        ], [
            'id_type.required'  => 'Please select your valid ID type.',
            'id_photo.required' => 'Please upload a clear photo or document of your valid government ID.',
            'id_photo.max'      => 'The ID photo must not exceed 5MB in size.',
        ]);

        $user = Auth::user();

        if ($request->hasFile('id_photo')) {
            $path = $request->file('id_photo')->store('id_documents', 'public');

            if ($user) {
                $user->id_type             = $validated['id_type'];
                $user->id_number           = $validated['id_number'] ?? null;
                $user->id_photo            = $path;
                $user->id_status           = 'pending';
                $user->id_rejection_reason = null;
                $user->save();

                \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
            }
        }

        return redirect()->route('account.index', ['tab' => 'profile'])
            ->with('success', 'Your ID has been submitted successfully and is now under review! You will be notified once approved.');
    }

    /**
     * Update Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'confirmed', Password::min(6)],
            'password_confirmation' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if ($user) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'The current password you entered is incorrect.'])->withInput();
            }

            $user->password = Hash::make($request->input('password'));
            $user->save();

            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
        }

        return back()->with('success', 'Your password has been changed successfully.');
    }

    /**
     * Update Primary Address
     */
    public function updateAddress(Request $request)
    {
        $validated = $request->validate([
            'street_address' => ['required', 'string', 'max:255'],
            'region'         => ['required', 'string', 'max:100'],
            'province'       => ['nullable', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'barangay'       => ['nullable', 'string', 'max:100'],
            'postal_code'    => ['nullable', 'string', 'max:20'],
        ]);

        $street    = trim($validated['street_address']);
        $barangay  = trim($validated['barangay'] ?? '');
        $city      = trim($validated['city'] ?? '');
        $province  = trim($validated['province'] ?? '');
        $region    = trim($validated['region'] ?? '');
        $postalCode = trim($validated['postal_code'] ?? '');

        $addressParts = array_filter([
            $street,
            $barangay ? 'Brgy. ' . $barangay : null,
            $city, $province, $region, $postalCode,
        ]);
        $fullAddress = implode(', ', $addressParts);

        $user = Auth::user();
        if ($user) {
            $user->update([
                'street_address' => $street,
                'barangay'       => $barangay,
                'city'           => $city,
                'province'       => $province,
                'region'         => $region,
                'postal_code'    => $postalCode,
                'address'        => $fullAddress,
            ]);

            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
        }

        return back()->with('success', 'Delivery address updated successfully!');
    }

    // ==========================================
    // MOCK DATA GENERATORS (FOR CLEAN DEMOS)
    // ==========================================

    private function getPurchasesData()
    {
        return [
            [
                'order_id'       => 'ORD-2026-9081',
                'store_name'     => 'TechZone Gadgets PH',
                'store_badge'    => 'Official Mall',
                'status'         => 'to_receive',
                'status_label'   => 'To Receive (Out for Delivery)',
                'status_color'   => 'amber',
                'tracking_no'    => 'EXP-PH-994120',
                'courier_name'   => 'Cartzy Express Rider (Arnel Gomez)',
                'date'           => 'Mar 05, 2026',
                'items'          => [
                    [
                        'name'       => 'Wireless Noise-Canceling Bluetooth Headphones Pro',
                        'variation'  => 'Matte Black · Standard Edition',
                        'quantity'   => 1,
                        'price'      => 2499.00,
                        'image'      => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=120&h=120&fit=crop&q=80',
                    ],
                ],
                'shipping_fee'   => 0.00,
                'voucher_discount' => 150.00,
                'total_amount'   => 2349.00,
            ],
            [
                'order_id'       => 'ORD-2026-8842',
                'store_name'     => 'GlowBeauty Cosmetics Manila',
                'store_badge'    => 'Preferred Seller',
                'status'         => 'completed',
                'status_label'   => 'Completed · Received',
                'status_color'   => 'emerald',
                'tracking_no'    => 'EXP-PH-771829',
                'courier_name'   => 'Flash Express Courier',
                'date'           => 'Feb 28, 2026',
                'items'          => [
                    [
                        'name'       => 'Hydrating Rose Water Face Mist & Serum Glow Set',
                        'variation'  => '100ml Duo Bottle Bundle',
                        'quantity'   => 2,
                        'price'      => 450.00,
                        'image'      => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=120&h=120&fit=crop&q=80',
                    ],
                ],
                'shipping_fee'   => 45.00,
                'voucher_discount' => 45.00,
                'total_amount'   => 900.00,
            ],
            [
                'order_id'       => 'ORD-2026-8104',
                'store_name'     => 'ShoeHaven Lifestyle Store',
                'store_badge'    => 'Preferred',
                'status'         => 'completed',
                'status_label'   => 'Completed',
                'status_color'   => 'emerald',
                'tracking_no'    => 'EXP-PH-554102',
                'courier_name'   => 'J&T Express PH',
                'date'           => 'Feb 15, 2026',
                'items'          => [
                    [
                        'name'       => 'Comfort Running Sneakers Lightweight Casual Shoes',
                        'variation'  => 'White / Cloud Grey · Size EU 38',
                        'quantity'   => 1,
                        'price'      => 1299.00,
                        'image'      => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=120&h=120&fit=crop&q=80',
                    ],
                ],
                'shipping_fee'   => 0.00,
                'voucher_discount' => 100.00,
                'total_amount'   => 1199.00,
            ],
        ];
    }

    private function getCardsData()
    {
        return [
            [
                'id'         => 1,
                'type'       => 'GCash',
                'icon'       => '📱',
                'account'    => '0977 •••• 409',
                'name'       => 'Jess Pambago',
                'is_default' => true,
            ],
            [
                'id'         => 2,
                'type'       => 'Maya E-Wallet',
                'icon'       => '💳',
                'account'    => '0977 •••• 409',
                'name'       => 'Jess Pambago',
                'is_default' => false,
            ],
            [
                'id'         => 3,
                'type'       => 'Mastercard',
                'icon'       => '💳',
                'account'    => '•••• •••• •••• 4812',
                'name'       => 'JESS PAMBAGO',
                'is_default' => false,
            ],
        ];
    }

    private function getAddressesData($user)
    {
        $primary = [
            'id'             => 1,
            'type'           => 'Home',
            'recipient_name' => $user->name ?? 'Jess Pambago',
            'phone'          => $user->phone ?? '09773587409',
            'full_address'   => $user->address ?? '1011, Purok 4, Brgy. Masapang, Victoria, Laguna, IV-A, 4011',
            'street'         => $user->street_address ?? '1011, Purok 4',
            'barangay'       => $user->barangay ?? 'Masapang',
            'city'           => $user->city ?? 'Victoria',
            'province'       => $user->province ?? 'Laguna',
            'region'         => $user->region ?? 'IV-A',
            'postal_code'    => $user->postal_code ?? '4011',
            'is_default'     => true,
            'label'          => 'Default Home Address',
        ];

        return [$primary];
    }

    private function getVouchersData()
    {
        return [
            [
                'id'         => 'FS-MARCH',
                'title'      => 'Free Shipping ₱0 Min. Spend',
                'discount'   => '100% OFF Shipping',
                'min_spend'  => '₱0 Minimum Spend',
                'valid_until'=> 'Mar 31, 2026',
                'category'   => 'Shipping',
                'badge'      => 'Sitewide',
            ],
            [
                'id'         => 'CARTZY10',
                'title'      => '10% OFF Electronics & Tech',
                'discount'   => '₱200 Cap',
                'min_spend'  => '₱1,000 Minimum Spend',
                'valid_until'=> 'Mar 15, 2026',
                'category'   => 'Discount',
                'badge'      => 'TechZone Mall',
            ],
            [
                'id'         => 'NEWBUYER50',
                'title'      => 'Welcome New Buyer Voucher',
                'discount'   => '₱50 OFF',
                'min_spend'  => '₱300 Minimum Spend',
                'valid_until'=> 'Dec 31, 2026',
                'category'   => 'Discount',
                'badge'      => 'New User',
            ],
        ];
    }

    private function getCoinsData()
    {
        return [
            'balance'         => 145,
            'peso_equivalent' => '₱1.45',
            'expiring_soon'   => '25 coins expiring on Mar 31, 2026',
            'history'         => [
                ['title' => 'Daily Login Reward', 'amount' => '+1 Coin', 'date' => 'Today 08:30 AM'],
                ['title' => 'Order #ORD-2026-8842 Coin Cashback', 'amount' => '+20 Coins', 'date' => 'Feb 28, 2026'],
                ['title' => 'Redeemed on Order #ORD-2026-9081', 'amount' => '-15 Coins', 'date' => 'Mar 05, 2026'],
                ['title' => 'Account Registration Bonus', 'amount' => '+100 Coins', 'date' => 'Feb 15, 2026'],
            ],
        ];
    }

    private function getNotificationsData()
    {
        return [
            [
                'id'      => 1,
                'icon'    => '🚚',
                'title'   => 'Parcel Out for Delivery!',
                'message' => 'Your order #ORD-2026-9081 is out for delivery with Cartzy Rider Arnel Gomez.',
                'time'    => '20 minutes ago',
                'unread'  => true,
            ],
            [
                'id'      => 2,
                'icon'    => '🎟️',
                'title'   => 'New Free Shipping Voucher Added',
                'message' => 'A special March Payday Free Shipping voucher has been credited to your wallet.',
                'time'    => '3 hours ago',
                'unread'  => false,
            ],
            [
                'id'      => 3,
                'icon'    => '⚡',
                'title'   => 'Mega Tech Flash Sale is Live',
                'message' => 'Up to 50% off on top electronics and wireless audio accessories.',
                'time'    => 'Yesterday',
                'unread'  => false,
            ],
        ];
    }
}
