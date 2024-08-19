<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MannikJ\Laravel\Wallet\Models\Transaction as ModelsTransaction;
use MannikJ\Laravel\Wallet\Traits\HasWallet;
use Rinvex\Subscriptions\Models\PlanSubscription;
use Rinvex\Subscriptions\Traits\HasPlanSubscriptions;
use Stripe\Review;

/**
 * @property Wallet $wallet
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasPlanSubscriptions;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $guarded = ['id'];
    protected $with = ['review', 'subscription'];
    protected $appends = ['wallet', 'escrow_wallet'];
//    protected $appends = ['wallet', 'escrow_wallet'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $fillable = ['firstname', 'lastname'];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'object',
        'ver_code_send_at' => 'datetime'
    ];

    protected $data = [
        'data'=>1
    ];

    public function wallet(): Attribute
    {
        return new Attribute(
            function () {
                $account = $this->accounts->where('name', 'main')->first();

                if (!$account) {
                    $account = $this->accounts()->create([
                        'user_id' => $this->getKey(),
                        'name' => 'main',
                    ]);
                }

                return $account->wallet;
            }
        );
    }

    public function escrowWallet(): Attribute
    {
        return new Attribute(
            function () {
                $account = $this->accounts->where('name', 'escrow')->first();

                if (!$account) {
                    $account = $this->accounts()->create([
                        'user_id' => $this->getKey(),
                        'name' => 'escrow',
                    ]);
                }

                return $account->wallet;
            }
        );
    }

    public function accounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Account::class);
    }


    public function setFullnameAttribute($value)
    {
        $names = explode(' ', $value);

        // Ensure $names is an array
        if(is_array($names) && count($names) > 0) {
            $this->firstname = $names[0];
            if (count($names) > 1) {
                $this->lastname = implode(' ', array_slice($names, 1));
            }
        }
    }
    public function getuserFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }





    public function loginLogs()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function transactions()
    {
        return $this->hasMany(ModelsTransaction::class)->orderBy('id','desc');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status','!=',0);
    }

    public function appliedCoupons()
    {
        return $this->hasMany(AppliedCoupon::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    // SCOPES



    public function scopeActive()
    {
        return $this->where('status', 1);
    }

    public function scopeBanned()
    {
        return $this->where('status', 0);
    }

    public function scopeEmailUnverified()
    {
        return $this->where('ev', 0);
    }

    public function scopeSmsUnverified()
    {
        return $this->where('sv', 0);
    }
    public function scopeEmailVerified()
    {
        return $this->where('ev', 1);
    }

    public function scopeSmsVerified()
    {
        return $this->where('sv', 1);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
    public function products()
    {
        return $this->hasMany(Product::class,'seller_id');
    }
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class,'seller_id')->where('status','!=',0);
    }
    public function review(){
        return $this->hasMany(UserReview::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }
    public function shop(){
        return $this->belongsTo(Shop::class, 'seller_id', 'seller_id');
    }

    public function subscription(){
        return $this->belongsTo(PlanSubscription::class, 'id','subscriber_id');
    }

}
