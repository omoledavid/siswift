<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reply extends Model
{
    protected $fillable = ['review_id', 'user_id', 'content'];

    public function review() : BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
