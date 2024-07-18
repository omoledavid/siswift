<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $with = ['user'];

    protected $casts = [
        'meta_keywords' => 'array',
        'social_links' => 'array'
    ];
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
