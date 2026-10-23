<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLog extends Model
{
    public const LOGIN = 'login';
    public const LOGOUT = 'logout';
    public const FAILED_LOGIN = 'failed_login';

    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'email', 'role', 'event', 'method', 'description', 'ip_address', 'user_agent'];

    protected $casts = ['created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Record an auth event; never lets a logging failure break sign-in or sign-out. */
    public static function record(string $event, Request $request, ?User $user = null, array $extra = []): void
    {
        try {
            static::create([
                'user_id' => $user?->id,
                'email' => $user?->email ?? $extra['email'] ?? null,
                'role' => $user?->role,
                'event' => $event,
                'method' => $extra['method'] ?? null,
                'description' => $extra['description'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity log write failed: '.$e->getMessage());
        }
    }
}
