<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'refund_id', // Include the refund_id in fillable properties
        'reason',
        'status',
        'image',
        'video',
    ];
    protected $casts = ['refund_id' => 'integer'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buyer()
    {
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'buyer_id');
    }

    public function seller()
    {
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'seller_id');
    }

    public function replies()
    {
        return $this->hasMany(DisputeReply::class);
    }
}
