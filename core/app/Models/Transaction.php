<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends \MannikJ\Laravel\Wallet\Models\Transaction
{


    protected  $guarded = ['id'];

    public function seller()
    {
        return $this->belongsTo(Seller::class,'seller_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Custom method to get user by wallet
    public function getUserByWallet()
    {
        // Check if wallet relationship is loaded, otherwise load it
        if (!$this->relationLoaded('wallet')) {
            $this->load('wallet');
        }

        // Ensure wallet exists and is loaded
        if ($this->wallet) {
            return $this->user()->where('id', $this->wallet->id)->first(); // Assuming 'user_id' is the foreign key in Wallet model
        }

        return null; // Return null if no wallet is associated
    }

}
