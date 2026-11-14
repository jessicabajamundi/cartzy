<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnCase extends Model
{
    protected $guarded = ['id'];
    protected $attributes = ['status' => 'requested', 'amount_minor' => 0];
    protected $casts = ['amount_minor' => 'integer', 'scheduled_date' => 'date', 'received_at' => 'datetime'];

    public function dispute() { return $this->belongsTo(AdminDispute::class, 'admin_dispute_id'); }
    public function logisticsProvider() { return $this->belongsTo(LogisticsProvider::class); }
    public function rider() { return $this->belongsTo(Rider::class); }
    public function refund() { return $this->hasOne(RefundTransaction::class); }
    public function events() { return $this->hasMany(ReturnEvent::class)->orderBy('id'); }

    public function scopeVisibleTo($query, User $user)
    {
        return match ($user->role) {
            'admin' => $query,
            'buyer' => $query->whereHas('dispute', fn ($q) => $q->where('reporter_id', $user->id)),
            'seller' => $query->whereHas('dispute.sellerOrder.seller', fn ($q) => $q->where('user_id', $user->id)),
            'logistics' => $query->where(function ($q) use ($user) {
                $pid = $user->logisticsProvider?->id;
                $q->where('logistics_provider_id', $pid)
                  ->orWhereHas('dispute.sellerOrder.shipment', fn ($sq) => $sq->where('logistics_provider_id', $pid));
            }),
            default => $query->whereRaw('1 = 0'),
        };
    }
}
