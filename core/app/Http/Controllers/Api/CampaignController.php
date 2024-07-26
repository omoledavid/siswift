<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Plan;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
           'product_id' => ['required', 'exists:products,id'],
        ]);

        $validatedData['clicks'] = [];
        $validatedData['message'] = [];
        $validatedData['user_id'] = $request->user()->id;
        $campaignExist = Campaign::where('product_id', $validatedData['product_id'])->where('user_id', $validatedData['user_id'])->first();
        if ($campaignExist) {
            return response()->json(['You already boosted this product'], 400);
        }
        $campaign = Campaign::query()->create($validatedData);


        return response()->json([
            'message' => 'success',
            'data' => $campaign
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::query()->find($request->get('plan_id'));

        $campaign->update([
            'plan_id' => $request->get('plan_id'),
            'start_date' => now(),
            'end_date' => now()->add($plan->invoice_period, $plan->invoice_interval)
        ]);

        return response()->json([
            'message' => 'success',
            'data' => $campaign
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function campaignData(){
        $user = auth()->user();
        $campaigns = Campaign::where('user_id', $user->id)->get();
        return response()->json([
            'campaigns' => $campaigns,

        ]);
    }
}
