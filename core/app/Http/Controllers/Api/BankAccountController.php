<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\WithdrawDetail;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    private $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'account_name' => 'required|string|max:40',
            'account_number' => 'required|max:40',
            'bank_name' => 'required|string|max:600',
            'bank_code' => 'required|string|max:200'
        ]);

        /** @var User $user */
        $user = auth()->user();

        $result = $this->paystackService->validateBankAccount($request->input('account_number'), $request->input('bank_code'));

        if ($result['error']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        if(!$user->bankAccounts()
            ->where(['account_number' => $request->input('account_number'), 'bank_name' => $request->input('bank_name')])
            ->exists()){
            return response()->json([
                'message' => 'Account number already exist'
            ]);
        }

        $bankAccount = $user->bankAccounts()->create([
            'account_number' => $request->input('account_number'),
            'bank_name' => $request->input('bank_name'),
            'bank_code' => $request->input('bank_code'),
            'account_name' => $request->input('account_name'),
        ]);

        return response()->json([
            'data' => $bankAccount,
            'response' => $result['data'],
        ]);

    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();
        return response()->noContent();
    }
}
