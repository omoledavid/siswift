<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'attributes' => 'array',
        'offer_price' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributes()
    {
        return $this->belongsTo(AssignProductAttribute::class, 'attributes');
    }

    public static function insertUserToCart($user_id, $session_id)
    {
        $cart = self::where('session_id', $session_id)->get();

        self::where('session_id', $session_id)->update(['user_id' => $user_id]);
    }
}
