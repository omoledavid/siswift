<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    // Get the wishlist for the authenticated user
    public function index(): JsonResponse
    {
        $wishlist = Wishlist::query()->where('user_id', Auth::id())->with('product')->get();

        return response()->json([
            'status' => true,
            'wishlist' => $wishlist,
        ]);
    }

    // Add a product to the wishlist
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|numeric|exists:products,id',
        ]);
//        dd($request->all());

        $wishlist = Wishlist::query()->firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $validated['product_id'],
        ]);

        return response()->json($wishlist, 201);
    }

    // Show a single wishlist item (optional)
    public function show($id): JsonResponse
    {
        $wishlistItem = Wishlist::query()->where('user_id', Auth::id())->find($id);

        if (!$wishlistItem) {
            return response()->json([
                'status' => false,
                'message' => 'Wishlist item not found',
            ]);
        }

        return response()->json([
            'status' => true,
            'wishlistItem' => $wishlistItem,
        ]);
    }

    // Remove a product from the wishlist
    public function destroy($id)
    {
        $wishlistItem = Wishlist::where('user_id', Auth::id())->find($id);

        if (!$wishlistItem) {
            return response()->json([]);
        }

        $wishlistItem->delete();

        return response()->json(['message' => 'Product removed from wishlist']);
    }
}
