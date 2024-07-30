<?php

namespace App\Models;

use App\Enums\EscrowStatus;
use App\Exceptions\EscrowException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Escrow extends Model
{
    use HasFactory;

protected $guarded = [];

    protected $casts = [
        'status' => EscrowStatus::class,
    ];

    /**
     * @throws \Exception
     */
    public static function start(User $buyer, Order $order)
    {

        try {
            DB::beginTransaction();
            $escrow = static::query()->create([
                'buyer_id' => $buyer->id,
                'seller_id' => $order->seller_id,
                'order_id' => $order->id,
                'status' => EscrowStatus::Initiated
            ]);

            $escrow->buyer->wallet->withdraw($order->amount);
            $escrow->seller->escrow_wallet->deposit($order->amount);

            DB::commit();
            return $escrow;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }


    public function reject(): static
    {
        return DB::transaction(function(){
            if($this->status !== EscrowStatus::Initiated){
                throw new EscrowException('This escrow cannot be rejected');
            }

            $this->update([
                'status' => EscrowStatus::Rejected
            ]);

            $this->seller->escrow_wallet->withdraw($this->order->amount);
            $this->buyer->wallet->deposit($this->order->amount);

            return $this;
        });
    }

    public function confirm()
    {
        return DB::transaction(function(){
            if($this->status !== EscrowStatus::Initiated){
                throw new EscrowException('This escrow cannot be confirmed');
            }

            $this->update([
                'status' => EscrowStatus::Confirmed
            ]);

            return $this;
        });
    }

    public function complete()
    {
        return DB::transaction(function(){
            if($this->status !== EscrowStatus::Confirmed){
                throw new EscrowException('This escrow is not yet confirmed by the seller');
            }

            $this->update([
                'status' => EscrowStatus::Delivered
            ]);

            $this->order->payment_status = 1;
            $this->order->status = 1;
            $this->order->save();

            $this->seller->escrow_wallet->withdraw($this->order->amount);
            $this->seller->wallet->deposit($this->order->amount);

            return $this;
        });
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'seller_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
