<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $with = ['seller', 'products'];


    public function appliedCoupon()
    {
        return $this->hasOne(AppliedCoupon::class,'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function deposit()
    {
        return $this->hasOne(Deposit::class, 'order_id', 'id')->latest()->withDefault();
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, OrderDetail::class, 'order_id', 'id');
    }

    public function product(): Attribute
    {
        return new Attribute(
            fn () => $this->products()->first(),
        );
    }
    public function seller(){
        return $this->belongsTo(Seller::class,'seller_id');
    }


    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getSellerIdAttribute()
    {
        // Get the first order detail
        $orderDetail = $this->orderDetail()->first();
        // Return the seller_id from the first order detail, or null if no order detail exists
        return $orderDetail ? $orderDetail->seller_id : null;
    }

    // Add the custom attribute to the $appends array
    protected $appends = ['seller_id'];


    public function getAmountAttribute()
    {
        return $this->total_amount - $this->shipping_charge;
    }

    public function scopePending()
    {
        return $this->where('status', 0)->where('payment_status',1);
    }

    public function statusBadge()
    {
        if($this->status == 0){
            return makeHtmlElement('span', 'warning', 'Pending');
        }elseif($this->status == 1){
            return makeHtmlElement('span', 'primary', 'Processing');
        }elseif($this->status == 2){
            return makeHtmlElement('span', 'dark', 'Dispatched');
        }elseif($this->status == 3){
            return makeHtmlElement('span', 'success', 'Delivered');
        }else{
            return makeHtmlElement('span', 'danger', 'Cancelled');
        }
    }
    public function escrow(){
        return $this->hasOne(Escrow::class);
    }

}
