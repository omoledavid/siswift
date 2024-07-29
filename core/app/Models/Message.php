<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Textmagic\Services\Models\Messages;

class Message extends Model
{
    use HasFactory;
    protected $guarded = [];
//    protected $table = 'conversations';

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function messages(){
        return $this->hasMany(Conversation::class, 'message_id');
    }
}
