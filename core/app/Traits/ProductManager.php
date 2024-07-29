<?php

namespace App\Traits;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\ProductStock;
use App\Models\Shop;
use App\Models\User;
use App\Rules\FileTypeValidate;
use Illuminate\Support\Facades\Auth;

trait ProductManager
{
    protected function pageTitle($isTrashed, $searchKey)
    {
        if ($isTrashed)
            $title = "All Trashed Products";
        else
            $title = "All Products";
        if ($searchKey)
            $title = "Product Search : '$searchKey'";

        return $title;
    }

    public function products($sellerId = 0, $isTrashed = false)
    {
        $search = trim(strtolower(request()->search));
        $query = Product::query();
        if ($sellerId) {
            $query = $query->sellers();
        }

        if (request()->has('recommeded')) {
            $query->inRandomOrder();
        }

        $query = $query->with(['categories', 'brand'])->whereHas('categories', function ($query) {
            if (request()->category) {
                $category = strtolower(request()->category);
                $query->where('categories.name', 'LIKE', "%$category%");
            }
        })->whereHas('brand', function ($query) {
            if (request()->brand) {
                $brand = strtolower(request()->brand);
                $query->where('brands.name', 'LIKE', "%$brand%");
            }
        });

        if ($min_price = request()->min_price) {
            $query->where('base_price', '>=', (int)$min_price);
        }

        if ($max_price = request()->max_price) {
            $query->where('base_price', '<=', (int)$max_price);
        }

        if ($sort_by = strtolower(request()->sort_by)) {
            $query->orderBy($sort_by, request()->has('asc') ? 'ASC' : 'DESC');
        }


        if ($isTrashed)
            $query = $query->onlyTrashed();
        if ($search)
            $query = $query->where('name', 'like', "%$search%");

        $data['products'] = $query->orderByDesc('id')->paginate(getPaginate());
        $data['pageTitle'] = $this->pageTitle($isTrashed, $search);
        $data['emptyMessage'] = "No product found";
        return $data;
    }

    public function pendingProducts($sellerId = 0, $isTrashed = false)
    {
        $search = trim(strtolower(request()->search));

        $query = Product::query();
        if ($sellerId) {
            $query = $query->sellers();
            $query = $query->with(['categories', 'brand', 'stocks'])->whereHas('categories')->whereHas('brand');
        }
        if ($isTrashed)
            $query = $query->onlyTrashed();
        if ($search)
            $query = $query->where('name', 'like', "%$search%");

        $data['products'] = $query->where('status', 0)->orderByDesc('id')->paginate(getPaginate());
        $data['pageTitle'] = 'Pending Products';
        $data['emptyMessage'] = "No product found";
        return $data;
    }

    public function productByVendor($admin = true, $isTrashed = false)
    {
        $search = trim(strtolower(request()->search));

        $query = Product::query();
        if ($isTrashed)
            $query = $query->onlyTrashed();
        if ($search)
            $query = $query->where('name', 'like', "%$search%");
        if ($admin) {
            $data['pageTitle'] = 'Products By Admin';
            $query = $query->where('seller_id', 0);
        } else {
            $data['pageTitle'] = 'Products By Seller';
            $query = $query->where('seller_id', '!=', 0);
        }

        $data['products'] = $query->orderByDesc('id')->paginate(getPaginate());
        $data['emptyMessage'] = "No product found";
        return $data;
    }

    public function productCreate()
    {
        $data['categories'] = Category::with('allSubcategories')->where('parent_id', null)->get();
        $data['brands'] = Brand::orderBy('name')->get();
        $data['pageTitle'] = "Add New Product";
        return $data;
    }

    public function editProduct($id, $sellerId = 0)
    {
        if ($sellerId)
            $data['product'] = Product::where('seller_id', $sellerId)->where('id', $id)->firstOrFail();
        else
            $data['product'] = Product::whereId($id)->first();

        $data['categories'] = Category::with('allSubcategories')->where('parent_id', null)->get();
        $data['brands'] = Brand::orderBy('name')->get();
        $data['images'] = [];
        foreach ($data['product']->productPreviewImages as $key => $image) {
            $img['id'] = $image->id;
            $img['src'] = getImage(imagePath()['product']['path'] . '/' . $image->image);
            $data['images'][] = $img;
        }

        $data['pageTitle'] = "Edit Product";
        return $data;
    }


