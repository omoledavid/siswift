<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ShopCreationError;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Traits\ShopManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    use ShopManager;

    public function index()
    {
        return response()->json(Shop::all());
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

}
