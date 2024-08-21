<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
