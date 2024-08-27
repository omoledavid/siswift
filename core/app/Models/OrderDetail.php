<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $with = ['product'];
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id');
    }


    public function scopeOrders()
    {
        return $this->whereHas('order',function($q){
            $q->where('payment_status',  '!=' ,0)->when(request()->search,function($order){
                return $order->where('order_number',request()->search);
            });
         });

    }

    public function scopePendingOrder()
    {
        return $this->whereHas('order',function($q){
            $q->where('payment_status' , 0)->where('status', 0)->when(request()->search,function($order){
                return $order->where('order_number',request()->search);
            });
         });

    }

    public function scopeProcessingOrder()
    {
        return $this->whereHas('order',function($q){
            $q->where('payment_status', '!=' , 0)->where('status', 1)->when(request()->search,function($order){
                return $order->where('order_number',request()->search);
            });
        });

    }

    public function scopeDispatchedOrder()
    {
        return $this->whereHas('order',function($q){
            $q->where('payment_status', '!=' , 0)->where('status', 2)->when(request()->search,function($order){
                return $order->where('order_number',request()->search);
            });
         });

    }
    public function scopeCompletedOrder()
    {
        return $this->whereHas('order',function($q){
            $q->where('payment_status', '!=' , 0)->where('status', 1)->when(request()->search,function($order){
                return $order->where('order_number',request()->search);
            });
         });

    }
    public function scopeCancelledOrder()
    {
        return $this->whereHas('order',function($q){
            $q->where('payment_status', '!=' , 0)->where('status', 3)->when(request()->search,function($order){
                return $order->where('order_number',request()->search);
            });
         });

    }

    public function scopeCod()
    {
        return $this->whereHas('order',function($q){
            $q->where('payment_status',2);
        });

    }



}
