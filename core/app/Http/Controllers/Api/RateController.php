<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class RateController extends Controller
{
    public function index(){

    }
    //

    public function store(Request $request){
        $this->validate($request, [
            'pid'       => 'required|string',
            'review'    => 'required|string',
            'rating'    => 'required|numeric',
        ]);

        $product_id = $request->pid;
        $check_user = ProductReview::where('user_id', auth()->user()->id)->where('product_id', $product_id);
        if($check_user->count() == 0){
            $add_review = ProductReview::create([
                'user_id'       => auth()->user()->id,
                'product_id'    => $product_id,
                'review'        => $request->review,
                'rating'        => $request->rating,
            ]);
            if ($add_review) {
                $notify[] = ['success', 'Review Added'];
            } else {
                $notify[] = ['error', 'ERROR: Something went wrong!!'];
            }
        }else{
            $notify[] = ['error', 'You have already reviewd for this product'];
        }
        return response()->json($notify);
    }
}