    public function storeProduct($request, $id, $sellerId = 0)
    {
        $validation_rule = $this->getProductValidationRule($id);
        $request->validate($validation_rule, [
            'specification.*.name.required' => 'All specification name is required',
            'specification.*.value' => 'All specification value is required',
        ]);
        if ($request->user()) {
            $user = $request->user();
            if ($user->seller_id == null) {
//                $seller = $user->seller;

                if (!$seller) {
                    $seller = $this->createSeller([
                        'fullname' => $user->fullname,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'address' => $user->address,
                        'country' => $user->country
                    ]);
                }
                $shop = new Shop();
                $shop->name = ' ';
                $shop->seller_id = $seller->id;
                $shop->user_id = $user->id;
                $shop->status = 0;
                $shop->phone = ' ';
                $shop->address = ' ';
                $shop->opens_at = ' ';
                $shop->closed_at = ' ';
                $shop->meta_title = ' ';
                $shop->meta_description = ' ';
                $shop->meta_keywords = $request->meta_keywords ?? null;
                $shop->social_links = $request->social_links ?? null;
                $shop->save();

                $request->user()->update([
                    'seller_id' => $shop->seller_id
                ]);
            }
        }

        $product = new Product();

        if ($id) {
            $product = Product::findOrFail($id);
            if(auth()->user()){
                $user = $request->user();
            }else{
                $user = User::where('seller_id', $product->seller_id)->first();
            }
            if ($product->seller_id != $user->seller_id) {
                $notify[] = ['error', 'This product doesn\'t belong to this seller'];
                return $notify;
            }

            $product->status = 1;
        } else {
            //adding admin id
            $product->status = $sellerId == 0 ? 1 : 0;
        }

        if ($request->hasFile('main_image')) {
            try {
                $request->merge(['image' => uploadImage($request->main_image, imagePath()['product']['path'], imagePath()['product']['size'], @$product->main_image, imagePath()['product']['thumb'])]);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Could not upload the main image'];
                return $notify;
            }
        } else {
            $request->merge(['image' => $product->main_image]);
        }
        $shop_id = Shop::where('seller_id', $user->seller_id)->first()->id;


        $product->seller_id = $user->seller_id;
        $product->brand_id = $request->brand_id;
        $product->name = $request->name;
        $product->model = $request->model;
        $product->main_image = $request->image;
        $product->location = $request->location;
        $product->description = $request->description;
        $product->base_price = $request->base_price;
        $product->ram = $request->ram;
        $product->condition = $request->condition;
        $product->sim = $request->sim;
        $product->state = $request->state;
        $product->lga = $request->lga;
        $product->bulk_price = $request->bulk_price;
        $product->colour = $request->colour;
        $product->shop_id = $shop_id;
        $product->save();

        //Check Old Images
        $previous_images = $product->productPreviewImages->pluck('id')->toArray();
        $image_to_remove = array_values(array_diff($previous_images, $request->old ?? []));

        foreach ($image_to_remove as $item) {
            $productImage = ProductImage::find($item);
            $location = imagePath()['product']['path'];

            removeFile($location . '/' . $productImage->image);
            $productImage->delete();
        }

        if ($request->hasFile('photos')) {
            foreach ($request->photos as $image) {
                try {
                    $product_img = uploadImage($image, imagePath()['product']['path'], imagePath()['product']['size'], null, imagePath()['product']['thumb']);
                } catch (\Exception $exp) {
                    $notify[] = ['error', 'Could not upload additional images'];
                    return $notify;
                }
                $productImage = new ProductImage();
                $productImage->product_id = $product->id;
                $productImage->image = $product_img;
                $productImage->save();
            }
        }

        $type = 'Added';
        //product branch

        if ($id) {
            $product->categories()->sync($request->categories);
            $type = 'Updated';

            //If the value of track_inventory or has_variants is changed then delete the prev inventory


            // Check stock table to update the sku in stock table
        } else $product->categories()->attach($request->categories);
//        $seller = User::findorFail(auth()->user()->id);
//        if($seller->seller_id == null){
//            $seller->seller_id = $sellerId;
//            $seller->save();
//        }

        if(auth()->user()){
            return $product;
        }else{
            $notify[]=['success', 'Product updated successfully'];
            return $notify;
        }
    }

