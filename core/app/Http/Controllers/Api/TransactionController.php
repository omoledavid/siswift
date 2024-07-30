<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\Withdrawal;
use App\Models\WithdrawDetail;
use App\Models\WithdrawMethod;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use MannikJ\Laravel\Wallet\Models\Transaction;
use App\Services\PaystackService;

class TransactionController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }
    public function transactions()
    {
        $user = auth()->user();
        $transaction = Transaction::where('wallet_id', $user->wallet->id)->orderBy('id', 'desc')->paginate(10);
        return response()->json($transaction);
    }
    public function withdrawDetails(Request $request){
        $user = auth()->user();
        $request->validate([
            'account_name' => 'required|string|max:40',
            'account_number' => 'required|max:40',
            'bank_name' => 'required|string|max:600',
            'bank_code' => 'required|string|max:200'
        ]);
        $accountNumber = $request->input('account_number');
        $bankCode = $request->input('bank_code');

        $result = $this->paystackService->validateBankAccount($accountNumber, $bankCode);

        if ($result['error']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        $detailsExist = WithdrawDetail::where('account_number', $request->account_number)->first();
        if($detailsExist){
            return response()->json([
                'message' => 'Account number already exist'
            ]);
        }
        $withDetails = new WithdrawDetail();
        $withDetails->user_id = $user->id;
        $withDetails->account_name = $request->input('account_name');
        $withDetails->account_number = $request->input('account_number');
        $withDetails->bank_name = $request->input('bank_name');
        $withDetails->bank_code = $request->input('bank_code');
        $withDetails->save();
        return response()->json([
            'data' => $withDetails,
            'response' => $result['data'],
        ]);

    }

    public function withdraw(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'account_name' => 'required|string|max:40',
            'account_number' => 'required|string|max:40',
            'bank_name' => 'required|string|max:600',
            'currency' => 'required|string|max:600',
            'method_code' => 'required|string|max:600',
            'amount' => 'required|string|max:600',
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


        $charge = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
        $afterCharge = $request->amount - $charge;
        $finalAmount = $afterCharge * $method->rate;

        $seller->wallet->withdraw($finalAmount);

        $withdraw = new Withdrawal();
        $withdraw->method_id = $method->id; // wallet method ID
        $withdraw->seller_id = $seller->id;
        $withdraw->amount = $request->amount;
        $withdraw->currency = $method->currency;
        $withdraw->rate = $method->rate;
        $withdraw->charge = $charge;
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $afterCharge;
        $withdraw->trx = getTrx();
        $withdraw->save();
        session()->put('wtrx', $withdraw->trx);
        return response()->json(['status' => 'success', 'message' => 'Withdraw Successful.']);


    }

    public function withdrawalMethod(Request $request)
    {
        $user = auth()->user();
        $methods = WithdrawDetail::where('user_id', $user->id)->get();
        return response()->json(
            [
                'status' => 'success',
                'data' => $methods
            ]
        );
    }

    public function escrowAccept(Escrow $escrow)
    {

        if ((int)$escrow->seller_id === (int)request()->user()->seller_id) {
            $escrow->confirm();
            return response()->json([
                'status' => 'success',
                'message' => 'Escrow Accepted',
                'data' => $escrow
            ]);
        }

        return response()->json(['status' => 'failed'], 400);
    }
    public function escrowReject(Escrow $escrow)
    {
        if ((int)$escrow->seller_id === (int)request()->user()->seller_id) {
            $escrow->reject();
            return response()->json([
                'status' => 'success',
                'message' => 'Escrow Rejected',
                'data' => $escrow
            ]);
        }

        return response()->json(['status' => 'failed'], 400);
    }

    public function escrowComplete(Escrow $escrow)
    {

        if ((int)$escrow->buyer_id === (int)request()->user()->id) {
            $escrow->complete();
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failed'], 400);
    }

    public function escrows()
    {
        $user = auth()->user();
        $escrow = Escrow::where('seller_id', $user->seller_id)->orWhere('buyer_id', $user->id)->get();
        return response()->json([
            'status' => 'success',
            'data' => $escrow
        ]);
    }
}
