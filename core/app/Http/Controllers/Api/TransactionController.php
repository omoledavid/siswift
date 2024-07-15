<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'name'                  => 'required|string|max:40',
            'phone'                 => 'required|string|max:40',
            'address'               => 'required|string|max:600',
        ]);


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
