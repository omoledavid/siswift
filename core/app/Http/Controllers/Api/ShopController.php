<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ShopCreationError;
use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\SellLog;
use App\Models\Shop;
use App\Traits\ShopManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    use ShopManager;

    public function index()
    {
        $user = auth()->user();
        return response()->json(Shop::where('seller_id', $user->seller_id)->get());
    }
    public function store(Request $request): JsonResponse
    {
        try {

            $shop = $this->createNewShop($request);

            return response()->json([
                'status' => 'success',
                'data' => $shop
            ]);
        }catch (ShopCreationError $e){
            return response()->json([
                'status' => 'error',
                'data' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, Shop $shop): JsonResponse
    {
        $shop = $this->updateShop($shop, $request);

        return response()->json([
            'status' => 'success',
            'data' => $shop
        ]);
    }
    public function destroy(Shop $shop): JsonResponse
    {
        $shop->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Shop has been deleted'
        ]);
    }
    public function stat(){
        $seller = auth()->user();
        $order['pending']       = OrderDetail::pendingOrder()->where('seller_id',$seller->id)->count();
        $order['processing']    = OrderDetail::processingOrder()->where('seller_id',$seller->id)->count();
        $order['delivered']     = OrderDetail::completedOrder()->where('seller_id',$seller->id)->count();
        $order['cancelled']     = OrderDetail::cancelledOrder()->where('seller_id',$seller->id)->count();
        $product['approved']    = Product::active()->where('seller_id',$seller->id)->count();
        $product['pending']     = Product::pending()->where('seller_id',$seller->id)->count();
        $product['total_sold']  = Product::active()->where('seller_id',$seller->id)->sum('sold');
        $sale['last_seven_days']          = SellLog::where('seller_id',$seller->id)->where('created_at', '>=', Carbon::today()->subDays(7))->sum('after_commission');

        $sale['last_fifteen_days']        = SellLog::where('seller_id',$seller->id)->where('created_at', '>=', Carbon::today()->subDays(15))->sum('after_commission');

        $sale['last_thirty_days']         = SellLog::where('seller_id',$seller->id)->where('created_at', '>=', Carbon::today()->subDays(30))->sum('after_commission');
        return response()->json([
            'Proccessing' => $order['processing'],
            'Delivered' => $order['delivered'],
            'Cancelled' => $order['cancelled'],
            'pending order' => $product['pending'],
            'total_sold' => $product['total_sold'],
            'sales' => [
                'last_seven_days' => $sale['last_seven_days'],
                'last_fifteen_days' => $sale['last_fifteen_days'],
                'last_thirty_days' => $sale['last_thirty_days'],
            ],
            'ads' => 0,
            'ads_duration' => 0

        ]);
    }

}
