<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCommentReaction extends Model
{
    protected $fillable = ['product_comment_id', 'user_id', 'type'];

    public function comment()
    {
        return $this->belongsTo(ProductComment::class, 'product_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
