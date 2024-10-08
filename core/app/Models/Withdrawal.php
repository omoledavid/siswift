<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'withdraw_information' => 'object'
    ];
    protected $fillable = ['user_id', 'amount', 'account_number', 'bank_code', 'status'];

    public function seller()
    {
        return $this->belongsTo(User::class,'seller_id')->withDefault();
    }

    public function method()
    {
        return $this->belongsTo(WithdrawMethod::class, 'method_id');
    }

    public function scopePending()
    {
        return $this->where('status', 2);
    }

    public function scopeApproved()
    {
        return $this->where('status', 1);
    }

    public function scopeRejected()
    {
        return $this->where('status', 3);
    }
}
