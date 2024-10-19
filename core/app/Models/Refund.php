<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'amount','user_id', 'reason', 'status'];

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer()
    {
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'buyer_id');
    }

    public function seller()
    {
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'seller_id');
    }
}
