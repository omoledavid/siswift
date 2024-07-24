<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Subscriptions\Models\PlanFeature;

class Plan extends Model
{
    use HasFactory;
    protected $with = ['features'];

    public function timeSetting(){
        return null;
    }

    public function features(){
        return $this->hasMany(PlanFeature::class);
    }
}
