<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    }
}
