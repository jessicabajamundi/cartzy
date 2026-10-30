<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Token login for the Cartzy mobile app. Uses the same users table and rules as the website.
 */
class AuthController extends Controller
{
    /** The mobile app calls couriers "riders". */
    private const MOBILE_ROLES = ['buyer' => User::ROLE_BUYER, 'rider' => User::ROLE_COURIER, 'courier' => User::ROLE_COURIER,
        'seller' => User::ROLE_SELLER, 'logistics' => User::ROLE_LOGISTICS, 'admin' => User::ROLE_ADMIN];

    public static function userJson(User $user): array
    {
        $parts = preg_split('/\s+/', trim((string) $user->name), 2);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role === User::ROLE_COURIER ? 'rider' : $user->role,
            'status' => $user->status,
            'email_verified' => $user->email_verified_at !== null,
            'birthday' => $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') : null,
        ];
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['nullable', Rule::in(array_keys(self::MOBILE_ROLES))],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', strtolower(trim($data['email'])))->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'The provided credentials do not match our records.'], 422);
        }

        if (isset($data['role']) && self::MOBILE_ROLES[$data['role']] !== $user->role) {
            return response()->json([
                'message' => 'This account is not a '.$data['role'].' account.',
                'account_role' => $user->role === User::ROLE_COURIER ? 'rider' : $user->role,
            ], 403);
        }
        if ($user->status === 'pending' && !$user->isSeller()) {
            return response()->json(['message' => 'Your account registration is still pending admin verification.'], 403);
        }
        if ($user->status === 'rejected') {
            return response()->json(['message' => 'Your account application was disapproved by the administrator.'], 403);
        }
        if (in_array($user->status, ['suspended', 'deactivated'], true) || $user->is_suspended) {
            return response()->json(['message' => 'Your account is suspended. Please contact platform support.'], 403);
        }

        $token = $user->createToken($data['device_name'] ?? 'cartzy-mobile')->plainTextToken;

        return response()->json(['token' => $token, 'user' => self::userJson($user)]);
    }

    /** Same email OTP as the website: request a code, then register with it. */
    public function requestOtp(Request $request)
    {
        $webhookUrl = trim((string) config('services.mail_webhook.url', ''));
        if ($request->boolean('client_send') && $webhookUrl === '') {
            if (is_string($request->input('email'))) {
                $request->merge(['email' => strtolower(trim($request->input('email')))]);
            }
            if (is_string($request->input('phone'))) {
                $phone = trim($request->input('phone'));
                if (preg_match('/\A\+639[0-9]{9}\z/', $phone)) {
                    $phone = '0' . substr($phone, 3);
                } elseif (preg_match('/\A9[0-9]{9}\z/', $phone)) {
                    $phone = '0' . $phone;
                }
                $request->merge(['phone' => $phone]);
            }

            $validated = $request->validate([
                'email' => [
                    'bail', 'required', 'string', 'max:255', 'email',
                    'regex:/\A[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+\z/',
                    function ($attribute, $value, $fail) {
                        if (User::whereRaw('LOWER(email) = ?', [strtolower($value)])->exists()) {
                            $fail('This email is already in use. Please log in instead. To register another account, use a different email address.');
                        }
                    },
                ],
                'name' => ['nullable', 'string'],
                'role' => ['nullable', 'string'],
                'phone' => [
                    'bail', 'nullable', 'string', 'regex:/\A[0-9]+\z/', 'size:11', 'starts_with:09',
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
                ],
            ], [
                'email.email' => 'Please enter a valid email address.',
                'email.regex' => 'Please enter a valid email address.',
            ]);

            $email = strtolower(trim($validated['email']));
            $name = trim($validated['name'] ?? 'Applicant');
            $role = $validated['role'] ?? 'buyer';
            $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            $otpItem = [
                'id' => (string) str_replace(['.', '@'], '', $email) . '_' . time(),
                'email' => $email,
                'name' => $name,
                'role' => $role,
                'otp' => $otpCode,
                'status' => 'sent',
                'requested_at' => now()->format('M d, Y h:i A'),
                'sent_at' => now()->format('M d, Y h:i A'),
            ];

            Cache::put('otp_' . $email, $otpItem, now()->addMinutes(10));

            return response()->json([
                'success' => true,
                'message' => 'A 6-digit verification code has been sent to your email address.',
                'email' => $email,
                'mail_sent' => false,
                'otp_code' => $otpCode,
            ]);
        }

        return app(\App\Http\Controllers\AuthController::class)->requestOtp($request);
    }

    public function verifyOtp(Request $request)
    {
        return app(\App\Http\Controllers\AuthController::class)->verifyOtp($request);
    }

    /** Forgot password: same emailed reset code as the website, then a JSON reset. */
    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['nullable', Rule::in(array_keys(self::MOBILE_ROLES))],
        ]);

        $user = User::where('email', strtolower(trim($data['email'])))->first();
        if ($user && isset($data['role']) && self::MOBILE_ROLES[$data['role']] !== $user->role) {
            return response()->json([
                'message' => 'This email belongs to a different account type. Use the '
                    .($user->role === User::ROLE_COURIER ? 'Courier' : ucfirst($user->role)).' login instead.',
                'account_role' => $user->role === User::ROLE_COURIER ? 'rider' : $user->role,
            ], 403);
        }

        $webhookUrl = trim((string) config('services.mail_webhook.url', ''));
        if ($request->boolean('client_send') && $webhookUrl === '') {
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }
            $email = strtolower(trim($data['email']));
            $otpCode = (string) str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put('pwd_reset_otp_' . $email, [
                'otp' => $otpCode,
                'name' => $user->name,
                'created_at' => now(),
            ], now()->addMinutes(15));

            return response()->json([
                'success' => true,
                'message' => 'A 6-digit verification code has been sent to your email address.',
                'email' => $email,
                'name' => $user->name,
                'mail_sent' => false,
                'otp_code' => $otpCode,
            ]);
        }

        return app(\App\Http\Controllers\AuthController::class)->sendResetOtp($request);
    }

    public function verifyResetCode(Request $request)
    {
        return app(\App\Http\Controllers\AuthController::class)->verifyResetOtp($request);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $email = strtolower(trim($data['email']));
        $cached = Cache::get('pwd_reset_otp_'.$email);
        if (!$cached || $cached['otp'] !== $data['otp']) {
            return response()->json([
                'message' => 'Your verification code has expired. Please request a new code.',
                'errors' => ['otp' => ['Your verification code has expired. Please request a new code.']],
            ], 422);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'User account could not be found.'], 404);
        }

        $user->password = Hash::make($data['password']);
        $user->save();
        // Sign out every device that used the old password.
        $user->tokens()->delete();

        Cache::forget('pwd_reset_otp_'.$email);
        Cache::forget('pwd_reset_verified_'.$email);

        if (class_exists(\App\Console\Commands\ExportDatabaseSql::class)) {
            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
        }

        return response()->json(['message' => 'Your password has been reset. You can now log in.']);
    }

    public function registerBuyer(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'last_name' => ['required', 'string', 'max:50', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'regex:/^09\d{9}$/', Rule::unique('users', 'phone')],
            'birthday' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now()->subYearsNoOverflow(18)->toDateString()],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'otp' => ['required', 'digits:6'],
        ], [
            'phone.regex' => 'Enter an 11-digit mobile number starting with 09.',
            'birthday.before_or_equal' => 'You must be at least 18 years old to register.',
        ]);

        $email = strtolower(trim($data['email']));
        $otp = Cache::get('otp_'.$email);
        if (!$otp || $otp['otp'] !== $data['otp']) {
            return response()->json(['message' => 'Invalid or expired verification code.', 'errors' => ['otp' => ['Invalid or expired verification code.']]], 422);
        }

        $user = DB::transaction(function () use ($data, $email) {
            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $email,
                'phone' => $data['phone'],
                'birthday' => $data['birthday'],
                'age' => \Carbon\Carbon::parse($data['birthday'])->age,
                'role' => User::ROLE_BUYER,
                'status' => 'active',
                'email_verified_at' => now(),
                'password' => Hash::make($data['password']),
            ]);
            \App\Models\Buyer::firstOrCreate(['user_id' => $user->id], [
                'name' => $user->name, 'birthday' => $user->birthday, 'age' => $user->age, 'phone' => $user->phone,
                'id_status' => 'unverified', 'status' => 'active',
            ]);
            $user->roles()->syncWithoutDetaching([\App\Models\Role::firstOrCreate(['name' => 'buyer'])->id]);

            return $user;
        });
        Cache::forget('otp_'.$email);

        return response()->json(['token' => $user->createToken('cartzy-mobile')->plainTextToken, 'user' => self::userJson($user)], 201);
    }

    public function registerRider(Request $request)
    {
        if (is_string($request->input('vehicle_type'))) {
            $vt = strtolower(str_replace('-', '', trim($request->input('vehicle_type'))));
            $request->merge(['vehicle_type' => $vt]);
        }
        if (is_string($request->input('license_number'))) {
            $request->merge(['license_number' => strtoupper(trim($request->input('license_number')))]);
        }

        $nonMotorized = in_array($request->input('vehicle_type'), ['bicycle', 'ebike'], true);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'last_name' => ['required', 'string', 'max:50', 'regex:/\A(?:\p{L}\p{M}*| )+\z/u'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'regex:/^09\d{9}$/', Rule::unique('users', 'phone')],
            'birthday' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now()->subYearsNoOverflow(18)->toDateString()],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'otp' => ['required', 'digits:6'],
            'logistics_partner' => ['nullable', 'string', 'max:255'],
            'logistics_provider_id' => ['nullable', 'integer', 'exists:logistics_providers,id'],
            'vehicle_type' => ['required', 'string', Rule::in(['motorcycle', 'bicycle', 'ebike', 'tricycle', 'car', 'van', 'truck'])],
            'plate_no' => [
                Rule::requiredIf(fn () => !$nonMotorized),
                'nullable', 'string', 'min:5', 'max:8', 'regex:/^[a-zA-Z0-9]+$/',
            ],
            'license_number' => [
                Rule::requiredIf(fn () => !$nonMotorized),
                'nullable', 'string', 'size:13', 'regex:/^[A-Za-z][0-9]{2}-[0-9]{2}-[0-9]{6}$/',
            ],
            'license_expiry' => [
                Rule::requiredIf(fn () => !$nonMotorized),
                'nullable', 'date',
            ],
            'license_doc' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'or_cr_doc' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'phone.regex' => 'Enter an 11-digit mobile number starting with 09.',
            'birthday.before_or_equal' => 'You must be at least 18 years old to register.',
        ]);

        $email = strtolower(trim($data['email']));
        $otp = Cache::get('otp_'.$email);
        if (!$otp || $otp['otp'] !== $data['otp']) {
            return response()->json(['message' => 'Invalid or expired verification code.', 'errors' => ['otp' => ['Invalid or expired verification code.']]], 422);
        }

        if ($request->hasFile('license_doc') && $request->file('license_doc')->isValid()) {
            $request->file('license_doc')->store('rider_documents', 'public');
        }
        if ($request->hasFile('or_cr_doc') && $request->file('or_cr_doc')->isValid()) {
            $request->file('or_cr_doc')->store('rider_documents', 'public');
        }

        $providerId = $data['logistics_provider_id'] ?? null;
        if (!$providerId && !empty($data['logistics_partner'])) {
            $providerId = \App\Models\LogisticsProvider::whereRaw('LOWER(name) = ?', [strtolower(trim($data['logistics_partner']))])->value('id');
        }
        if (!$providerId) {
            $providerId = \App\Models\LogisticsProvider::where('status', 'approved')->value('id')
                ?? \App\Models\LogisticsProvider::value('id');
        }

        $user = DB::transaction(function () use ($data, $email, $providerId) {
            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $email,
                'phone' => $data['phone'],
                'birthday' => $data['birthday'],
                'age' => \Carbon\Carbon::parse($data['birthday'])->age,
                'role' => User::ROLE_COURIER,
                'status' => 'pending',
                'email_verified_at' => now(),
                'password' => Hash::make($data['password']),
            ]);

            if ($providerId) {
                \App\Models\Rider::firstOrCreate(['user_id' => $user->id], [
                    'logistics_provider_id' => $providerId,
                    'vehicle_type' => $data['vehicle_type'],
                    'plate_no' => !empty($data['plate_no']) ? strtoupper(trim($data['plate_no'])) : null,
                    'status' => 'pending',
                    'is_active' => false,
                ]);
            }

            $user->roles()->syncWithoutDetaching([\App\Models\Role::firstOrCreate(['name' => 'courier'])->id]);

            return $user;
        });

        Cache::forget('otp_'.$email);

        return response()->json([
            'message' => 'Your rider application has been submitted and is pending approval.',
            'user' => self::userJson($user),
        ], 201);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => self::userJson($request->user())]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    public function googleLogin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'google_id' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:500'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $email = strtolower(trim($data['email']));
        $googleId = $data['google_id'] ?? null;
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $name = explode('@', $email)[0];
        }
        $avatar = $data['avatar'] ?? null;

        $user = null;
        if ($googleId) {
            $user = User::where('google_id', $googleId)->first();
        }
        if (!$user) {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        }

        if ($user) {
            $updated = false;
            if ($googleId && empty($user->google_id)) {
                $user->google_id = $googleId;
                $updated = true;
            }
            if ($avatar && empty($user->avatar)) {
                $user->avatar = $avatar;
                $updated = true;
            }
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $updated = true;
            }
            if ($updated) {
                $user->save();
            }
        } else {
            $user = DB::transaction(function () use ($email, $name, $googleId, $avatar) {
                $created = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $avatar,
                    'role' => User::ROLE_BUYER,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'password' => Hash::make(\Illuminate\Support\Str::random(32)),
                ]);

                \App\Models\Buyer::firstOrCreate(['user_id' => $created->id], [
                    'name' => $created->name,
                    'id_status' => 'unverified',
                    'status' => 'active',
                ]);

                $role = \App\Models\Role::firstOrCreate(['name' => 'buyer']);
                $created->roles()->syncWithoutDetaching([$role->id]);

                return $created;
            });
        }

        if (in_array($user->status, ['suspended', 'deactivated'], true) || $user->is_suspended) {
            return response()->json(['message' => 'Your account is suspended. Please contact platform support.'], 403);
        }
        if ($user->status === 'rejected') {
            return response()->json(['message' => 'Your account application was disapproved by the administrator.'], 403);
        }
        if ($user->status === 'pending' && !$user->isSeller()) {
            return response()->json(['message' => 'Your account registration is still pending admin verification.'], 403);
        }

        $token = $user->createToken($data['device_name'] ?? 'cartzy-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => self::userJson($user),
        ]);
    }
}
