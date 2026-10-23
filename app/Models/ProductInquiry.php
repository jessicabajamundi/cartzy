<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A buyer's pre-purchase conversation with a seller about one product.
 */
class ProductInquiry extends Model
{
    protected $fillable = ['buyer_id', 'seller_id', 'product_id'];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function messages()
    {
        return $this->hasMany(ProductInquiryMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ProductInquiryMessage::class)->latestOfMany();
    }

    /** Inquiries visible to the given user (as the buyer or as the seller's owner). */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isSeller()
            ? $query->where('seller_id', $user->seller?->id ?? 0)
            : $query->where('buyer_id', $user->id);
    }
}
