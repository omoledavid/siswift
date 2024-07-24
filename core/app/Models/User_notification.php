<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function send(User $user, string $content): Model|Builder
    {
        return static::query()->create([
           'user_id' => $user->id,
           'title' => $content,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function seller()
    {
        return $this->belongsTo(User::class);
    }
}
