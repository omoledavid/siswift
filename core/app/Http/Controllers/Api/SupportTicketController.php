<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\SupportTicketManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    use SupportTicketManager;

    protected function seller()
    {
        return request()->user();
    }

    public function createTicket(Request $request): JsonResponse
    {

        $response = $this->storeTicket($request, $request->user()->id, '');
        $seller = $this->seller();

        return response()->json([
            'status' => 'success',
            'data' => $request['ticket']
        ]);
    }
}
