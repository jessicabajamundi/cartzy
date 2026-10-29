<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private function normalizeRegistrationContact(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        if (is_string($request->input('phone'))) {
            $phone = trim($request->input('phone'));
            if ($request->input('phone_country_code') === '+63' && preg_match('/\A9[0-9]{9}\z/', $phone)) {
                $phone = '0' . $phone;
            }
            if (preg_match('/\A\+639[0-9]{9}\z/', $phone)) {
                $phone = '0' . substr($phone, 3);
            }
            $request->merge(['phone' => $phone]);
        }
    }

    private function sellerPhoneRules(): array
    {
        return $this->registrationPhoneRules();
    }

    private function registrationPhoneRules(): array
    {
        return [...$this->mobilePhoneRules(),
            function ($attribute, $value, $fail) {
                if (!empty($value)) {
                    $valClean = trim($value);
                    $formattedLocal = str_starts_with($valClean, '0') ? $valClean : '0' . $valClean;
                    $formattedIntl = '+63' . ltrim($formattedLocal, '0');
                    if (User::whereIn('phone', [$valClean, $formattedLocal, $formattedIntl])->exists()) {
                        $fail('This mobile number is already in use.');
                    }
                }
            },
        ];
    }

    private function mobilePhoneRules(): array
    {
        return ['bail', 'nullable', 'string', 'regex:/\A[0-9]+\z/', 'size:11', 'starts_with:09'];
    }

    private function registrationEmailRules(): array
    {
        return [
            'bail', 'required', 'string', 'max:255', 'email',
            'regex:/\A[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+\z/',
            function ($attribute, $value, $fail) {
                if (User::whereRaw('LOWER(email) = ?', [strtolower($value)])->exists()) {
                    $fail('This email is already in use. Please log in instead. To register another account, use a different email address.');
                }
            },
        ];
    }

    /**
     * Show the login form.
     */
    public function showLoginForm(Request $request)
    {
        $portals = self::loginPortals();
        $portalKey = $request->route('portal', User::ROLE_BUYER);

        return view('auth.login', ['portal' => $portals[$portalKey] + ['key' => $portalKey], 'portals' => $portals]);
    }

    /**
     * One login page per role. Each portal only signs in accounts of its own role.
     */
    public static function loginPortals(): array
    {
        return [
            User::ROLE_BUYER => [
                'label' => 'Buyer', 'route' => 'login',
                'title' => 'Welcome back', 'subtitle' => 'Login to your account and continue shopping',
                'button' => 'Sign In to Account', 'register' => route('register'),
            ],
            User::ROLE_SELLER => [
                'label' => 'Seller', 'route' => 'login.seller',
                'title' => 'Seller Centre', 'subtitle' => 'Log in to manage your store, products, and orders',
                'button' => 'Sign In to Seller Centre', 'register' => route('register', ['role' => User::ROLE_SELLER]),
                'eyebrow' => 'FOR SELLERS', 'heading' => ['Your store.', 'Your growth.'],
            ],
            User::ROLE_LOGISTICS => [
                'label' => 'Logistics', 'route' => 'login.logistics',
                'title' => 'Logistics Hub', 'subtitle' => 'Log in to manage pickups, riders, and deliveries',
                'button' => 'Sign In to Logistics Hub', 'register' => route('register', ['role' => User::ROLE_LOGISTICS]),
                'eyebrow' => 'FOR LOGISTICS PARTNERS', 'heading' => ['Every parcel.', 'Right on time.'],
            ],
            User::ROLE_COURIER => [
                'label' => 'Courier', 'route' => 'login.courier',
                'title' => 'Rider Portal', 'subtitle' => 'Log in to see your deliveries and routes',
                'button' => 'Sign In to Rider Portal', 'register' => route('register.rider'),
                'eyebrow' => 'FOR COURIER RIDERS', 'heading' => ['On the road.', 'On your terms.'],
            ],
            User::ROLE_ADMIN => [
                'label' => 'Admin', 'route' => 'login.admin',
                'title' => 'Admin Console', 'subtitle' => 'Authorized administrators only',
                'button' => 'Sign In to Admin Console', 'register' => null,
                'eyebrow' => 'PLATFORM ADMINISTRATION', 'heading' => ['Keep cartzy', 'running smoothly.'],
            ],
        ];
    }

    /** Login page route name for a role (buyer login when unknown). */
    public static function loginRouteFor(?string $role): string
    {
        return self::loginPortals()[$role]['route'] ?? 'login';
    }

    /** Login page route name for the portal a URL path belongs to, e.g. "seller/orders" -> seller login. */
    public static function loginRouteForPath(string $path): string
    {
        $section = explode('/', trim($path, '/'))[0];

        return in_array($section, [User::ROLE_SELLER, User::ROLE_LOGISTICS, User::ROLE_COURIER, User::ROLE_ADMIN], true)
            ? self::loginRouteFor($section) : 'login';
    }

    /**
     * Handle an incoming login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);

        $remember = $request->boolean('remember');
        $portals = self::loginPortals();
        $portal = array_key_exists((string) $request->input('portal'), $portals) ? $request->input('portal') : User::ROLE_BUYER;

        try {
            if (Auth::attempt($credentials, $remember)) {
                $user = Auth::user();

                if ($user->role !== $portal) {
                    ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, $user, ['method' => 'password', 'description' => 'Wrong login portal ('.$portal.')']);
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    $correct = $portals[$user->role] ?? null;

                    return back()->withErrors([
                        'email' => 'This login page is for '.strtolower($portals[$portal]['label']).' accounts.'
                            .($correct ? ' Please use the '.$correct['label'].' login instead.' : ''),
                    ])->with('portal_hint', $correct ? ['label' => $correct['label'], 'url' => route($correct['route'])] : null)
                        ->onlyInput('email');
                }

                if ($user->status === 'pending') {
                    if ($user->isSeller()) {
                        $request->session()->regenerate();
                        ActivityLog::record(ActivityLog::LOGIN, $request, $user, ['method' => 'password', 'description' => 'Pending seller account']);
                        return redirect()->intended(route($user->getDashboardRoute()))
                            ->with('info', 'Welcome, ' . ($user->name ?? 'Seller') . '! Your account is currently pending administrator verification. You can view your dashboard and store settings, but adding products and selling are locked until approved.');
                    }

                    ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, $user, ['method' => 'password', 'description' => 'Account pending approval']);
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Your account registration is currently pending admin verification. Please wait for an administrator to approve your application.',
                    ])->onlyInput('email');
                }

                if ($user->status === 'rejected') {
                    ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, $user, ['method' => 'password', 'description' => 'Account rejected']);
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Your account application was disapproved by the administrator.',
                    ])->onlyInput('email');
                }

                if (in_array($user->status, ['suspended', 'deactivated'], true) || $user->is_suspended) {
                    ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, $user, ['method' => 'password', 'description' => 'Account suspended']);
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Your account is suspended. Please contact platform support for assistance.',
                    ])->onlyInput('email');
                }

                $request->session()->regenerate();
                ActivityLog::record(ActivityLog::LOGIN, $request, $user, ['method' => 'password']);

                // Redirect to role-specific dashboard or intended URL
                return redirect()->intended(route($user->getDashboardRoute()))
                    ->with('success', 'Welcome back, ' . ($user->name ?? 'User') . '!');
            }
        } catch (\Exception $e) {
            // Fallback if DB connection fails
        }

        ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, null, [
            'email' => $credentials['email'], 'method' => 'password', 'description' => 'Invalid credentials',
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm(Request $request)
    {
        $selectedRole = $request->query('role', 'buyer');
        $shopCategories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        return view('auth.register', compact('selectedRole', 'shopCategories'));
    }

    /**
     * Show the rider registration form.
     */
    public function showRiderRegisterForm()
    {
        $logisticsProviders = \App\Models\LogisticsProvider::where('status', 'approved')
            ->orderBy('name')
            ->get();

        return view('auth.register-rider', compact('logisticsProviders'));
    }

    /**
     * Handle AJAX request to automatically generate and send OTP to applicant email.
     */
    public function requestOtp(Request $request)
    {
        $this->normalizeRegistrationContact($request);
        $validated = $request->validate([
            'email' => $this->registrationEmailRules(),
            'name' => ['nullable', 'string'],
            'role' => ['nullable', 'string'],
            'phone' => $this->registrationPhoneRules(),
        ], ['email.email' => 'Please enter a valid email address.',
            'email.regex' => 'Please enter a valid email address.']);

        $email = strtolower(trim($validated['email']));
        $name = trim($validated['name'] ?? 'Applicant');
        $role = $validated['role'] ?? 'buyer';

        // Generate a secure 6-digit random verification code
        $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $otpItem = [
            'id' => (string)str_replace(['.', '@'], '', $email) . '_' . time(),
            'email' => $email,
            'name' => $name,
            'role' => $role,
            'otp' => $otpCode,
            'status' => 'sent',
            'requested_at' => now()->format('M d, Y h:i A'),
            'sent_at' => now()->format('M d, Y h:i A'),
        ];

        // Automatically dispatch email
        try {
            if (!Mail::isFake() && in_array(config('mail.default'), ['log', 'array', 'failover'], true)) {
                throw new \RuntimeException('Registration requires a delivery-capable mailer.');
            }
            Mail::send('emails.otp-verification', [
                'name' => $name,
                'otp' => $otpCode,
                'email' => $email,
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your Cartzy email verification code');
            });
        } catch (\Exception $e) {
            Log::warning('Registration verification email could not be sent.', [
                'mailer' => config('mail.default'), 'exception' => get_class($e),
            ]);
            return response()->json([
                'success' => false, 'mail_sent' => false,
                'message' => 'We could not send your verification email. Please try again shortly.',
            ], 503);
        }

        // Only activate codes after the mail transport accepts the message.
        Cache::put('otp_' . $email, $otpItem, now()->addMinutes(10));
        return response()->json([
            'success' => true,
            'message' => 'A 6-digit verification code has been sent to your email address.',
            'email' => $email,
            'mail_sent' => true,
        ]);
    }

    /**
     * Verify email OTP via AJAX before password creation
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $email = strtolower(trim($validated['email']));
        $enteredOtp = trim($validated['otp']);
        $otpItem = Cache::get('otp_' . $email);

        if ($otpItem && $otpItem['otp'] === $enteredOtp) {
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'The verification code entered is incorrect. Please check your email.',
        ], 422);
    }

    /**
     * Check OTP status
     */
    public function checkOtpStatus(Request $request)
    {
        $email = strtolower(trim($request->query('email', '')));
        if (!$email) {
            return response()->json(['status' => 'unknown']);
        }

        $otpItem = Cache::get('otp_' . $email);
        return response()->json([
            'status' => $otpItem ? 'sent' : 'none',
            'sent_at' => $otpItem['sent_at'] ?? null,
        ]);
    }

    /**
     * Check if a mobile number is already in use.
     */
    public function checkPhone(Request $request)
    {
        $this->normalizeRegistrationContact($request);
        $phone = $request->input('phone');
        if (empty($phone)) {
            return response()->json(['available' => true]);
        }
        $valClean = trim($phone);
        $formattedLocal = str_starts_with($valClean, '0') ? $valClean : '0' . $valClean;
        $formattedIntl = '+63' . ltrim($formattedLocal, '0');
        $exists = User::whereIn('phone', [$valClean, $formattedLocal, $formattedIntl])->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This mobile number is already in use.' : null,
        ]);
    }

    /**
     * Show the pending approval page after successful registration.
     */
    public function showPendingApproval()
    {
        return view('auth.pending', [
            'pending_name'  => session('pending_name', 'Applicant'),
            'pending_email' => session('pending_email', ''),
            'pending_role'  => session('pending_role', 'seller'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request)
    {
        $this->normalizeRegistrationContact($request);
        if ($request->input('role') === 'seller' && is_string($request->input('tin_number'))) {
            $request->merge(['tin_number' => str_replace('-', '', trim($request->input('tin_number')))]);
        }
        if (is_string($request->input('license_number'))) {
            $request->merge(['license_number' => strtoupper(trim($request->input('license_number')))]);
        }
        $sellerRules = $request->input('role') === 'seller' ? [
            'shop_name' => ['required', 'string', 'min:3', 'max:30', function ($attribute, $value, $fail) {
                if (\App\Models\Seller::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($value))])->exists()) {
                    $fail('This shop name is already in use. Choose another name.');
                }
            }],
            'shop_category' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'seller_type' => ['required', Rule::in(['individual', 'sole_proprietorship', 'corporation'])],
            'tin_number' => ['required', 'regex:/\A[0-9]{12}\z/'],
            'phone' => ['required', ...$this->registrationPhoneRules()],
            'street_address' => ['required', 'string', 'max:500'],
            'region' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'regex:/\A[0-9]{4}\z/'],
            'id_photo' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'dti_permit' => [
                \Illuminate\Validation\Rule::requiredIf(fn () => $request->input('seller_type') === 'sole_proprietorship'),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'sec_certificate' => [
                \Illuminate\Validation\Rule::requiredIf(fn () => $request->input('seller_type') === 'corporation'),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'bir_certificate' => [
                \Illuminate\Validation\Rule::requiredIf(fn () => in_array($request->input('seller_type'), ['sole_proprietorship', 'corporation'], true)),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ] : [];
        $courierRules = ($request->input('role') === User::ROLE_COURIER && ($request->has('logistics_provider_id') || $request->has('vehicle_type') || $request->hasFile('license_doc') || $request->hasFile('or_cr_doc'))) ? [
            'logistics_provider_id' => ['required', 'exists:logistics_providers,id'],
            'vehicle_type'          => ['required', 'string', Rule::in(['motorcycle', 'bicycle', 'ebike', 'tricycle', 'car', 'van', 'truck'])],
            'plate_no'              => [
                Rule::requiredIf(fn () => !in_array($request->input('vehicle_type'), ['bicycle', 'ebike'], true)),
                'nullable',
                'string',
                'min:6',
                'max:7',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'license_number'        => [
                Rule::requiredIf(fn () => !in_array($request->input('vehicle_type'), ['bicycle', 'ebike'], true)),
                'nullable',
                'string',
                'size:13',
                'regex:/^[A-Za-z][0-9]{2}-[0-9]{2}-[0-9]{6}$/',
            ],
            'license_expiry'        => [
                Rule::requiredIf(fn () => !in_array($request->input('vehicle_type'), ['bicycle', 'ebike'], true)),
                'nullable',
                'date',
            ],
            'license_doc'           => [
                Rule::requiredIf(fn () => !in_array($request->input('vehicle_type'), ['bicycle', 'ebike'], true)),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'or_cr_doc'             => [
                Rule::requiredIf(fn () => !in_array($request->input('vehicle_type'), ['bicycle', 'ebike'], true)),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ] : [
            'logistics_provider_id' => ['nullable', 'exists:logistics_providers,id'],
            'vehicle_type'          => ['nullable', 'string', Rule::in(['motorcycle', 'bicycle', 'ebike', 'tricycle', 'car', 'van', 'truck'])],
            'plate_no'              => ['nullable', 'string', 'min:6', 'max:7', 'regex:/^[a-zA-Z0-9]+$/'],
            'license_number'        => ['nullable', 'string', 'size:13', 'regex:/^[A-Za-z][0-9]{2}-[0-9]{2}-[0-9]{6}$/'],
            'license_expiry'        => ['nullable', 'date'],
            'license_doc'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'or_cr_doc'             => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
        $nameRules = [
            'first_name' => ['required', 'string', 'max:50', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'last_name' => ['required', 'string', 'max:50', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
        ];
        if (is_string($request->input('first_name')) && is_string($request->input('last_name'))) {
            $request->merge(['name' => trim($request->input('first_name') . ' ' . $request->input('last_name'))]);
        }

        $validated = $request->validate([
            ...$nameRules,
            'name'                 => ['required', 'string', 'max:255'],
            'middle_initial'       => ['nullable', 'string', 'max:5'],
            'email'                => $this->registrationEmailRules(),
            'sex'                  => ['nullable', 'string', 'in:Male,Female,Prefer not to say'],
            'birthday'             => ['exclude_if:role,logistics,seller', 'bail', 'required', 'date_format:Y-m-d', 'before_or_equal:' . now()->subYearsNoOverflow(18)->toDateString()],
            'phone'                => $this->registrationPhoneRules(),
            'street_address'       => ['nullable', 'string', 'max:500'],
            'address'              => ['nullable', 'string', 'max:500'],
            'region'               => ['nullable', 'string', 'max:100'],
            'province'             => ['nullable', 'string', 'max:100'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'barangay'             => ['nullable', 'string', 'max:100'],
            'postal_code'          => ['nullable', 'string', 'max:20'],
            'business_name'        => ['nullable', 'string', 'max:255'],
            'service_area'         => ['nullable', 'string', 'max:255'],
            'line_of_business'     => ['nullable', 'string', 'max:150'],
            'id_photo'             => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'dti_permit'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'role'                 => ['required', 'string', Rule::in([User::ROLE_BUYER, User::ROLE_SELLER, User::ROLE_COURIER, User::ROLE_LOGISTICS])],
            'password'             => ['required', 'string', 'confirmed', in_array($request->input('role'), [User::ROLE_SELLER, User::ROLE_COURIER], true)
                ? Password::min(8)->mixedCase()->numbers()->symbols() : Password::min(6)],
            'email_verification_otp' => ['required', 'string', 'min:6', 'max:6'],
            ...$sellerRules,
            ...$courierRules,
        ], [
            'name.required'        => 'Please enter your full name.',
            'first_name.required'  => 'Please enter your first name.',
            'last_name.required'   => 'Please enter your last name.',
            'first_name.max'       => 'First name must not exceed 50 characters.',
            'last_name.max'        => 'Last name must not exceed 50 characters.',
            'first_name.regex'     => 'Only letters are allowed.',
            'last_name.regex'      => 'Only letters are allowed.',
            'email.required'       => 'Please enter an email address.',
            'email.email'          => 'Please enter a valid email address.',
            'email.regex'          => 'Please enter a valid email address.',
            'phone.regex'          => 'Please enter a valid mobile number starting with 09 and containing only digits.',
            'phone.starts_with'    => 'Please enter a valid mobile number starting with 09 and containing only digits.',
            'phone.size'           => 'Mobile number must be exactly 11 digits.',
            'email.unique'         => 'This email is already registered. Please log in instead.',
            'sex.in'               => 'Please select a valid sex option.',
            'birthday.required'    => 'Please enter your birthday.',
            'birthday.date_format' => 'Please enter a valid birthday.',
            'birthday.before_or_equal' => 'You must be at least 18 years old to register.',
            'role.required'        => 'Please choose an account type.',
            'role.in'              => 'Invalid account type selected.',
            'password.required'    => 'Please provide a password.',
            'password.confirmed'   => 'The password confirmation does not match.',
            'password.min'         => in_array($request->input('role'), [User::ROLE_SELLER, User::ROLE_COURIER], true)
                ? 'Password must be at least 8 characters long.' : 'Password must be at least 6 characters long.',
            'id_photo.mimes'       => 'ID must be a JPG, PNG, or PDF file.',
            'id_photo.max'         => 'ID file must not exceed 5MB.',
            'dti_permit.mimes'     => 'Business / DTI permit must be a JPG, PNG, or PDF file.',
            'dti_permit.max'       => 'Business / DTI permit must not exceed 5MB.',
            'logistics_provider_id.required' => 'Please choose a logistics partner.',
            'vehicle_type.required'          => 'Please select your vehicle type.',
            'plate_no.required'              => 'Please enter your vehicle plate number.',
            'plate_no.min'                   => 'Plate number must be at least 6 characters.',
            'plate_no.max'                   => 'Plate number must not exceed 7 characters.',
            'plate_no.regex'                 => 'Plate number must contain letters and digits only.',
            'license_number.required'        => 'Please enter your driver\'s license number.',
            'license_number.size'            => 'License number must be exactly 13 characters (e.g. H83-78-937937).',
            'license_number.regex'           => 'Please follow the official license format: A00-00-000000 (e.g. H83-78-937937).',
            'license_expiry.required'        => 'Please enter your license expiry date.',
            'license_doc.required'           => 'Please upload a photo of your driver\'s license.',
            'license_doc.mimes'              => 'License document must be a JPG, PNG, or PDF file.',
            'license_doc.max'                => 'License document must not exceed 5MB.',
            'or_cr_doc.required'             => 'Please upload your vehicle OR/CR.',
            'or_cr_doc.mimes'                => 'OR/CR document must be a JPG, PNG, or PDF file.',
            'or_cr_doc.max'                  => 'OR/CR document must not exceed 5MB.',
            'email_verification_otp.required' => 'Please enter the 6-digit verification code.',
            'email_verification_otp.min'      => 'Please enter the complete 6-digit code.',
        ]);

        // Validate OTP
        $email      = strtolower(trim($validated['email']));
        $enteredOtp = trim($validated['email_verification_otp']);
        $otpItem    = Cache::get('otp_' . $email);

        if (!$otpItem || $otpItem['otp'] !== $enteredOtp) {
            return back()->withInput()->withErrors([
                'password' => 'Invalid 6-digit OTP verification code. Please enter the exact code sent to your email.',
            ]);
        }

        // Auto-calculate age from birthday
        $age = null;
        if (!empty($validated['birthday'])) {
            $age = max(0, (int) \Carbon\Carbon::parse($validated['birthday'])->age);
        }

        // Build composite address string
        $street    = trim($validated['street_address'] ?? ($validated['address'] ?? ''));
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
        $finalAddress = ($validated['role'] === User::ROLE_LOGISTICS && !empty($validated['service_area']))
            ? trim($validated['service_area'])
            : ($fullAddress ?: $street);

        // Handle ID photo upload
        $idPhotoPath = null;
        if ($request->hasFile('id_photo') && $request->file('id_photo')->isValid()) {
            $idPhotoPath = $request->file('id_photo')->store('id_photos', 'public');
        }

        // Handle Business / DTI Permit upload
        $dtiPermitPath = null;
        if ($request->hasFile('dti_permit') && $request->file('dti_permit')->isValid()) {
            $dtiPermitPath = $request->file('dti_permit')->store('dti_permits', 'public');
        }

        $secCertPath = null;
        if ($request->hasFile('sec_certificate') && $request->file('sec_certificate')->isValid()) {
            $secCertPath = $request->file('sec_certificate')->store('seller_documents', 'local');
        }

        $birCertPath = null;
        if ($request->hasFile('bir_certificate') && $request->file('bir_certificate')->isValid()) {
            $birCertPath = $request->file('bir_certificate')->store('seller_documents', 'local');
        }

        $licenseDocPath = null;
        if ($request->hasFile('license_doc') && $request->file('license_doc')->isValid()) {
            $licenseDocPath = $request->file('license_doc')->store('rider_documents', 'public');
        }

        $orCrDocPath = null;
        if ($request->hasFile('or_cr_doc') && $request->file('or_cr_doc')->isValid()) {
            $orCrDocPath = $request->file('or_cr_doc')->store('rider_documents', 'public');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $status = ($validated['role'] === User::ROLE_BUYER) ? 'active' : 'pending';

            $user = User::create([
                'name'             => $validated['name'],
                'middle_initial'   => $validated['middle_initial'] ?? null,
                'email'            => $validated['email'],
                'sex'              => $validated['sex'] ?? null,
                'birthday'         => $validated['birthday'] ?? null,
                'age'              => $age,
                'phone'            => $validated['phone'] ?? null,
                'address'          => $finalAddress,
                'street_address'   => $street,
                'region'           => $region,
                'province'         => $province,
                'city'             => $city,
                'barangay'         => $barangay,
                'postal_code'      => $postalCode,
                'business_name'    => $validated['role'] === User::ROLE_SELLER ? $validated['shop_name'] : ($validated['business_name'] ?? null),
                'line_of_business' => $validated['role'] === User::ROLE_SELLER ? \App\Models\Category::findOrFail($validated['shop_category'])->name : null,
                'seller_type'      => $validated['seller_type'] ?? null,
                'tin_number'       => $validated['tin_number'] ?? null,
                'sec_certificate'  => $secCertPath,
                'bir_certificate'  => $birCertPath,
                'id_photo'         => $idPhotoPath,
                'dti_permit'       => $dtiPermitPath,
                'role'             => $validated['role'],
                'status'           => $status,
                'email_verified_at'=> now(),
                'password'         => Hash::make($validated['password']),
            ]);

            // If registering as Buyer, create corresponding Buyer profile record
            if ($user->isBuyer()) {
                \App\Models\Buyer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name'                => $user->name,
                        'middle_initial'      => $user->middle_initial,
                        'sex'                 => $user->sex,
                        'birthday'            => $user->birthday,
                        'age'                 => $age,
                        'phone'               => $user->phone,
                        'id_photo'            => $idPhotoPath,
                        'id_type'             => $user->id_type ?? null,
                        'id_number'           => $user->id_number ?? null,
                        'id_status'           => 'unverified',
                        'status'              => 'active',
                    ]
                );

                $roleModel = \App\Models\Role::firstOrCreate(['name' => 'buyer']);
                $user->roles()->syncWithoutDetaching([$roleModel->id]);
            }

            // If registering as Seller, create corresponding Seller store record
            if ($user->isSeller()) {
                $baseSlug = \Illuminate\Support\Str::slug($user->business_name ?: $user->name) ?: 'seller-store';
                $uniqueSlug = $baseSlug;
                $counter = 1;
                while (\App\Models\Seller::where('slug', $uniqueSlug)->exists()) {
                    $uniqueSlug = $baseSlug . '-' . $counter++;
                }

                $seller = \App\Models\Seller::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name'   => $user->business_name ?: $user->name,
                        'slug'   => $uniqueSlug,
                        'status' => 'pending',
                    ]
                );

                $pickup = \App\Models\Address::create([
                    'user_id' => $user->id, 'label' => 'Shop pickup', 'recipient' => $user->name,
                    'phone' => $user->phone, 'line1' => $street, 'barangay' => $barangay,
                    'city' => $city, 'province' => $province, 'postal_code' => $postalCode, 'is_default' => true,
                ]);
                $seller->update(['pickup_address_id' => $pickup->id]);
                $roleModel = \App\Models\Role::firstOrCreate(['name' => 'seller']);
                $user->roles()->syncWithoutDetaching([$roleModel->id]);
            }

            // If registering as Logistics partner, create corresponding LogisticsProvider record
            if ($user->isLogistics()) {
                $baseSlug = \Illuminate\Support\Str::slug($user->business_name ?: $user->name) ?: 'logistics-partner';
                $uniqueSlug = $baseSlug;
                $counter = 1;
                while (\App\Models\LogisticsProvider::where('slug', $uniqueSlug)->exists()) {
                    $uniqueSlug = $baseSlug . '-' . $counter++;
                }

                \App\Models\LogisticsProvider::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name'          => $user->business_name ?: $user->name,
                        'slug'          => $uniqueSlug,
                        'status'        => 'pending',
                        'contact_phone' => $user->phone,
                    ]
                );

                $roleModel = \App\Models\Role::firstOrCreate(['name' => 'logistics']);
                $user->roles()->syncWithoutDetaching([$roleModel->id]);
            }

            // If registering as Courier / Rider, create corresponding Rider record
            if ($user->isCourier()) {
                if (!empty($validated['logistics_provider_id'])) {
                    \App\Models\Rider::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'logistics_provider_id' => $validated['logistics_provider_id'],
                            'vehicle_type'          => $validated['vehicle_type'] ?? null,
                            'plate_no'              => !empty($validated['plate_no']) ? strtoupper(trim($validated['plate_no'])) : null,
                            'status'                => 'pending',
                            'is_active'             => false,
                        ]
                    );
                }

                $roleModel = \App\Models\Role::firstOrCreate(['name' => 'courier']);
                $user->roles()->syncWithoutDetaching([$roleModel->id]);
            }

            // Clear verified OTP
            \Illuminate\Support\Facades\DB::commit();
            Cache::forget('otp_' . $email);

            // Auto-sync DB dump
            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();

            // Buyers get instant access to shop without needing admin approval
            if ($user->isBuyer()) {
                Auth::login($user);
                $request->session()->regenerate();
                ActivityLog::record(ActivityLog::LOGIN, $request, $user, ['method' => 'registration']);
                return redirect()->route('home')
                    ->with('success', 'Welcome to Cartzy, ' . $user->name . '! Your account is active. Start shopping now!');
            }

            // Sellers, Logistics & Couriers require Admin approval
            return redirect()->route('register.pending')
                ->with('pending_name', $user->name)
                ->with('pending_email', $user->email)
                ->with('pending_role', $user->role);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->withErrors([
                'email' => 'Registration failed: ' . $e->getMessage(),
            ]);
        }
    }

    /** Legacy demo links now use the regular account sign-in flow. */
    public function demoLogin(Request $request, $role = 'admin')
    {
        return redirect()->route('login');
    }
    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        $loginRoute = self::loginRouteFor($request->user()?->role);
        if ($request->user()) {
            ActivityLog::record(ActivityLog::LOGOUT, $request, $request->user());
        }
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($loginRoute)
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => 'Sun, 02 Jan 1990 00:00:00 GMT',
                'Clear-Site-Data' => '"cache"',
            ]);
    }

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(Request $request)
    {
        $portals = self::loginPortals();
        $portal = $request->query('portal', User::ROLE_BUYER);
        if (!array_key_exists((string) $portal, $portals)) {
            $portal = User::ROLE_BUYER;
        }

        session(['auth_google_portal' => $portal]);

        $portalRoute = $portals[$portal]['route'] ?? 'login';

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route($portalRoute)->withErrors([
                'email' => 'Google Sign-In is not configured yet. Please configure GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env file.',
            ]);
        }

        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(Request $request)
    {
        $portals = self::loginPortals();
        $portal = session()->pull('auth_google_portal', User::ROLE_BUYER);
        if (!array_key_exists((string) $portal, $portals)) {
            $portal = User::ROLE_BUYER;
        }
        $portalRoute = $portals[$portal]['route'] ?? 'login';

        if ($request->has('error')) {
            return redirect()->route($portalRoute)->withErrors([
                'email' => 'Google authentication was cancelled or encountered an error.',
            ]);
        }

        try {
            // Use stateless to bypass InvalidStateException caused by cross-domain redirects or session loss
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
            } catch (\Throwable $statelessException) {
                $googleUser = Socialite::driver('google')->user();
            }
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage() ?: 'Session state expired or authentication was interrupted. Please try again.';
            Log::error('Google OAuth error: ' . $errorMsg);
            return redirect()->route($portalRoute)->withErrors([
                'email' => 'Unable to authenticate with Google: ' . $errorMsg,
            ]);
        }

        if (!$googleUser || !$googleUser->getEmail()) {
            return redirect()->route($portalRoute)->withErrors([
                'email' => 'Could not retrieve email address from Google. Please sign in or register manually.',
            ]);
        }

        $email = strtolower(trim($googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();
        $name = $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Google User');
        $avatar = $googleUser->getAvatar();

        try {
            // 1. Find user by google_id
            $user = User::where('google_id', $googleId)->first();

            if ($user) {
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->save();
                }
            } else {
                // 2. Check if an account with this email already exists
                $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

                if ($user) {
                    $user->google_id = $googleId;
                    if (empty($user->avatar) && $avatar) {
                        $user->avatar = $avatar;
                    }
                    if (!$user->email_verified_at) {
                        $user->email_verified_at = now();
                    }
                    $user->save();
                } else {
                    // No existing account found for this Google email
                    if ($portal === User::ROLE_ADMIN) {
                        return redirect()->route('login.admin')->withErrors([
                            'email' => 'No administrator account found with email ' . $email . '. Admin accounts cannot be self-registered.',
                        ]);
                    }

                    if ($portal === User::ROLE_SELLER) {
                        return redirect()->route('register', ['role' => User::ROLE_SELLER])->withInput([
                            'email' => $email,
                            'name' => $name,
                            'role' => User::ROLE_SELLER,
                        ])->withErrors([
                            'email' => 'No seller account found with email ' . $email . '. Please complete your seller store registration below.',
                        ]);
                    }

                    if ($portal === User::ROLE_LOGISTICS) {
                        return redirect()->route('register', ['role' => User::ROLE_LOGISTICS])->withInput([
                            'email' => $email,
                            'name' => $name,
                            'role' => User::ROLE_LOGISTICS,
                        ])->withErrors([
                            'email' => 'No logistics account found with email ' . $email . '. Please complete your registration below.',
                        ]);
                    }

                    if ($portal === User::ROLE_COURIER) {
                        return redirect()->route('register.rider')->withInput([
                            'email' => $email,
                            'name' => $name,
                        ])->withErrors([
                            'email' => 'No courier rider account found with email ' . $email . '. Please submit your rider application first.',
                        ]);
                    }

                    // Buyer registration (Google does not provide birthday: create buyer account directly)
                    $user = \Illuminate\Support\Facades\DB::transaction(function () use ($email, $name, $googleId, $avatar) {
                        $created = User::create([
                            'name'              => $name,
                            'email'             => $email,
                            'google_id'         => $googleId,
                            'avatar'            => $avatar,
                            'role'              => User::ROLE_BUYER,
                            'status'            => 'active',
                            'email_verified_at' => now(),
                            'password'          => Hash::make(\Illuminate\Support\Str::random(32)),
                        ]);

                        \App\Models\Buyer::firstOrCreate(
                            ['user_id' => $created->id],
                            [
                                'name'      => $created->name,
                                'id_status' => 'unverified',
                                'status'    => 'active',
                            ]
                        );

                        $roleModel = \App\Models\Role::firstOrCreate(['name' => 'buyer']);
                        $created->roles()->syncWithoutDetaching([$roleModel->id]);

                        return $created;
                    });
                }
            }

            // Portal matching: verify the user's role matches the portal they are logging into
            if ($user->role !== $portal) {
                ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, $user, ['method' => 'google', 'description' => 'Wrong login portal ('.$portal.')']);
                $correct = $portals[$user->role] ?? null;
                return redirect()->route($portalRoute)->withErrors([
                    'email' => 'This login page is for '.strtolower($portals[$portal]['label']).' accounts.'
                        .($correct ? ' Please use the '.$correct['label'].' login instead.' : ''),
                ])->with('portal_hint', $correct ? ['label' => $correct['label'], 'url' => route($correct['route'])] : null);
            }

            // Ensure account is not suspended/rejected/pending
            if (in_array($user->status, ['suspended', 'deactivated'], true) || $user->is_suspended) {
                ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, $user, ['method' => 'google', 'description' => 'Account suspended']);
                return redirect()->route($portalRoute)->withErrors([
                    'email' => 'Your account is suspended. Please contact platform support for assistance.',
                ]);
            }

            if ($user->status === 'rejected') {
                ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, $user, ['method' => 'google', 'description' => 'Account rejected']);
                return redirect()->route($portalRoute)->withErrors([
                    'email' => 'Your account application was disapproved by the administrator.',
                ]);
            }

            if ($user->status === 'pending') {
                if ($user->isSeller()) {
                    Auth::login($user, true);
                    $request->session()->regenerate();
                    ActivityLog::record(ActivityLog::LOGIN, $request, $user, ['method' => 'google', 'description' => 'Pending seller account']);
                    return redirect()->intended(route($user->getDashboardRoute()))
                        ->with('info', 'Welcome, ' . ($user->name ?? 'Seller') . '! Your account is currently pending administrator verification. You can view your dashboard and store settings, but adding products and selling are locked until approved.');
                }

                ActivityLog::record(ActivityLog::FAILED_LOGIN, $request, $user, ['method' => 'google', 'description' => 'Account pending approval']);
                return redirect()->route($portalRoute)->withErrors([
                    'email' => 'Your account registration is currently pending admin verification.',
                ]);
            }

            // Log user in
            Auth::login($user, true);
            $request->session()->regenerate();
            ActivityLog::record(ActivityLog::LOGIN, $request, $user, ['method' => 'google']);

            return redirect()->intended(route($user->getDashboardRoute()))
                ->with('success', 'Welcome, ' . ($user->name ?? $portals[$user->role]['label']) . '! Signed in successfully with Google.');

        } catch (\Exception $e) {
            Log::error('Google Auth Processing Error: ' . $e->getMessage());
            return redirect()->route($portalRoute)->withErrors([
                'email' => 'Authentication failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset OTP to user email.
     */
    public function sendResetOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $email = strtolower(trim($validated['email']));

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        // Generate secure 6-digit OTP
        $otpCode = (string) str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Store in cache for 15 minutes
        Cache::put('pwd_reset_otp_' . $email, [
            'otp' => $otpCode,
            'name' => $user->name,
            'created_at' => now(),
        ], now()->addMinutes(15));

        // Dispatch Email
        $mailSent = false;
        try {
            Mail::send('emails.password-reset-otp', [
                'name' => $user->name,
                'otp'  => $otpCode,
                'email'=> $email,
            ], function ($message) use ($email, $otpCode) {
                $message->to($email)
                    ->subject('Your cartzy Password Reset Code [' . $otpCode . ']');
            });
            $mailSent = true;
        } catch (\Exception $e) {
            Log::info("Password reset OTP for {$email}: {$otpCode} (Mail driver: " . config('mail.default') . "). Note: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'A 6-digit verification code has been sent to your email address.',
            'email' => $email,
            'otp_preview' => config('app.debug') ? $otpCode : null,
        ]);
    }

    /**
     * Verify the 6-digit password reset OTP.
     */
    public function verifyResetOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp'   => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $email = strtolower(trim($validated['email']));
        $enteredOtp = trim($validated['otp']);

        $cached = Cache::get('pwd_reset_otp_' . $email);

        if (!$cached || $cached['otp'] !== $enteredOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code. Please check your email.',
            ], 422);
        }

        // Mark as verified for 15 minutes
        Cache::put('pwd_reset_verified_' . $email, true, now()->addMinutes(15));

        return response()->json([
            'success' => true,
            'message' => 'Verification code confirmed. You can now set your new password.',
        ]);
    }

    /**
     * Handle final password reset submission.
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'otp'      => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'Please enter a new password.',
            'password.min'       => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $email = strtolower(trim($validated['email']));
        $enteredOtp = trim($validated['otp']);

        $cached = Cache::get('pwd_reset_otp_' . $email);
        $isVerified = Cache::get('pwd_reset_verified_' . $email, false);

        if ((!$cached || $cached['otp'] !== $enteredOtp) && !$isVerified) {
            return back()->withInput()->withErrors([
                'email' => 'Your verification session has expired. Please request a new code.',
            ]);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withInput()->withErrors([
                'email' => 'User account could not be found.',
            ]);
        }

        // Update password
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Clear cached OTP tokens
        Cache::forget('pwd_reset_otp_' . $email);
        Cache::forget('pwd_reset_verified_' . $email);

        // Auto-sync SQL dump if helper exists
        if (class_exists(\App\Console\Commands\ExportDatabaseSql::class)) {
            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
        }

        // If current authenticated admin resets password from their account, redirect back to admin account
        if (Auth::check() && Auth::id() === $user->id && $user->isAdmin()) {
            ActivityLog::record('password_reset', $request, $user, ['description' => 'Password reset via OTP']);
            return redirect()->route('admin.account')->with('success', 'Your password has been reset and updated successfully!');
        }

        $portal = $request->input('portal');
        $loginRoute = ($portal && in_array($portal, [User::ROLE_ADMIN, User::ROLE_SELLER, User::ROLE_LOGISTICS, User::ROLE_COURIER], true))
            ? self::loginRouteFor($portal)
            : self::loginRouteFor($user->role);

        return redirect()->route($loginRoute)->with('success', 'Your password has been reset successfully! You can now log in.');
    }
}
