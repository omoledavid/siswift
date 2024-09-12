<?php

namespace App\Http\Controllers\Api;

use App\Enums\Feature;
use App\Enums\ProductStatus;
use App\Models\ProductView;
use App\Traits\ShopManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Traits\ProductManager;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Traits\ProductVariantManager;
use Laravel\Ui\Presets\React;

class ProductController extends Controller
{
    use ProductManager, ShopManager;

    public function __construct()
    {
        $this->middleware('web');
    }


    protected function seller()
    {
        return request()->user();
    }

    protected function id()
    {
        return $this->seller()->id;
    }

    /*
    ==================Product Manager TRAIT==================
    */

    public function index()
    {
        $user = request()->user();
        $sellerIdToExclude = $user->seller_id;

        // Query the products while excluding the specified seller's products
        $allProducts = Product::where('track_inventory', '>=', 1)->where('status', ProductStatus::ACTIVE)->orderBy('id', 'desc')->paginate(12);

        if ($allProducts->isEmpty()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No products found',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => ['products' => $allProducts],
        ]);
    }


    public function show(Product $product)
    {
        $user = auth()->user();
        $viewedProduct = ProductView::query()->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
        if(!$viewedProduct)
        {
            $product->views += 1;
            $product->save();

            ProductView::create([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);
        }
        // Fetch suggested products
        $suggestedProduct = Product::where('status', ProductStatus::ACTIVE)->orderBy('sold', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'suggest' => $suggestedProduct
        ]);
    }


    public function sellerProducts()
    {
        $user = auth()->user();
        $products = Product::where('seller_id', $user->seller_id)->orderBy('id', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $valData = featureValue(Feature::UPLOAD->value);
        $photoCount = is_array($request->photos) ? count($request->photos) : 0;
        if ($photoCount > $valData) {
            return response()->json([
                'status' => 'failed',
                'message' => "You can only upload". $valData. " photos at a time 45",
            ], 400);
        };
        $data = $this->storeProduct($request, null, $this->id());
        if (!$data) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Create shop to continue',
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $data
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $valData = featureValue(Feature::UPLOAD->value);
        $photoCount = count($request->photos ?? []);
        $categoriesCount = count($request->categories ?? []);
        if ($photoCount > $valData) {
            return response()->json([
                'status' => 'failed',
                'message' => "You can only upload $valData photos at a time",
            ], 400);
        };
        if ($categoriesCount > 2) {
            return response()->json([
                'status' => 'failed',
                'message' => "You can only select 2 categories at a time",
            ], 400);
        };
        return response()->json([
            'status' => 'success',
            'data' => $this->storeProduct($request, $product->id, $this->id())
        ]);
    }

    public function trashed()
    {
        return view('seller.products.index', $this->products($this->id(), true));
    }

    public function create()
    {
        return view('seller.products.create', $this->productCreate());
    }

    public function edit($id)
    {
        return view('seller.products.create', $this->editProduct($id, $this->id()));
    }

    public function destroy($id)
    {
        $this->deleteProduct($id, auth()->user()->seller_id);
        return response()->json('Product deleted successfully');
    }

    /*
    ==================ProductVariantManager TRAIT==================
    */

    public function addVariant($product_id)
    {
        return view('seller.products.variant.create', $this->addProductVariant($product_id, $this->id()));
    }

    public function storeVariant(Request $request, $id)
    {
        return back()->withNotify(
            $this->storeProductVariant($request, $id, $this->id())
        );
    }

    public function updateVariant(Request $request, $id)
    {
        return back()->withNotify(
            $this->updateProductVariant($request, $id, $this->id())
        );
    }

    public function deleteVariant($id)
    {
        return back()->withNotify(
            $this->deleteProductVariant($id, $this->id())
        );
    }

    public function reviews()
    {
        return view('seller.products.reviews', $this->productReviews($this->id()));
    }

    public function reviewSearch(Request $request)
    {
        if ($request->key != null) {
            return view('seller.products.reviews', $this->productReviewSearch($request->key, $this->id()));
        } else {
            return redirect()->route('seller.product.reviews');
        }
    }


    public function addVariantImages($id)
    {
        return view('seller.products.variant.images', $this->addProductVariantImages($id, $this->id()));
    }

    public function saveVariantImages(Request $request, $id)
    {
        $storeImages = $this->saveProductVariantImages($request, $id, $this->id());

        return redirect()->back()->withNotify($storeImages);
    }

    public function delist($id)
    {
        $user = auth()->user();
        $product = Product::where('id', $id)->first();
        if ($product->seller_id != $user->seller_id) {
            return response()->json([
                'status' => false,
                'message' => 'You can\'t delist other\'s product'
            ], 403);
        }
        if ($product->status == ProductStatus::DELIST->value || $product->status == ProductStatus::PENDING->value) {
            return response()->json([
                'status' => false,
                'message' => 'This product is already delisted or waiting approval'
            ], 400);
        }
        $product->status = ProductStatus::DELIST;
        $product->save();
        return response()->json([
            'status' => true,
            'message' => 'Product has been Unlisted',
            'product' => $product
        ]);
    }

    public function relist($id)
    {
        $user = auth()->user();
        $product = Product::where('id', $id)->first();
        if ($product->seller_id != $user->seller_id) {
            return response()->json([
                'status' => false,
                'message' => 'You can\'t relist other\'s product'
            ], 403);
        }
        if ($product->status == ProductStatus::PENDING->value) {
            return response()->json([
                'status' => false,
                'message' => 'This product is pending approval'
            ], 400);
        }
        $product->status = ProductStatus::PENDING;
        $product->save();
        return response()->json([
            'status' => true,
            'message' => 'Product has been relisted',
            'product' => $product
        ]);
    }

    public function stats($id): JsonResponse
    {
        // Find the product by ID, or fail if not found
        $product = Product::findOrFail($id);

        // Get the current month start and end dates
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        // Get the last month start and end dates
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // All-time stats (from the product columns directly)
        $allStats = [
            'views' => $product->views,
            'chats' => $product->chats,
            'sold' => $product->sold,
        ];

        // This month stats (assuming you store timestamps for views, chats, and sold events)
        $thisMonthStats = [
            'views' => $product->where('id', $id)
                ->whereBetween('updated_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('views'),
            'chats' => $product->where('id', $id)
                ->whereBetween('updated_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('chats'),
            'sold' => $product->where('id', $id)
                ->whereBetween('updated_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('sold'),
        ];

        // Last month stats
        $lastMonthStats = [
            'views' => $product->where('id', $id)
                ->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd])
                ->sum('views'),
            'chats' => $product->where('id', $id)
                ->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd])
                ->sum('chats'),
            'sold' => $product->where('id', $id)
                ->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd])
                ->sum('sold'),
        ];

        // Return the stats as JSON
        return response()->json([
            'status' => true,
            'all_stats' => $allStats,
            'this_month' => $thisMonthStats,
            'last_month' => $lastMonthStats,
        ]);
    }


}
