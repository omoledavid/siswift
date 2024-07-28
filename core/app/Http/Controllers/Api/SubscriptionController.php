<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rinvex\Subscriptions\Models\PlanSubscription;

class SubscriptionController extends Controller
{
    public function index(){
        $user = auth()->user();
        $subscription = PlanSubscription::where('subscriber_id', $user->id)->first();
        if($subscription){
            return response()->json([
                'message' => 'You\'re subscribed to the '.$subscription->name.' plan.',
                'subscription' => $subscription,

            ]);
        }else{
            return response()->json([
                'You\'re not subscribed to any plans.',
            ]);
        }
    }
}
