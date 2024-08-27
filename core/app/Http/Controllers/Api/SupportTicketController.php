<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
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

    public function index(): JsonResponse
    {
        $seller = $this->seller();
        $tickets = SupportTicket::where('user_id', $seller->id)->orwhere('seller_id', $seller->id)
            ->orderBy('priority', 'desc')
            ->orderBy('id','desc')
            ->paginate(getPaginate());
        return response()->json([
            'status' => 'sucessful',
            'tickets' => $tickets
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        $savedTicket = $this->storeTicket($request, $request->user()->id, 'user');
        return response()->json([
            'status' => 'success',
            'data' => $savedTicket
        ]);
    }
}
