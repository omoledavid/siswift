<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisputeReply extends Model
{
    use HasFactory;
    protected $fillable = ['dispute_id', 'user_id', 'message', 'image', 'video'];

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
