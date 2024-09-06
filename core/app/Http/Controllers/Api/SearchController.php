<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // Initialize the query builder
        $query = Product::query()->where('status', ProductStatus::ACTIVE);

        // Filter by brand
        if ($request->has('brand') && $request->brand != '') {
            $query->where('brand_id', $request->brand);
        }

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Filter by location
        if ($request->has('state') && $request->state != '') {
            $query->where('state', 'like', '%' . $request->state . '%');
        }

        // Search by name, product model, or specification
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('model', 'like', '%' . $searchTerm . '%')
                    ->orWhere('specification', 'like', '%' . $searchTerm . '%');
            });
        }
        // Apply sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'latest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'low_price':
                    $query->orderBy('base_price', 'asc');
                    break;
                case 'popular':
                    // Assuming 'popularity' is a column, if not, replace it with the appropriate logic
                    $query->orderBy('sold', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc'); // Default to latest
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc'); // Default sorting
        }

        // Get the results
        $products = $query->get();
        $ads = Product::where('status', ProductStatus::ACTIVE)->where('is_featured', 1)->get();

        // Return the results to the view
        return response()->json([
            'status' => true,
            'data' => $products,
            'ads' => $ads
        ]);
    }
}
