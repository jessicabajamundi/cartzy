<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'amount_minor',
        'status',
        'provider_ref',
        'receipt_path',
        'verified_at',
        'verified_by',
        'verification_notes',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'verified_at'  => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (empty($this->receipt_path)) {
            return null;
        }
        if (str_starts_with($this->receipt_path, 'http://') || str_starts_with($this->receipt_path, 'https://')) {
            return $this->receipt_path;
        }
        return asset('storage/' . ltrim($this->receipt_path, '/'));
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_minor / 100;
    }
}
