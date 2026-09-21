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

        // Check for official Admin credentials (works immediately with real DB user)
        if ($credentials['email'] === 'administrationa570@gmail.com' && $credentials['password'] === 'caramelmacchiato') {
            try {
                $user = User::where('email', 'administrationa570@gmail.com')->first()
                    ?? User::where('role', User::ROLE_ADMIN)->first();

                if (!$user) {
                    $user = User::create([
                        'name' => 'admin',
                        'email' => 'administrationa570@gmail.com',
                        'password' => Hash::make('caramelmacchiato'),
                        'role' => User::ROLE_ADMIN,
                        'status' => 'active',
                    ]);
                }

                Auth::login($user, $remember);
            } catch (\Throwable $e) {
                $user = new User();
                $user->forceFill([
                    'id' => 5,
                    'name' => 'admin',
                    'email' => 'administrationa570@gmail.com',
                    'role' => User::ROLE_ADMIN,
                    'status' => 'active',
                ]);
                Auth::login($user, $remember);
            }

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, Admin!');
        }

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

                if ($user->status === 'suspended') {
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
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string'],
            'role' => ['nullable', 'string'],
        ]);

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
                    ->subject('Your Cartzy Email Verification Code [' . $otpCode . ']');
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
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'middle_initial'       => ['nullable', 'string', 'max:5'],
            'email'                => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'sex'                  => ['nullable', 'string', 'in:Male,Female,Prefer not to say'],
            'birthday'             => ['nullable', 'date', 'before:today'],
            'phone'                => ['nullable', 'string', 'max:20'],
            'street_address'       => ['nullable', 'string', 'max:500'],
            'address'              => ['nullable', 'string', 'max:500'],
            'region'               => ['nullable', 'string', 'max:100'],
            'province'             => ['nullable', 'string', 'max:100'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'barangay'             => ['nullable', 'string', 'max:100'],
            'postal_code'          => ['nullable', 'string', 'max:20'],
            'id_photo'             => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'role'                 => ['required', 'string', Rule::in([User::ROLE_BUYER, User::ROLE_SELLER, User::ROLE_COURIER])],
            'password'             => ['required', 'string', 'confirmed', Password::min(6)],
            'email_verification_otp' => ['required', 'string', 'min:6', 'max:6'],
        ], [
            'name.required'        => 'Please enter your full name.',
            'email.required'       => 'Please enter an email address.',
            'email.unique'         => 'This email is already registered. Please log in instead.',
            'sex.in'               => 'Please select a valid sex option.',
            'birthday.before'      => 'Birthday must be a past date.',
            'role.required'        => 'Please choose an account type.',
            'role.in'              => 'Invalid account type selected.',
            'password.required'    => 'Please provide a password.',
            'password.confirmed'   => 'The password confirmation does not match.',
            'password.min'         => 'Password must be at least 6 characters long.',
            'id_photo.mimes'       => 'ID must be a JPG, PNG, or PDF file.',
            'id_photo.max'         => 'ID file must not exceed 5MB.',
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

        // Handle ID photo upload
        $idPhotoPath = null;
        if ($request->hasFile('id_photo') && $request->file('id_photo')->isValid()) {
            $idPhotoPath = $request->file('id_photo')->store('id_photos', 'public');
        }

        try {
            $status = ($validated['role'] === User::ROLE_BUYER) ? 'active' : 'pending';

            $user = User::create([
                'name'           => $validated['name'],
                'middle_initial' => $validated['middle_initial'] ?? null,
                'email'          => $validated['email'],
                'sex'            => $validated['sex'] ?? null,
                'birthday'       => $validated['birthday'] ?? null,
                'age'            => $age,
                'phone'          => $validated['phone'] ?? null,
                'address'        => $fullAddress ?: $street,
                'street_address' => $street,
                'region'         => $region,
                'province'       => $province,
                'city'           => $city,
                'barangay'       => $barangay,
                'postal_code'    => $postalCode,
                'id_photo'       => $idPhotoPath,
                'role'           => $validated['role'],
                'status'         => $status,
                'password'       => Hash::make($validated['password']),
            ]);

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

            // Sellers & Couriers still require KYC verification
            return redirect()->route('register.pending')
                ->with('pending_name', $user->name)
                ->with('pending_email', $user->email);

        } catch (\Exception $e) {
            return back()->withInput()->withErrors([
                'email' => 'Registration failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle fast demo login without requiring MySQL/XAMPP database.
     */
    public function demoLogin(Request $request, $role = 'admin')
    {
        $userData = match ($role) {
            'seller' => [
                'id' => 2,
                'name' => 'Maria Santos (TechZone Store)',
                'email' => 'seller@marketstore.ph',
                'role' => User::ROLE_SELLER,
            ],
            'courier' => [
                'id' => 3,
                'name' => 'Arnel Gomez (Express Rider)',
                'email' => 'courier@marketstore.ph',
                'role' => User::ROLE_COURIER,
            ],
            default => [
                'id' => 1,
                'name' => 'admin',
                'email' => 'administrationa570@gmail.com',
                'role' => User::ROLE_ADMIN,
            ],
        };

        // Authenticate matching user from database
        try {
            $user = User::where('email', $userData['email'])->first()
                ?? User::where('role', $userData['role'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password123'),
                    'role' => $userData['role'],
                    'status' => 'active',
                ]);
            }

            Auth::login($user);
        } catch (\Throwable $e) {
            $user = new User();
            $user->forceFill($userData);
            Auth::login($user);
        }

        $request->session()->regenerate();

        return redirect()->route($user->getDashboardRoute())
            ->with('success', 'Welcome back, ' . ($user->name ?? 'User') . '!');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('info', 'You have been logged out from Admin Portal.');
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

        return Socialite::driver('google')->redirect();
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
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to authenticate with Google: ' . $e->getMessage(),
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

            if (!$user) {
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
                    // 3. Create a brand new buyer account
                    $user = User::create([
                        'name'              => $name,
                        'email'             => $email,
                        'google_id'         => $googleId,
                        'avatar'            => $avatar,
                        'role'              => User::ROLE_BUYER,
                        'status'            => 'active',
                        'email_verified_at' => now(),
                        'password'          => Hash::make(Str::random(32)),
                    ]);

                    // Sync MySQL dump file
                    if (class_exists(\App\Console\Commands\ExportDatabaseSql::class)) {
                        \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
                    }
                }
            }

            // Ensure account is not suspended/rejected/pending
            if ($user->status === 'suspended') {
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
}
