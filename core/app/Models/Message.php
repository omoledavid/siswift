<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'message'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function files()
    {
        return $this->hasMany(MessageFile::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
