<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * User Roles Constants
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SELLER = 'seller';
    public const ROLE_COURIER = 'courier';
    public const ROLE_BUYER = 'buyer';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'middle_initial',
        'email',
        'google_id',
        'sex',
        'birthday',
        'age',
        'phone',
        'address',
        'street_address',
        'region',
        'province',
        'city',
        'barangay',
        'postal_code',
        'id_photo',
        'id_type',
        'id_number',
        'id_status',
        'id_rejection_reason',
        'id_verified_at',
        'password',
        'role',
        'avatar',
        'status',
        'is_suspended',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_suspended' => 'boolean',
        ];
    }

    /**
     * Role Check Helpers
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSeller(): bool
    {
        return $this->role === self::ROLE_SELLER;
    }

    public function isCourier(): bool
    {
        return $this->role === self::ROLE_COURIER;
    }

    public function isBuyer(): bool
    {
        return $this->role === self::ROLE_BUYER;
    }

    /**
     * Get Redirect Dashboard Route based on Role
     */
    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_SELLER => 'seller.dashboard',
            self::ROLE_COURIER => 'courier.dashboard',
            default => 'home',
        };
    }

    /**
     * Check if user is ID-verified for checkout & transactions
     */
    public function isIdVerified(): bool
    {
        return $this->id_status === 'verified';
    }

    /**
     * Get ID Verification Status Display Metadata
     */
    public function getIdStatusBadge(): array
    {
        $status = $this->id_status ?? 'unverified';

        return match ($status) {
            'verified' => [
                'status'     => 'verified',
                'label'      => 'Verified ID',
                'bg_class'   => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'icon'       => '✓',
                'can_shop'   => true,
                'desc'       => 'Your identity has been verified. You have full checkout privileges.',
            ],
            'pending' => [
                'status'     => 'pending',
                'label'      => 'Under Review',
                'bg_class'   => 'bg-amber-50 text-amber-800 border-amber-200',
                'icon'       => '⏳',
                'can_shop'   => false,
                'desc'       => 'Your ID document has been submitted and is currently being reviewed by our compliance team.',
            ],
            'rejected' => [
                'status'     => 'rejected',
                'label'      => 'Disapproved',
                'bg_class'   => 'bg-rose-50 text-rose-800 border-rose-200',
                'icon'       => '✕',
                'can_shop'   => false,
                'desc'       => 'Your ID verification was disapproved: ' . ($this->id_rejection_reason ?: 'Document is unreadable or expired.') . ' Please upload a valid ID.',
            ],
            default => [
                'status'     => 'unverified',
                'label'      => 'Not Verified',
                'bg_class'   => 'bg-gray-100 text-gray-700 border-gray-200',
                'icon'       => '⚠️',
                'can_shop'   => false,
                'desc'       => 'Please verify your government ID before placing orders or proceeding to checkout.',
            ],
        };
    }

    /**
     * Eloquent Relationships
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists() || $this->role === $roleName;
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function logisticsProvider()
    {
        return $this->hasOne(LogisticsProvider::class);
    }

    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function deliveryEvents()
    {
        return $this->hasMany(DeliveryEvent::class);
    }
}