    public function storeProductAndReturnNotification($request, $id, $sellerId = 0)
    {
        $this->storeProduct($request, $id, $sellerId);
        $message = "Product saved Successfully";
        $notify[] = ['success', $message];
        return $notify;
    }

    public function deleteProduct($id, $sellerId = 0)
    {
        $query = Product::where('id', $id);
        if ($sellerId)
            $query = $query->where('seller_id', $sellerId);
        $product = $query->withTrashed()->firstOrFail();
        $type = 'Deleted';

        if ($product->trashed()) {
            $product->restore();
            $type = 'Restored';
        } else $product->delete();

        $notify[] = ['success', "Product $type Successfully"];
        return $notify;
    }

    protected function getProductValidationRule($id)
    {
        $validation_rule = [
            'name' => 'required|string|max:191',
            'model' => 'nullable|string|max:100',
            'brand_id' => 'required|integer',
            'base_price' => 'required|numeric',
            "categories" => 'required|array|min:1',
            'has_variants' => 'sometimes|required|numeric|min:1|max:1',
            'track_inventory' => 'required|numeric|min:1|max:20',
            'show_in_frontend' => 'sometimes|required|numeric|min:1|max:1',
            'description' => 'nullable|string',
            'summary' => 'nullable|string|max:360',
            'sku' => 'nullable',
//          'sku'                   => 'nullable|required_without:has_variants|string|max:100',
            'extra' => 'sometimes|required|array',
            'extra.*.key' => 'required_with:extra',
            'extra.*.value' => 'required_with:extra',
            'specification' => 'sometimes|required|array',
            'specification.*.name' => 'required_with:specification',
            'specification.*.value' => 'required_with:specification',
            'meta_title' => 'nullable|string|max:191',
            'meta_description' => 'nullable|string|max:191',
            'meta_keywords' => 'nullable|array',
            'meta_keywords.array.*' => 'nullable|string',
            'video_link' => 'nullable|string',
            'photos' => 'required_if:id,0|array|min:1',
            'photos.*' => ['image', new FileTypeValidate(['jpeg', 'jpg', 'png'])],
        ];

        if ($id == 0) {
            $validation_rule['main_image'] = ['required', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])];
        } else {
            $validation_rule['main_image'] = ['nullable', 'image', new FileTypeValidate(['jpeg', 'jpg', 'png'])];
        }

        return $validation_rule;
    }

    protected function checkSKU($sku, $id)
    {
        Product::where('sku', $sku)->where('id', '!=', $id)->with('stocks')->orWhereHas('stocks', function ($q) use ($sku, $id) {
            $q->where('sku', $sku)->where('product_id', '!=', $id);
        })->first();
    }


    public function productReviews($sellerId = 0)
    {
        $query = ProductReview::with(['product', 'user']);

        if ($sellerId) {
            $query = $query->whereHas('product', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            });
        } else {
            $query = $query->whereHas('product');
        }

        $data['reviews'] = $query->whereHas('user')->latest()->paginate(getPaginate());

        $data['pageTitle'] = "Product Reviews";
        $data['emptyMessage'] = "No review yet";

        return $data;
    }

    public function productReviewSearch($key, $sellerId = 0)
    {
        $query = ProductReview::query();

        if ($sellerId) {
            $query = $query->whereHas('product', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            });
        } else {
            $query->whereHas('product');
        }

        $query = $query->with('product', 'user');

        $query = $query->where(function ($q) use ($key) {
            $q->where('review', 'like', "%$key%")
                ->orWhere('rating', 'like', "%$key%")
                ->orWhereHas('user', function ($user) use ($key) {
                    $user->where('username', 'like', "%$key%");
                })->orWhereHas('product', function ($product) use ($key) {
                    $product->where('name', 'like', "%$key%");
                });
        });

        $data['reviews'] = $query->whereHas('user')->latest()->paginate(getPaginate());
        $data['pageTitle'] = "Search of Reviews $key";
        $data['emptyMessage'] = "No Review Yet";

        return $data;
    }
}
