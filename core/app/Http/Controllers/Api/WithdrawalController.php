<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\WithdrawDetail;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class WithdrawalController extends Controller
{

    public function store(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method_id' => 'required|string',
        ]);
        $user = auth()->user();

        // Check user balance, account verification, etc.
        $account_details = WithdrawDetail::where('id', $validatedData['method_id'])->first();
        if(!$account_details){
            return response()->json([
                'message' => 'Kindly add withdrawal method first',
            ]);
        }

        $withdrawal = Withdrawal::create([
            'seller_id' => $user->id,
            'amount' => $validatedData['amount'],
            'account_number' => $account_details->account_number,
            'bank_code' => $account_details->bank_code,
            'status' => 2, //2 is for pending
        ]);

        try {
            $user->wallet->withdraw($validatedData['amount'], [
                'description' => 'Withdraw Money to '.$account_details->account_number.' '.$account_details->bank_name,
            ]);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '. env('PAYSTACK_SECRET_KEY'),
                'Cache-Control' => 'no-cache',
            ])->post('https://api.paystack.co/transferrecipient', [
                'type' => 'nuban',
                'name' => $account_details->account_name,
                'account_number' => $account_details->account_number,
                'bank_code' => $account_details->bank_code,
                'currency' => 'NGN',
            ]);

            $result = $response->body();
            $json_data = json_decode($result, true);
            $recipientCode = $json_data['data']['recipient_code'];

            $withdrawal->update(['status' => 3]); //3 is for processing

            // Handle successful transfer
            return response()->json([
                'message' => 'Withdrawal initiated successfully',
                'result' => json_decode($result, true),
                'code' => $recipientCode,
            ], 200);
        } catch (\Exception $e) {
            // Handle error
            $withdrawal->update(['status' => 'failed']);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
