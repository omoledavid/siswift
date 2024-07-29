<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Rinvex\Subscriptions\Models\PlanFeature;

class PlanController extends Controller
{
    public function index()
    {
        $pageTitle = "Plans";
        $emptyMessage = 'no plan';
        $plans = Plan::orderBy('id', 'desc')->paginate(10);
        return view('admin.plan.index', compact('pageTitle', 'plans', 'emptyMessage'));
    }

    public function create()
    {
        $pageTitle = "Create Plan";
        return view('admin.plan.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required'
        ]);
        $plan = app('rinvex.subscriptions.plan')->create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'signup_fee' => 0,
            'invoice_period' => $request->get('duration'),
            'invoice_interval' => $request->get('interval'),
            'trial_period' => $request->get('trial_duration'),
            'trial_interval' => $request->get('trial_interval'),
            'sort_order' => $request->get('order'),
            'currency' => 'NGN',
            'type' => $request->type
        ]);
        $plan->features()->saveMany([
            new PlanFeature(['name' => 'photo_upload', 'value' => $request->photo_upload, 'sort_order' => 1]),
            new PlanFeature(['name' => 'visibility', 'value' => $request->visibility, 'sort_order' => 2]),
            new PlanFeature(['name' => 'analytics', 'value' => $request->analytics, 'sort_order' => 3]),
            new PlanFeature(['name' => 'promote_listing', 'value' => $request->promote_listing, 'sort_order' => 5]),
            new PlanFeature(['name' => 'highlights', 'value' => $request->highlights, 'sort_order' => 6]),
            new PlanFeature(['name' => 'ad_free', 'value' => $request->ad_free, 'sort_order' => 7]),
            new PlanFeature(['name' => 'support', 'value' => $request->support, 'sort_order' => 8]),
            new PlanFeature(['name' => 'whatsapp', 'value' => $request->whatsapp, 'sort_order' => 9]),
            new PlanFeature(['name' => 'extra_no', 'value' => $request->extra_no, 'sort_order' => 10]),
            new PlanFeature(['name' => 'promotion', 'value' => $request->promotion, 'sort_order' => 11]),
            new PlanFeature(['name' => 'social', 'value' => $request->social, 'sort_order' => 12]),
            new PlanFeature(['name' => 'manager', 'value' => $request->manager, 'sort_order' => 13]),
        ]);
        $notify[] = ['success', 'Plan created successfully!'];
        return redirect()->route('admin.plan.index')->withNotify($notify);
    }


    public function update($id)
    {
        $pageTitle = "Create Plan";
        $plan = app('rinvex.subscriptions.plan')->find($id);

        // Transform the features collection into an associative array
        $features = $plan->features->pluck('value', 'name')->toArray();

        return view('admin.plan.edit', compact('pageTitle', 'plan', 'features'));
    }

    public function updatePlan(Request $request)
    {
        $plan = app('rinvex.subscriptions.plan')->find($request->plan_id);
        $plan->update([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'signup_fee' => 0,
            'invoice_period' => $request->get('duration'),
            'invoice_interval' => $request->get('interval'),
            'trial_period' => $request->get('trial_duration'),
            'trial_interval' => $request->get('trial_interval'),
            'sort_order' => $request->get('order'),
            'currency' => 'NGN',
            'type' => $request->type
        ]);
        $notify[] = ['success', 'Plan detail has been updated'];
        return redirect()->route('admin.plan.index')->withNotify($notify);
    }


}
