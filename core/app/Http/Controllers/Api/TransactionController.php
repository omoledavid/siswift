<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use MannikJ\Laravel\Wallet\Models\Transaction;

class TransactionController extends Controller
{
    public function transactions(){
        $user = auth()->user();
    $transaction = Transaction::where('wallet_id', $user->wallet->id)->paginate(10);
    return response()->json($transaction);
    }
    public function withdraw(Request $request){
        $user = auth()->user();
        $request->validate([
            'account_name'                  => 'required|string|max:40',
            'account_number'                 => 'required|string|max:40',
            'bank_name'               => 'required|string|max:600',
            'currency'               => 'required|string|max:600',
            'method_code'               => 'required|string|max:600',
            'amount'               => 'required|string|max:600',
        ]);

        $method = WithdrawMethod::where('id', $request->method_code)->where('status', 1)->firstOrFail();
        $seller = $user;

        if ($request->amount < $method->min_limit) {
            $notify[] = ['error', 'Your requested amount is smaller than minimum amount.'];
            return response()->json($notify);
        }

        if ($request->amount > $method->max_limit) {
            $notify[] = ['error', 'Your requested amount is larger than maximum amount.'];
            return response()->json($notify);
        }

        if ($request->amount > $seller->wallet->balance) {
            $notify[] = ['error', 'You do not have sufficient balance for withdraw.'];
            return response()->json($notify);
        }


        $charge         = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
        $afterCharge    = $request->amount - $charge;
        $finalAmount    = $afterCharge * $method->rate;

        $withdraw = new Withdrawal();
        $withdraw->method_id    = $method->id; // wallet method ID
        $withdraw->seller_id    = $seller->id;
        $withdraw->amount       = $request->amount;
        $withdraw->currency     = $method->currency;
        $withdraw->rate         = $method->rate;
        $withdraw->charge       = $charge;
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $afterCharge;
        $withdraw->trx          = getTrx();
        $withdraw->save();
        session()->put('wtrx', $withdraw->trx);
        return response()->json(['status' => 'success', 'message' => 'Withdraw Successful.']);


    }
    public function withdrawalMethod(Request $request){
        $user = auth()->user();
        $methods = WithdrawMethod::all();
        return response()->json(
            [
                'status' => 'success',
                'data' => $methods
            ]
        );
    }
}
