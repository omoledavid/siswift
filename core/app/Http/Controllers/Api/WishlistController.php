<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id    = auth()->user()->id??null;
        $notify[] = [];

        if($user_id != null){
            $wishlist_data = Wishlist::where('user_id', $user_id)
                ->with(['product', 'product.stocks', 'product.categories' ,'product.offer'])
                ->whereHas('product', function($q){
                    return $q->whereHas('categories')->whereHas('brand');
                })
                ->get();
        }else{
            $s_id       = session()->get('session_id');
            if(!$s_id){
                return redirect(route('home'))->withNotify($notify);
            }
            $wishlist_data = Wishlist::where('session_id', $s_id)
                ->with(['product', 'product.stocks', 'product.categories' ,'product.offer'])
                ->whereHas('product', function($q){
                    return $q->whereHas('categories')->whereHas('brand');
                })
                ->get();
        }
        return response()->json($wishlist_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',

        ]);

        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user_id = auth()->user()->id??null;

        $s_id = session()->get('session_id');
        if ($s_id == null) {
            session()->put('session_id', uniqid());
            $s_id = session()->get('session_id');
        }

        if($user_id != null){
            $wishlist = Wishlist::where('user_id', $user_id)
                ->where('product_id', $request->product_id)
                ->first();
        }else{

            $wishlist = Wishlist::where('session_id', $s_id)
                ->where('product_id', $request->product_id)
                ->first();
        }

        if($wishlist) {
            return response()->json(['error' => 'Already in the wish list']);
        }else {
            $wishlist = new Wishlist();
            $wishlist->user_id    = auth()->user()->id??null;
            $wishlist->session_id = $s_id;
            $wishlist->product_id = $request->product_id;
            $wishlist->save();
        }
        $wishlist = session()->get('wishlist');

        $wishlist[$request->product_id] = [
            "id" => $request->product_id,
        ];

        session()->put('wishlist', $wishlist);
        return response()->json(['success' => 'Added to Wishlist']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($id==0){
            $user_id    = auth()->user()->id??null;
            if($user_id != null){
                $wishlist = Wishlist::where('user_id', $user_id);
            }else{
                $s_id       = session()->get('session_id');
                if(!$s_id){
                    abort(404);
                }
                $wishlist = Wishlist::where('session_id', $s_id);
            }

        }else{
            $wishlist   = Wishlist::findorFail($id);
            $product_id = $wishlist->product_id;
            $wl         = session()->get('wishlist');
            unset($wl[$product_id]);
            session()->put('wishlist', $wl);
        }
        Artisan::call('cache:clear');
        if($wishlist) {
            $wishlist->delete();
            return response()->json(['success' => 'Deleted From Wishlist']);
        }

        return response()->json(['error' => 'This product isn\'t available in your wishlsit']);
    }
}
