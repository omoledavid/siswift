<?php

namespace App\Http\Controllers\Api;

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
    use ProductManager;

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
        return response()->json([
            'status' => 'success',
            'data' => $this->products()
        ]);
    }

    public function show(Product $product)
    {
        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->storeProduct($request, null, $this->id())
        ]);
    }

    public function update(Request $request, Product $product)
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->storeProduct($request, $product->id, $this->id())
        ]);
    }

    public function productByCat($param)
    {
        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'message' => ['success' => $param]
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

    public function delete($id)
    {
        $this->deleteProduct($id, $this->id());
        return response()->noContent();
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
}
