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

    /** What each event means to the account owner: [title, group]. Groups drive the filters and icons. */
    public const EVENTS = [
        'login' => ['Signed in', 'security'],
        'logout' => ['Signed out', 'security'],
        'failed_login' => ['Failed sign-in attempt', 'security'],
        'password_reset' => ['Password reset', 'security'],
        'password_changed' => ['Password changed', 'security'],
        'devices_signed_out' => ['Signed out other devices', 'security'],
        'profile_updated' => ['Personal details updated', 'account'],
        'avatar_updated' => ['Profile photo changed', 'account'],
        'avatar_removed' => ['Profile photo removed', 'account'],
        'id_submitted' => ['ID submitted for verification', 'account'],
        'address_added' => ['Delivery address added', 'account'],
        'address_updated' => ['Delivery address updated', 'account'],
        'address_deleted' => ['Delivery address deleted', 'account'],
        'address_default' => ['Default address changed', 'account'],
        'order_placed' => ['Order placed', 'orders'],
        'order_paid' => ['Order paid', 'orders'],
        'order_cancelled' => ['Order cancellation requested', 'orders'],
        'problem_reported' => ['Problem reported on an order', 'orders'],
    ];

    protected $fillable = ['user_id', 'email', 'role', 'event', 'method', 'channel', 'description', 'ip_address', 'user_agent'];

    protected $casts = ['created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Record an event; never lets a logging failure break the action being logged. */
    public static function record(string $event, Request $request, ?User $user = null, array $extra = []): void
    {
        try {
            static::create([
                'user_id' => $user?->id,
                'email' => $user?->email ?? $extra['email'] ?? null,
                'role' => $user?->role,
                'event' => $event,
                'method' => $extra['method'] ?? null,
                'channel' => $request->is('api/*') ? 'mobile' : 'web',
                'description' => $extra['description'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity log write failed: '.$e->getMessage());
        }
    }

    /** Record an account action for the signed-in user from anywhere (controllers or services). */
    public static function forUser(User|int|null $user, string $event, ?string $description = null): void
    {
        $user = is_int($user) ? User::find($user) : $user;
        if ($user) static::record($event, request(), $user, ['description' => $description]);
    }

    /** Groups a filter can choose; anything else means "all". */
    public const GROUPS = ['security', 'account', 'orders'];

    public const PER_PAGE = 10;

    /** Limit to one group's events (security, account or orders). */
    public function scopeInGroup($query, ?string $group)
    {
        if (!in_array($group, self::GROUPS, true)) return $query;

        return $query->whereIn('event', array_keys(array_filter(self::EVENTS, fn ($e) => $e[1] === $group)));
    }

    public function title(): string
    {
        return self::EVENTS[$this->event][0] ?? ucfirst(str_replace('_', ' ', $this->event));
    }

    public function group(): string
    {
        return self::EVENTS[$this->event][1] ?? 'account';
    }

    /** A short, human device label such as "Chrome on Windows" or "Cartzy app on Android". */
    public function device(): string
    {
        $agent = (string) $this->user_agent;
        $os = match (true) {
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') || str_contains($agent, 'iOS') => 'iOS',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Mac OS') || str_contains($agent, 'Macintosh') => 'Mac',
            str_contains($agent, 'Linux') => 'Linux',
            default => null,
        };
        if ($this->channel === 'mobile' || str_starts_with($agent, 'Dart/')) {
            return 'Cartzy app'.($os ? ' on '.$os : '');
        }
        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'OPR/') => 'Opera',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Browser',
        };

        return $browser.($os ? ' on '.$os : '');
    }

    /** The shape both the account page and the mobile API use. */
    public function toFeedArray(): array
    {
        $method = match ($this->method) {
            'google' => 'with Google',
            'password' => 'with password',
            'registration' => 'after creating the account',
            default => null,
        };

        return [
            'id' => $this->id,
            'event' => $this->event,
            'group' => $this->group(),
            'title' => $this->title(),
            'description' => $this->description ?: ($method ? ucfirst($method) : null),
            'channel' => $this->channel ?? 'web',
            'device' => $this->device(),
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
