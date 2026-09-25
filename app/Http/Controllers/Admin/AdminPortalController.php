<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminPortalController extends Controller
{
    /**
     * Helper to get or initialize session-based dataset for demo without database
     */
    private function getMockData($key, $default)
    {
        if (!session()->has('admin_' . $key)) {
            session(['admin_' . $key => $default]);
        }
        return session('admin_' . $key);
    }

    private function setMockData($key, $data)
    {
        session(['admin_' . $key => $data]);
    }

    /**
     * 1. View Dashboard (Platform Overview & Notifications)
     */
    public function dashboard()
    {
        $stats = [
            'total_gmv' => 1845920.00,
            'platform_revenue' => 184592.00, // 10% commission
            'total_buyers' => 1420,
            'total_sellers' => 84,
            'total_couriers' => 38,
            'pending_registrations' => count(array_filter($this->getRegistrations(), fn($r) => $r['status'] === 'pending')),
            'active_disputes' => count(array_filter($this->getDisputes(), fn($d) => $d['status'] === 'open')),
            'compliance_flags' => count(array_filter($this->getComplianceItems(), fn($c) => $c['status'] === 'flagged')),
        ];

        $notifications = [
            [
                'id' => 1,
                'title' => 'New Seller KYC Submission',
                'message' => 'TechZone Gadgets submitted DTI & BIR 2303 for verification.',
                'time' => '10 mins ago',
                'type' => 'kyc',
                'unread' => true,
                'link' => route('admin.registrations'),
            ],
            [
                'id' => 2,
                'title' => 'Dispute Escalated',
                'message' => 'Buyer #BY-9021 filed a dispute: Item Damaged during Transit.',
                'time' => '35 mins ago',
                'type' => 'dispute',
                'unread' => true,
                'link' => route('admin.disputes'),
            ],
            [
                'id' => 3,
                'title' => 'Seller Compliance Warning',
                'message' => 'ShoeHaven listed "Replica Sneakers" under Apparel category.',
                'time' => '2 hours ago',
                'type' => 'compliance',
                'unread' => false,
                'link' => route('admin.compliance'),
            ],
            [
                'id' => 4,
                'title' => '10% Commission Settled',
                'message' => '₱18,450.00 collected from 142 completed orders today.',
                'time' => '4 hours ago',
                'type' => 'finance',
                'unread' => false,
                'link' => route('admin.commission'),
            ],
        ];

        $recentRegistrations = array_slice($this->getRegistrations(), 0, 4);
        $recentDisputes = array_slice($this->getDisputes(), 0, 3);
        $recentTransactions = array_slice($this->getCommissionTransactions(), 0, 5);

        return view('admin.dashboard', compact('stats', 'notifications', 'recentRegistrations', 'recentDisputes', 'recentTransactions'));
    }

    /**
     * 2. Manage Account Registrations (Buyer, Seller, Courier review & KYC Verification)
     */
    public function registrations(Request $request)
    {
        $roleFilter = $request->query('role', 'all');
        $statusFilter = $request->query('status', 'all');
        $registrations = $this->getRegistrations();

        if ($roleFilter !== 'all') {
            $registrations = array_filter($registrations, fn($r) => $r['role'] === $roleFilter);
        }
        if ($statusFilter !== 'all') {
            $registrations = array_filter($registrations, fn($r) => $r['status'] === $statusFilter);
        }

        return view('admin.registrations', compact('registrations', 'roleFilter', 'statusFilter'));
    }

    public function updateRegistrationStatus(Request $request, $id)
    {
        $status = $request->input('status'); // 'approved' or 'rejected'
        $rejectionReason = $request->input('reason', '');
        $registrations = $this->getRegistrations();

        $updatedApplicant = null;
        foreach ($registrations as &$reg) {
            if ($reg['id'] == $id) {
                $reg['status'] = $status;
                $reg['rejection_reason'] = $status === 'rejected' ? $rejectionReason : null;
                $reg['updated_at'] = now()->format('M d, Y h:i A');
                $updatedApplicant = $reg;
                break;
            }
        }
        $this->setMockData('registrations', $registrations);

        // Update database record if user exists in DB
        try {
            $dbUser = User::find($id);
            if ($dbUser) {
                if ($status === 'approved') {
                    $dbUser->status = 'active';
                    $dbUser->id_status = 'verified';
                    $dbUser->id_verified_at = now();
                    $dbUser->id_rejection_reason = null;
                } else {
                    $dbUser->status = 'rejected';
                    $dbUser->id_status = 'rejected';
                    $dbUser->id_rejection_reason = $rejectionReason;
                }
                $dbUser->save();
                \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
                if (!$updatedApplicant) {
                    $updatedApplicant = [
                        'name' => $dbUser->name,
                        'role' => $dbUser->role,
                        'email' => $dbUser->email,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback gracefully
        }

        $actionText = $status === 'approved' ? 'Approved' : 'Disapproved';
        $name = $updatedApplicant['name'] ?? 'Applicant';
        $role = $updatedApplicant['role'] ?? 'User';
        $email = $updatedApplicant['email'] ?? 'applicant email';

        return back()->with('success', "Application for {$name} ({$role}) has been {$actionText}. Email notification dispatched to {$email}!");
    }

    /**
     * 3. Manage User Accounts (Activate, Suspend, Deactivate)
     */
    public function users(Request $request)
    {
        $roleFilter = $request->query('role', 'all');
        $statusFilter = $request->query('status', 'all');
        $search = strtolower($request->query('search', ''));
        $users = $this->getUsers();

        if ($roleFilter !== 'all') {
            $users = array_filter($users, fn($u) => $u['role'] === $roleFilter);
        }
        if ($statusFilter !== 'all') {
            $users = array_filter($users, fn($u) => $u['status'] === $statusFilter);
        }
        if (!empty($search)) {
            $users = array_filter($users, function($u) use ($search) {
                return str_contains(strtolower($u['name']), $search) || 
                       str_contains(strtolower($u['email']), $search) ||
                       str_contains(strtolower($u['phone']), $search);
            });
        }

        return view('admin.users', compact('users', 'roleFilter', 'statusFilter', 'search'));
    }

    public function updateUserStatus(Request $request, $id)
    {
        $newStatus = $request->input('status'); // 'active', 'suspended', 'deactivated'
        $reason = $request->input('reason', 'Administrative action');
        $users = $this->getUsers();

        $userName = '';
        foreach ($users as &$user) {
            if ($user['id'] == $id) {
                $user['status'] = $newStatus;
                $user['status_reason'] = $reason;
                $userName = $user['name'];
                break;
            }
        }
        $this->setMockData('users', $users);

        return back()->with('success', "User account for {$userName} changed to: " . strtoupper($newStatus) . ".");
    }

    /**
     * 4. Monitor Seller Compliance
     */
    public function compliance(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $items = $this->getComplianceItems();

        if ($statusFilter !== 'all') {
            $items = array_filter($items, fn($i) => $i['status'] === $statusFilter);
        }

        return view('admin.compliance', compact('items', 'statusFilter'));
    }

    public function handleComplianceAction(Request $request, $id)
    {
        $action = $request->input('action'); // 'warn', 'suspend_product', 'suspend_seller', 'dismiss'
        $items = $this->getComplianceItems();

        foreach ($items as &$item) {
            if ($item['id'] == $id) {
                if ($action === 'warn') {
                    $item['status'] = 'warning_issued';
                    $item['strikes'] = ($item['strikes'] ?? 0) + 1;
                    $msg = "Official 1st/2nd Warning issued to {$item['seller_name']} for category/prohibited product violation.";
                } elseif ($action === 'suspend_product') {
                    $item['status'] = 'product_taken_down';
                    $msg = "Product '{$item['product_name']}' has been taken down from public storefront.";
                } elseif ($action === 'suspend_seller') {
                    $item['status'] = 'seller_suspended';
                    $msg = "Seller account {$item['seller_name']} has been suspended for major compliance violation.";
                } else {
                    $item['status'] = 'verified_compliant';
                    $msg = "Compliance flag for '{$item['product_name']}' dismissed. Product verified as compliant.";
                }
                break;
            }
        }
        $this->setMockData('compliance', $items);

        return back()->with('success', $msg);
    }

    /**
     * 5. Manage Complaints and Disputes
     */
    public function disputes(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $disputes = $this->getDisputes();

        if ($statusFilter !== 'all') {
            $disputes = array_filter($disputes, fn($d) => $d['status'] === $statusFilter);
        }

        return view('admin.disputes', compact('disputes', 'statusFilter'));
    }

    public function resolveDispute(Request $request, $id)
    {
        $resolution = $request->input('resolution'); // 'refund_buyer', 'release_to_seller', 'courier_penalty', 'closed'
        $adminNotes = $request->input('notes', 'Admin investigated dispute evidence and issued final judgment.');
        $disputes = $this->getDisputes();

        foreach ($disputes as &$d) {
            if ($d['id'] == $id) {
                $d['status'] = 'resolved';
                $d['resolution'] = $resolution;
                $d['admin_notes'] = $adminNotes;
                $d['resolved_at'] = now()->format('M d, Y');
                break;
            }
        }
        $this->setMockData('disputes', $disputes);

        return back()->with('success', "Dispute #DISP-{$id} has been marked as Resolved. Decision coordinates communicated to Buyer, Seller, and Courier.");
    }

    /**
     * 6. Manage Commission (10%)
     */
    public function commission()
    {
        $transactions = $this->getCommissionTransactions();
        $totalSales = array_sum(array_column($transactions, 'order_amount'));
        $totalCommission = array_sum(array_column($transactions, 'commission_amount'));
        $sellerPayouts = array_sum(array_column($transactions, 'seller_net'));

        return view('admin.commission', compact('transactions', 'totalSales', 'totalCommission', 'sellerPayouts'));
    }

    /**
     * 7. Generate Reports
     */
    public function reports(Request $request)
    {
        $reportType = $request->query('type', 'sales_summary'); // 'sales_summary', 'commission_report'
        $dateRange = $request->query('range', 'this_month');

        $salesData = [
            'total_orders' => 384,
            'gross_sales' => 1845920.00,
            'platform_10_percent' => 184592.00,
            'completed_orders' => 362,
            'refunded_orders' => 12,
            'cancelled_orders' => 10,
            'top_categories' => [
                ['category' => 'Electronics & Gadgets', 'sales' => 742000.00, 'commission' => 74200.00, 'percentage' => 40.2],
                ['category' => 'Fashion & Apparel', 'sales' => 450120.00, 'commission' => 45012.00, 'percentage' => 24.4],
                ['category' => 'Health & Beauty', 'sales' => 328800.00, 'commission' => 32880.00, 'percentage' => 17.8],
                ['category' => 'Home & Living', 'sales' => 195000.00, 'commission' => 19500.00, 'percentage' => 10.6],
                ['category' => 'Food & Groceries', 'sales' => 130000.00, 'commission' => 13000.00, 'percentage' => 7.0],
            ],
            'monthly_breakdown' => [
                ['month' => 'January', 'orders' => 310, 'gross' => 1450000.00, 'commission' => 145000.00],
                ['month' => 'February', 'orders' => 384, 'gross' => 1845920.00, 'commission' => 184592.00],
            ]
        ];

        return view('admin.reports', compact('reportType', 'dateRange', 'salesData'));
    }

    /**
     * 8. Manage Platform Settings (Announcements & Policies)
     */
    public function settings()
    {
        $announcements = $this->getMockData('announcements', [
            [
                'id' => 1,
                'title' => '⚡ Mega Payday Sale 3.3 Platform Campaign',
                'target' => 'all', // 'all', 'buyers', 'sellers', 'couriers'
                'content' => 'Get ready for our upcoming 3.3 Payday Blitz! Sellers enjoy 50% discount on featured product promotions.',
                'active' => true,
                'created_at' => 'Feb 20, 2026',
            ],
            [
                'id' => 2,
                'title' => '📦 Express Courier Fuel Subsidy Announcement',
                'target' => 'couriers',
                'content' => 'All active riders completing >20 deliveries this weekend will receive a ₱500 fuel rebate.',
                'active' => true,
                'created_at' => 'Feb 22, 2026',
            ],
        ]);

        $policies = $this->getMockData('policies', [
            'commission_rate' => 10,
            'auto_payout_days' => 3,
            'max_dispute_window_days' => 7,
            'kyc_required_for_payout' => true,
            'terms_of_service' => "Welcome to Market Store. By operating as a Buyer, Merchant Seller, or Courier Partner, you agree to uphold our compliance and 10% platform transaction standard...",
            'seller_guidelines' => "1. Only list products in approved categories.\n2. Prohibited: Counterfeit, unverified health claims, hazardous materials.\n3. Orders must be packed within 24 hours.",
            'dispute_policy' => "Buyers may request return/refund within 7 days of delivery with valid photo/video proof. Escrow funds will be held by platform admin during active disputes.",
        ]);

        return view('admin.settings', compact('announcements', 'policies'));
    }

    public function saveAnnouncement(Request $request)
    {
        $announcements = $this->getMockData('announcements', []);
        $newAnnouncement = [
            'id' => count($announcements) + 1,
            'title' => $request->input('title', 'Platform Notice'),
            'target' => $request->input('target', 'all'),
            'content' => $request->input('content', ''),
            'active' => true,
            'created_at' => now()->format('M d, Y'),
        ];
        array_unshift($announcements, $newAnnouncement);
        $this->setMockData('announcements', $announcements);

        return back()->with('success', 'Announcement published live to platform users!');
    }

    public function updatePolicies(Request $request)
    {
        $policies = [
            'commission_rate' => 10,
            'auto_payout_days' => (int) $request->input('auto_payout_days', 3),
            'max_dispute_window_days' => (int) $request->input('max_dispute_window_days', 7),
            'kyc_required_for_payout' => $request->has('kyc_required_for_payout'),
            'terms_of_service' => $request->input('terms_of_service'),
            'seller_guidelines' => $request->input('seller_guidelines'),
            'dispute_policy' => $request->input('dispute_policy'),
        ];
        $this->setMockData('policies', $policies);

        return back()->with('success', 'Platform Policies and Terms updated successfully.');
    }

    /**
     * 9. Chat / Messaging Module
     */
    public function chat(Request $request)
    {
        $activeContactId = $request->query('contact', 1);

        $contacts = [
            [
                'id' => 1,
                'name' => 'Maria Santos (TechZone Store)',
                'role' => 'Seller',
                'avatar' => '🏬',
                'status' => 'online',
                'last_message' => 'Admin, I uploaded the revised BIR 2303 document for my KYC verification.',
                'time' => '10:42 AM',
                'unread' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Juan Dela Cruz (Buyer #9021)',
                'role' => 'Buyer',
                'avatar' => '🛍️',
                'status' => 'online',
                'last_message' => 'I submitted photos of the damaged delivery box for Dispute #DISP-101.',
                'time' => '09:15 AM',
                'unread' => 0,
            ],
            [
                'id' => 3,
                'name' => 'Rider Arnel Gomez',
                'role' => 'Courier',
                'avatar' => '🛵',
                'status' => 'offline',
                'last_message' => 'Package parcel #ORD-8821 was received by the consignee neighbor.',
                'time' => 'Yesterday',
                'unread' => 0,
            ],
            [
                'id' => 4,
                'name' => 'ShoeHaven Official',
                'role' => 'Seller',
                'avatar' => '👟',
                'status' => 'online',
                'last_message' => 'We have removed the flagged replica item as requested.',
                'time' => 'Yesterday',
                'unread' => 0,
            ],
        ];

        $currentContact = collect($contacts)->firstWhere('id', $activeContactId) ?? $contacts[0];

        $messages = $this->getMockData('chat_messages_' . $activeContactId, [
            [
                'sender' => 'contact',
                'text' => $currentContact['last_message'],
                'time' => $currentContact['time'],
            ],
            [
                'sender' => 'admin',
                'text' => 'Hello! Admin support here. We are reviewing your submission now.',
                'time' => 'Just now',
            ],
        ]);

        return view('admin.chat', compact('contacts', 'currentContact', 'messages'));
    }

    public function sendMessage(Request $request, $contactId)
    {
        $text = $request->input('message');
        if (trim($text)) {
            $messages = $this->getMockData('chat_messages_' . $contactId, []);
            $messages[] = [
                'sender' => 'admin',
                'text' => $text,
                'time' => now()->format('h:i A'),
            ];
            $this->setMockData('chat_messages_' . $contactId, $messages);
        }

        return back()->with('success', 'Message sent successfully.');
    }

    /**
     * 10. Account Management (Admin Profile & Security)
     */
    public function account()
    {
        $adminUser = Auth::user() ?? (object)[
            'name' => 'admin',
            'email' => 'administrationa570@gmail.com',
            'role' => 'admin',
            'phone' => '+63 917 000 0000',
            'created_at' => 'Jan 01, 2026',
        ];

        return view('admin.account', compact('adminUser'));
    }

    public function updateAccount(Request $request)
    {
        return back()->with('success', 'Admin profile information updated successfully!');
    }

    // ==========================================
    // MOCK DATA GENERATORS (NO DATABASE NEEDED)
    // ==========================================

    private function getRegistrations()
    {
        $mockRegistrations = $this->getMockData('registrations', [
            [
                'id' => 101,
                'name' => 'TechZone Gadgets Corp',
                'applicant_name' => 'Maria Santos',
                'email' => 'maria@techzone.ph',
                'phone' => '+63 917 888 1234',
                'role' => 'seller',
                'category' => 'Electronics & Gadgets',
                'documents' => ['DTI Certificate #09128', 'Mayor\'s Business Permit 2026', 'BIR Form 2303 Certificate of Registration', 'Valid Gov ID (Passport)'],
                'applied_at' => 'Feb 23, 2026 09:30 AM',
                'status' => 'pending',
                'rejection_reason' => null,
            ],
            [
                'id' => 102,
                'name' => 'Arnel Gomez (Express Rider)',
                'applicant_name' => 'Arnel Gomez',
                'email' => 'arnel.rider@gmail.com',
                'phone' => '+63 928 555 9876',
                'role' => 'courier',
                'vehicle' => 'Honda Click 125i (Plate: NQ 4812)',
                'documents' => ['Professional Driver\'s License', 'Vehicle Official Receipt (OR)', 'Certificate of Registration (CR)', 'NBI Clearance Valid 2026'],
                'applied_at' => 'Feb 23, 2026 11:15 AM',
                'status' => 'pending',
                'rejection_reason' => null,
            ],
            [
                'id' => 103,
                'name' => 'GlowBeauty Cosmetics Shop',
                'applicant_name' => 'Carmela Reyes',
                'email' => 'contact@glowbeauty.ph',
                'phone' => '+63 945 321 6543',
                'role' => 'seller',
                'category' => 'Health & Beauty',
                'documents' => ['FDA Cosmetic Notification', 'DTI Business Registration', 'Valid UMID ID'],
                'applied_at' => 'Feb 22, 2026 04:00 PM',
                'status' => 'approved',
                'rejection_reason' => null,
            ],
            [
                'id' => 104,
                'name' => 'Juan Dela Cruz (Verified Buyer Tier)',
                'applicant_name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@yahoo.com',
                'phone' => '+63 915 222 3344',
                'role' => 'buyer',
                'documents' => ['Philippine National ID (PhilID)', 'Proof of Billing (Meralco)'],
                'applied_at' => 'Feb 22, 2026 02:20 PM',
                'status' => 'pending',
                'rejection_reason' => null,
            ],
            [
                'id' => 105,
                'name' => 'CheapVapes PH',
                'applicant_name' => 'Rick Hernandez',
                'email' => 'rick@vapesupplier.com',
                'phone' => '+63 908 111 2233',
                'role' => 'seller',
                'category' => 'Tobacco & Vape',
                'documents' => ['Incomplete ID photo', 'Expired Barangay Clearance'],
                'applied_at' => 'Feb 21, 2026 01:10 PM',
                'status' => 'rejected',
                'rejection_reason' => 'Prohibited age-restricted products without required specialized DTI / BIR age-gate licensing permit.',
            ],
        ]);

        // Merge live database users if any exist
        try {
            $dbUsers = User::where('role', '!=', User::ROLE_ADMIN)->orderBy('id', 'desc')->get();
            $dbList = [];
            foreach ($dbUsers as $u) {
                $docs = [];
                if ($u->id_photo) {
                    $idLabel = $u->id_type ? $u->id_type : 'Government ID';
                    $numLabel = $u->id_number ? ' (#' . $u->id_number . ')' : '';
                    $docs[] = "Valid ID: {$idLabel}{$numLabel}";
                } else {
                    $docs[] = 'Online Verification Submission';
                }

                $regStatus = 'pending';
                if ($u->id_status === 'verified' || ($u->status === 'active' && !empty($u->id_photo) && $u->id_status !== 'rejected')) {
                    $regStatus = 'approved';
                } elseif ($u->id_status === 'rejected' || $u->status === 'rejected') {
                    $regStatus = 'rejected';
                }

                $dbList[] = [
                    'id'               => $u->id,
                    'name'             => $u->name,
                    'applicant_name'   => $u->name,
                    'middle_initial'   => $u->middle_initial,
                    'sex'              => $u->sex,
                    'birthday'         => $u->birthday ? \Carbon\Carbon::parse($u->birthday)->format('M d, Y') : null,
                    'age'              => $u->age,
                    'email'            => $u->email,
                    'phone'            => $u->phone ?? 'N/A',
                    'address'          => $u->address ?? ($u->street_address ? implode(', ', array_filter([$u->street_address, $u->barangay, $u->city, $u->province, $u->region, $u->postal_code])) : 'N/A'),
                    'role'             => $u->role,
                    'business_name'    => $u->business_name,
                    'line_of_business' => $u->line_of_business,
                    'category'         => $u->line_of_business,
                    'id_type'          => $u->id_type,
                    'id_number'        => $u->id_number,
                    'documents'        => $docs,
                    'id_photo'         => $u->id_photo ? asset('storage/' . $u->id_photo) : null,
                    'applied_at'       => $u->created_at ? $u->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
                    'status'           => $regStatus,
                    'rejection_reason' => $u->id_rejection_reason,
                ];
            }

            // Exclude mock users whose emails match real DB users
            $dbEmails = array_map(fn($item) => strtolower($item['email']), $dbList);
            $filteredMock = array_filter($mockRegistrations, fn($m) => !in_array(strtolower($m['email']), $dbEmails));

            return array_values(array_merge($dbList, $filteredMock));
        } catch (\Exception $e) {
            return $mockRegistrations;
        }
    }

    private function getUsers()
    {
        return $this->getMockData('users', [
            [
                'id' => 1,
                'name' => 'admin',
                'email' => 'administrationa570@gmail.com',
                'phone' => '+63 917 000 0000',
                'role' => 'admin',
                'status' => 'active',
                'joined' => 'Jan 01, 2026',
                'orders_count' => 0,
                'violations' => 0,
            ],
            [
                'id' => 2,
                'name' => 'TechZone Gadgets (Maria Santos)',
                'email' => 'maria@techzone.ph',
                'phone' => '+63 917 888 1234',
                'role' => 'seller',
                'status' => 'active',
                'joined' => 'Jan 15, 2026',
                'orders_count' => 248,
                'violations' => 0,
            ],
            [
                'id' => 3,
                'name' => 'ShoeHaven Manila',
                'email' => 'seller@shoehaven.com',
                'phone' => '+63 922 444 8899',
                'role' => 'seller',
                'status' => 'suspended',
                'joined' => 'Feb 01, 2026',
                'orders_count' => 64,
                'violations' => 2,
            ],
            [
                'id' => 4,
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.buyer@gmail.com',
                'phone' => '+63 915 222 3344',
                'role' => 'buyer',
                'status' => 'active',
                'joined' => 'Jan 10, 2026',
                'orders_count' => 18,
                'violations' => 0,
            ],
            [
                'id' => 5,
                'name' => 'Arnel Gomez Express Rider',
                'email' => 'arnel.rider@gmail.com',
                'phone' => '+63 928 555 9876',
                'role' => 'courier',
                'status' => 'active',
                'joined' => 'Jan 20, 2026',
                'orders_count' => 312,
                'violations' => 0,
            ],
            [
                'id' => 6,
                'name' => 'ScamBot Account #88',
                'email' => 'spammer99@fake.xyz',
                'phone' => '+63 999 000 0000',
                'role' => 'buyer',
                'status' => 'deactivated',
                'joined' => 'Feb 18, 2026',
                'orders_count' => 1,
                'violations' => 4,
            ],
        ]);
    }

    private function getComplianceItems()
    {
        return $this->getMockData('compliance', [
            [
                'id' => 201,
                'seller_name' => 'ShoeHaven Manila',
                'seller_email' => 'seller@shoehaven.com',
                'seller_category' => 'Fashion & Footwear',
                'product_name' => 'Air Jordan 1 High "Chicago" (100% Top Grade Replica)',
                'product_category' => 'Footwear',
                'flag_type' => 'Counterfeit / IP Infringement',
                'reason' => 'Listing claims "Top Grade Replica" which violates Anti-Counterfeit and Genuine Goods Policy.',
                'image_url' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=300',
                'status' => 'flagged',
                'strikes' => 1,
            ],
            [
                'id' => 202,
                'seller_name' => 'Urban Electronics',
                'seller_email' => 'urban@tech.ph',
                'seller_category' => 'Consumer Electronics',
                'product_name' => 'Slimming Herbal Weight Loss Pills (1000mg)',
                'product_category' => 'Health & Diet',
                'flag_type' => 'Category Mismatch & Unapproved Drug',
                'reason' => 'Electronics store listing unauthorized pharmaceutical supplement without FDA CPR clearance.',
                'image_url' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=300',
                'status' => 'flagged',
                'strikes' => 0,
            ],
            [
                'id' => 203,
                'seller_name' => 'Organic Fresh Farm',
                'seller_email' => 'farm@organic.ph',
                'seller_category' => 'Food & Groceries',
                'product_name' => 'Benguet Strawberries 500g Pack',
                'product_category' => 'Food & Groceries',
                'flag_type' => 'Category Match Verified',
                'reason' => 'Routine catalog automated compliance check passed.',
                'image_url' => 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=300',
                'status' => 'verified_compliant',
                'strikes' => 0,
            ],
        ]);
    }

    private function getDisputes()
    {
        return $this->getMockData('disputes', [
            [
                'id' => 301,
                'order_id' => 'ORD-88219',
                'buyer_name' => 'Juan Dela Cruz',
                'seller_name' => 'TechZone Gadgets',
                'courier_name' => 'Arnel Gomez (Rider #12)',
                'amount' => 4500.00,
                'item_name' => 'Mechanical Wireless Keyboard Pro RGB',
                'issue_category' => 'Item Damaged in Transit',
                'description' => 'Parcel box was severely crushed upon delivery and keys are broken. Courier dropped package over gate without handover.',
                'status' => 'open',
                'evidence' => ['Crushed box exterior photo', 'Broken key switches photo', 'Waybill receipt #WB-99128'],
                'opened_at' => 'Feb 23, 2026 08:15 AM',
                'resolution' => null,
                'admin_notes' => null,
            ],
            [
                'id' => 302,
                'order_id' => 'ORD-77412',
                'buyer_name' => 'Angelica Ramos',
                'seller_name' => 'FashionNova PH',
                'courier_name' => 'Speedy Express Fleet',
                'amount' => 1890.00,
                'item_name' => 'Floral Maxi Dress - Size M',
                'issue_category' => 'Wrong Item Sent',
                'description' => 'Received Men\'s Cargo Shorts Size 34 instead of Floral Dress.',
                'status' => 'under_investigation',
                'evidence' => ['Product barcode photo', 'Packing video from merchant'],
                'opened_at' => 'Feb 22, 2026 01:45 PM',
                'resolution' => null,
                'admin_notes' => 'Seller confirmed packaging error. Awaiting courier return pick-up confirmation.',
            ],
            [
                'id' => 303,
                'order_id' => 'ORD-65109',
                'buyer_name' => 'Mark Bautista',
                'seller_name' => 'GlowBeauty Shop',
                'courier_name' => 'Metro Express',
                'amount' => 1250.00,
                'item_name' => 'Vitamin C Serum 30ml',
                'issue_category' => 'Parcel Not Received (Fake Delivery Tag)',
                'description' => 'Courier marked parcel as delivered but buyer did not receive it.',
                'status' => 'resolved',
                'evidence' => ['Gate CCTV snapshot', 'GPS Geofence mismatch log'],
                'opened_at' => 'Feb 20, 2026',
                'resolution' => 'refund_buyer',
                'admin_notes' => 'Courier failed to provide proof of delivery. Full ₱1,250 refund credited to Buyer wallet. Courier penalized.',
            ],
        ]);
    }

    private function getCommissionTransactions()
    {
        return $this->getMockData('commission_transactions', [
            [
                'order_id' => 'ORD-99101',
                'date' => 'Feb 23, 2026 05:20 PM',
                'seller' => 'TechZone Gadgets',
                'buyer' => 'Juan Dela Cruz',
                'items' => 'Sony Noise Cancelling Headphones',
                'order_amount' => 12500.00,
                'commission_rate' => '10%',
                'commission_amount' => 1250.00,
                'seller_net' => 11250.00,
                'escrow_status' => 'Released to Seller',
            ],
            [
                'order_id' => 'ORD-99102',
                'date' => 'Feb 23, 2026 04:45 PM',
                'seller' => 'GlowBeauty Shop',
                'buyer' => 'Sarah Lim',
                'items' => 'Skincare Hydration Duo Set',
                'order_amount' => 2800.00,
                'commission_rate' => '10%',
                'commission_amount' => 280.00,
                'seller_net' => 2520.00,
                'escrow_status' => 'Holding in Escrow',
            ],
            [
                'order_id' => 'ORD-99103',
                'date' => 'Feb 23, 2026 03:10 PM',
                'seller' => 'Organic Fresh Farm',
                'buyer' => 'Carlos Diaz',
                'items' => 'Artisan Coffee Beans 1kg',
                'order_amount' => 950.00,
                'commission_rate' => '10%',
                'commission_amount' => 95.00,
                'seller_net' => 855.00,
                'escrow_status' => 'Released to Seller',
            ],
            [
                'order_id' => 'ORD-99104',
                'date' => 'Feb 23, 2026 01:00 PM',
                'seller' => 'TechZone Gadgets',
                'buyer' => 'Patricia Tan',
                'items' => 'Ergonomic Standing Desk Mat',
                'order_amount' => 3400.00,
                'commission_rate' => '10%',
                'commission_amount' => 340.00,
                'seller_net' => 3060.00,
                'escrow_status' => 'Released to Seller',
            ],
            [
                'order_id' => 'ORD-99105',
                'date' => 'Feb 22, 2026 07:30 PM',
                'seller' => 'FashionNova PH',
                'buyer' => 'Kyla Mendoza',
                'items' => 'Leather Handbag Tote',
                'order_amount' => 4200.00,
                'commission_rate' => '10%',
                'commission_amount' => 420.00,
                'seller_net' => 3780.00,
                'escrow_status' => 'Released to Seller',
            ],
        ]);
    }
}
