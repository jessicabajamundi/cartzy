<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDispute extends Model
{
    /** Problem types a buyer can report, and whether photo proof is required. */
    public const CATEGORIES = [
        'not_received' => ['I haven\'t received my order', false],
        'damaged' => ['Item arrived damaged', true],
        'wrong_item' => ['Wrong item, size or variation', true],
        'missing' => ['Missing items or parts', true],
        'not_as_described' => ['Item is different from the listing or looks fake', true],
        'defective' => ['Item doesn\'t work properly', true],
        'delivery' => ['Problem with the rider or delivery', false],
        'seller' => ['Problem with the seller', false],
        'payment' => ['Charged the wrong amount or payment problem', false],
        'other' => ['Something else', false],
    ];

    public const RESOLUTIONS = [
        'refund' => 'Refund',
        'return_refund' => 'Return the item and get a refund',
        'replacement' => 'Replacement',
        'report_only' => 'Just let Cartzy know',
    ];

    /** Days after delivery a buyer can still report a problem. */
    public const REPORT_WINDOW_DAYS = 15;

    protected $fillable = [
        'order_id', 'seller_order_id', 'reporter_id', 'category', 'requested_resolution', 'subject',
        'description', 'items', 'evidence', 'contact_phone', 'status', 'admin_notes', 'resolved_at',
    ];

    protected $casts = [
        'items' => 'array',
        'evidence' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function categoryLabel(): ?string
    {
        return self::CATEGORIES[$this->category][0] ?? null;
    }
}
