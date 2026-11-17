<?php

namespace App\Services;

use App\Models\{Buyer, Role, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleAccounts
{
    public function resolve(string $email, string $googleId, string $name, ?string $avatar, string $intent, string $portal = 'buyer'): User
    {
        if (!in_array($intent, ['login', 'register'], true) || $googleId === '') {
            throw ValidationException::withMessages(['email' => 'Please start Google sign-in again from Login or Register.']);
        }
        try {
            return DB::transaction(function () use ($email, $googleId, $name, $avatar, $intent, $portal) {
                $email = strtolower(trim($email));
                $byId = User::where('google_id', $googleId)->lockForUpdate()->first();
                $byEmail = User::whereRaw('LOWER(email) = ?', [$email])->lockForUpdate()->first();
                $user = $byId ?? $byEmail;
                if ($intent === 'register' && $user) {
                    throw ValidationException::withMessages(['email' => 'This Google email already has a Cartzy account. Please log in.']);
                }
                if ($intent === 'login' && !$user) {
                    throw ValidationException::withMessages(['email' => 'No Cartzy account found for this Google email. Please register first.']);
                }
                if ($user) {
                    if (($byId && $byEmail && !$byId->is($byEmail)) || ($user->google_id && $user->google_id !== $googleId)) {
                        throw ValidationException::withMessages(['email' => 'This email is linked to a different Google account. Use your original sign-in method.']);
                    }
                    if ($user->role !== $portal) {
                        throw ValidationException::withMessages(['email' => 'This Google account belongs to a different Cartzy account type. Please use the correct login page.']);
                    }
                    if (!($user->status === 'active' || ($portal === 'seller' && $user->status === 'pending')) || $user->is_suspended) {
                        throw ValidationException::withMessages(['email' => 'This Cartzy account is not active. Please contact support.']);
                    }
                    $user->google_id = $googleId;
                    $user->email_verified_at ??= now();
                    $user->save();
                    return $user;
                }
                if ($portal !== 'buyer') {
                    throw ValidationException::withMessages(['email' => 'Google registration is available for buyer accounts only.']);
                }
                $created = User::create(['name' => $name ?: 'Google User', 'email' => $email, 'google_id' => $googleId,
                    'avatar' => $avatar, 'role' => 'buyer', 'status' => 'active', 'email_verified_at' => now(),
                    'password' => Str::random(40), 'has_custom_password' => false]);
                Buyer::create(['user_id' => $created->id, 'name' => $created->name, 'id_status' => 'unverified', 'status' => 'active']);
                $role = Role::firstOrCreate(['name' => 'buyer']);
                $created->roles()->syncWithoutDetaching([$role->id]);
                return $created;
            }, 3);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $error) {
            throw ValidationException::withMessages(['email' => 'This Google email already has a Cartzy account. Please log in.']);
        }
    }
}
