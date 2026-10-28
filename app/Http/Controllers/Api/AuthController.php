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
