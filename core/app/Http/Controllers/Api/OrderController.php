<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function index(){
        return response()->json(Order::all());
    }
    public function store(Request $request){

    }
    public function show(Order $order){
        return response()->json($order);
    }
}
