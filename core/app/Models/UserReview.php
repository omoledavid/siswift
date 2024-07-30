<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReview extends Model
{
    use HasFactory;

    protected $with = ['seller'];

    public function seller(){
        return $this->belongsTo(User::class, 'seller_id', 'seller_id');
    }
}
