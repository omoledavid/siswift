<?php

namespace App\Models;

use App\Models\Contracts\Customer;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property User $payable
 */
class Payment extends Model
{
    use HasFactory;

    const StatusPending = 'pending';

    const StatusPaid = 'paid';

    const StatusFailed = 'failed';

    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];

    public static function make(User $user, float $money, string $gateway, string $callback_url, string $description)
    {
        return $user->payments()->create([
            'reference' => Str::uuid(),
            'amount' => $money,
            'status' => self::StatusPending,
            'gateway' => $gateway,
            'data' => [
                'callback_url' => $callback_url,
                'description' => $description,
            ],
        ]);
    }

    public function scopeOnlyPending(Builder $query)
    {
        return $query->where('status', self::StatusPending);
    }

    public function isPaid(): bool
    {
        return $this->status === self::StatusPaid;
    }

    public function verify(): bool
    {
        return $this->update(['status' => self::StatusPaid]);
    }

    public function payable()
    {
        return $this->morphTo();
    }
}
