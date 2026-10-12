<?php

namespace App\Http\Controllers;

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
    private function normalizeSellerContact(Request $request): void
    {
        if ($request->input('role') === User::ROLE_SELLER && is_string($request->input('email'))) {
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
        return [...$this->mobilePhoneRules(),
            function ($attribute, $value, $fail) {
                if (User::whereIn('phone', [$value, '+63' . substr($value, 1)])->exists()) {
                    $fail('This mobile number is already in use.');
                }
            },
        ];
    }

    private function mobilePhoneRules(): array
    {
        return ['bail', 'nullable', 'string', 'regex:/\A[0-9]+\z/', 'size:11', 'starts_with:09'];
    }

    private function sellerEmailRules(): array
    {
        return [
            'bail', 'required', 'string', 'max:255', 'email',
            'regex:/\A[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+\z/',
            function ($attribute, $value, $fail) {
                if (User::whereRaw('LOWER(email) = ?', [strtolower($value)])->exists()) {
                    $fail('This email is already in use. Please log in instead.');
                }
            },
        ];
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
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

        try {
            if (Auth::attempt($credentials, $remember)) {
                $user = Auth::user();

                if ($user->status === 'pending') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Your account registration is currently pending admin verification. Please wait for an administrator to approve your application.',
                    ])->onlyInput('email');
                }

                if ($user->status === 'rejected') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Your account application was disapproved by the administrator.',
                    ])->onlyInput('email');
                }

                if (in_array($user->status, ['suspended', 'deactivated'], true) || $user->is_suspended) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Your account is suspended. Please contact platform support for assistance.',
                    ])->onlyInput('email');
                }

                $request->session()->regenerate();

                // Redirect to role-specific dashboard or intended URL
                return redirect()->intended(route($user->getDashboardRoute()))
                    ->with('success', 'Welcome back, ' . ($user->name ?? 'User') . '!');
            }
        } catch (\Exception $e) {
            // Fallback if DB connection fails
        }

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
        return view('auth.register', compact('selectedRole'));
    }

    /**
     * Handle AJAX request to automatically generate and send OTP to applicant email.
     */
    public function requestOtp(Request $request)
    {
        $this->normalizeSellerContact($request);
        $validated = $request->validate([
            'email' => $request->input('role') === User::ROLE_SELLER
                ? $this->sellerEmailRules() : ['required', 'email'],
            'name' => ['nullable', 'string'],
            'role' => ['nullable', 'string'],
            'phone' => $request->input('role') === User::ROLE_SELLER ? $this->sellerPhoneRules() : $this->mobilePhoneRules(),
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

        // Store OTP code in Cache (valid for 15 minutes)
        Cache::put('otp_' . $email, $otpItem, now()->addMinutes(15));

        // Keep in registration queue history
        $otps = Cache::get('registration_otps', []);
        $otps = array_values(array_filter($otps, fn($i) => strtolower($i['email']) !== $email));
        array_unshift($otps, $otpItem);
        Cache::put('registration_otps', array_slice($otps, 0, 50), now()->addHours(24));

        // Automatically dispatch email
        $mailSent = false;
        try {
            Mail::send('emails.otp-verification', [
                'name' => $name,
                'otp' => $otpCode,
                'email' => $email,
            ], function ($message) use ($email, $otpCode) {
                $message->to($email)
                    ->subject('Your cartzy Email Verification Code [' . $otpCode . ']');
            });
            $mailSent = true;
        } catch (\Exception $e) {
            Log::info("Automatic OTP for {$email}: {$otpCode} (Mail driver: " . config('mail.default') . "). Note: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => '6-digit verification code has been automatically sent to your email address.',
            'email' => $email,
            'otp_preview' => config('app.debug') ? $otpCode : null, // Helper for local dev inspection if mailer is log
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

        if (!$otpItem) {
            $otps = Cache::get('registration_otps', []);
            foreach ($otps as $item) {
                if (strtolower($item['email']) === $email) {
                    $otpItem = $item;
                    break;
                }
            }
        }

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
        $this->normalizeSellerContact($request);
        $nameRules = [
            'first_name' => ['required', 'string', 'max:255', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
        ];
        if (is_string($request->input('first_name')) && is_string($request->input('last_name'))) {
            $request->merge(['name' => trim($request->input('first_name') . ' ' . $request->input('last_name'))]);
        }

        $validated = $request->validate([
            ...$nameRules,
            'name'                 => ['required', 'string', 'max:255'],
            'middle_initial'       => ['nullable', 'string', 'max:5'],
            'email'                => $request->input('role') === User::ROLE_SELLER
                ? $this->sellerEmailRules() : ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'sex'                  => ['nullable', 'string', 'in:Male,Female,Prefer not to say'],
            'birthday'             => ['exclude_if:role,logistics', 'bail', 'required', 'date_format:Y-m-d', 'before_or_equal:' . now()->subYearsNoOverflow(18)->toDateString()],
            'phone'                => $request->input('role') === User::ROLE_SELLER
                ? $this->sellerPhoneRules()
                : $this->mobilePhoneRules(),
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
            'password'             => ['required', 'string', 'confirmed', $request->input('role') === User::ROLE_SELLER
                ? Password::min(8)->mixedCase()->numbers()->symbols() : Password::min(6)],
            'email_verification_otp' => ['required', 'string', 'min:6', 'max:6'],
        ], [
            'name.required'        => 'Please enter your full name.',
            'first_name.required'  => 'Please enter your first name.',
            'last_name.required'   => 'Please enter your last name.',
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
            'password.min'         => $request->input('role') === User::ROLE_SELLER
                ? 'Password must be at least 8 characters long.' : 'Password must be at least 6 characters long.',
            'id_photo.mimes'       => 'ID must be a JPG, PNG, or PDF file.',
            'id_photo.max'         => 'ID file must not exceed 5MB.',
            'dti_permit.mimes'     => 'Business / DTI permit must be a JPG, PNG, or PDF file.',
            'dti_permit.max'       => 'Business / DTI permit must not exceed 5MB.',
            'email_verification_otp.required' => 'Please enter the 6-digit verification code.',
            'email_verification_otp.min'      => 'Please enter the complete 6-digit code.',
        ]);

        // Validate OTP
        $email      = strtolower(trim($validated['email']));
        $enteredOtp = trim($validated['email_verification_otp']);
        $otpItem    = Cache::get('otp_' . $email);

        if (!$otpItem) {
            $otps = Cache::get('registration_otps', []);
            foreach ($otps as $item) {
                if (strtolower($item['email']) === $email) {
                    $otpItem = $item;
                    break;
                }
            }
        }

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

        try {
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
                'business_name'    => in_array($validated['role'], [User::ROLE_SELLER, User::ROLE_LOGISTICS], true) ? ($validated['business_name'] ?? null) : null,
                'line_of_business' => $validated['role'] === User::ROLE_SELLER ? ($validated['line_of_business'] ?? null) : null,
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

                \App\Models\Seller::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name'   => $user->business_name ?: $user->name,
                        'slug'   => $uniqueSlug,
                        'status' => 'pending',
                    ]
                );

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

            // Clear verified OTP
            Cache::forget('otp_' . $email);

            // Auto-sync DB dump
            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();

            // Buyers get instant access to shop without needing admin approval
            if ($user->isBuyer()) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('home')
                    ->with('success', 'Welcome to Cartzy, ' . $user->name . '! Your account is active. Start shopping now!');
            }

            // Sellers, Logistics & Couriers require Admin approval
            return redirect()->route('register.pending')
                ->with('pending_name', $user->name)
                ->with('pending_email', $user->email)
                ->with('pending_role', $user->role);

        } catch (\Exception $e) {
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
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
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
    public function redirectToGoogle()
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->withErrors([
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
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors([
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
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to authenticate with Google: ' . $errorMsg,
            ]);
        }

        if (!$googleUser || !$googleUser->getEmail()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Could not retrieve email address from Google. Please sign up manually.',
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
                $user = User::where('email', $email)->first();

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
                    // Google does not provide a birthday: require age-checked registration.
                    return redirect()->route('register')->withInput([
                        'email' => $email,
                        'role' => User::ROLE_BUYER,
                    ])->withErrors([
                        'birthday' => 'Please complete registration with your birthday. You must be at least 18 years old to register.',
                    ]);
                }
            }

            // Ensure account is not suspended/rejected/pending
            if (in_array($user->status, ['suspended', 'deactivated'], true) || $user->is_suspended) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account is suspended. Please contact platform support.',
                ]);
            }

            if ($user->status === 'rejected') {
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account application was disapproved by the administrator.',
                ]);
            }

            if ($user->status === 'pending') {
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account registration is currently pending admin verification.',
                ]);
            }

            // Log user in
            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route($user->getDashboardRoute()))
                ->with('success', 'Welcome, ' . $user->name . '! Signed in successfully with Google.');

        } catch (\Exception $e) {
            Log::error('Google Auth Processing Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
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

        return redirect()->route('login')->with('success', 'Your password has been reset successfully! You can now log in.');
    }
}
