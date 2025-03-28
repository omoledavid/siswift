<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Subscriptions\Models;

class Plan extends Models\Plan
{
    use HasFactory;
    protected $table = 'plans';
    protected $with = ['features'];
    protected $fillable = ['type'];

//    public function features(){
//        return $this->hasMany(PlanFeature::class);
//    }
}
