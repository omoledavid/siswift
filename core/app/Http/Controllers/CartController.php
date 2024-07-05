<?php

namespace App\Http\Controllers;

use App\Models\AssignProductAttribute;
use App\Models\Cart;
use App\Models\ShippingMethod;
use App\Models\Product;
use App\Models\ProductStock;
use App\Traits\CartManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use CartManager;

    public function addToCart(Request $request)
    {
        $this->addProductToCart($request);

        return response()->json(['success' => 'Added to Cart']);
    }

    public function getCart()
    {
        $subtotal = 0;
        $user_id    = auth()->user()->id ?? null;

        if ($user_id != null) {
            $total_cart = Cart::where('user_id', $user_id)
                ->with(['product', 'product.offer'])
                ->whereHas('product', function ($q) {
                    return $q->whereHas('categories')->whereHas('brand');
                })
                ->orderBy('id', 'desc')
                ->get();

            if ($total_cart->count() > 3) {
                $latest = $total_cart->sortByDesc('id')->take(3);
            } else {
                $latest = $total_cart;
            }
        } else {
            $s_id       = session()->get('session_id');
            $total_cart = Cart::where('session_id', $s_id)
                ->with(['product', 'product.offer'])
                ->whereHas('product', function ($q) {
                    return $q->whereHas('categories')->whereHas('brand');
                })
                ->orderBy('id', 'desc')
                ->get();

            if ($total_cart->count() > 3) {
                $latest = $total_cart->sortByDesc('id')->take(3);
            } else {
                $latest = $total_cart;
            }
        }

        if ($total_cart->count() > 0) {

            foreach ($total_cart as $tc) {

                if ($tc->attributes != null) {
                    $s_price = AssignProductAttribute::priceAfterAttribute($tc->product, $tc->attributes);
                } else {
                    if ($tc->product->offer && $tc->product->offer->activeOffer) {
                        $s_price = $tc->product->base_price - calculateDiscount($tc->product->offer->activeOffer->amount, $tc->product->offer->activeOffer->discount_type, $tc->product->base_price);
                    } else {
                        $s_price = $tc->product->base_price;
                    }
                }
                $subtotal += $s_price * $tc->quantity;
            }
        }

        $more           = $total_cart->count() - count($latest);
        $emptyMessage  = 'No product in your cart';
        $coupon         = null;

        if (session()->has('coupon')) {
            $coupon = session('coupon');
        }

        return view(activeTemplate() . 'partials.cart_items', ['data' => $latest, 'subtotal' => $subtotal, 'emptyMessage' => $emptyMessage, 'more' => $more, 'coupon' => $coupon]);
    }

    public function getCartTotal()
    {
        $subtotal = 0;
        $user_id    = auth()->user()->id ?? null;
        if ($user_id != null) {
            $total_cart = Cart::where('user_id', $user_id)
                ->with(['product', 'product.offer'])
                ->whereHas('product', function ($q) {
                    return $q->whereHas('categories')->whereHas('brand');
                })
                ->get();
        } else {
            $s_id       = session()->get('session_id');
            $total_cart = Cart::where('session_id', $s_id)
                ->with(['product', 'product.offer'])
                ->whereHas('product', function ($q) {
                    return $q->whereHas('categories')->whereHas('brand');
                })
                ->get();
        }
        return $total_cart->count();
    }

    public function shoppingCart()
    {
        $user_id    = auth()->user()->id ?? null;
        if ($user_id != null) {
            $data = Cart::where('user_id', $user_id)->with(['product', 'product.stocks', 'product.categories', 'product.offer'])
                ->whereHas('product', function ($q) {
                    return $q->whereHas('categories')->whereHas('brand');
                })
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $s_id       = session()->get('session_id');
            $data = Cart::where('session_id', $s_id)
                ->with(['product', 'product.stocks', 'product.categories', 'product.offer'])
                ->whereHas('product', function ($q) {
                    return $q->whereHas('categories')->whereHas('brand');
                })
                ->orderBy('id', 'desc')
                ->get();
        }
        $pageTitle     = 'My Cart';
        $emptyMessage  = 'Cart is empty';
        return view(activeTemplate() . 'cart', compact('pageTitle', 'data', 'emptyMessage'));
    }

    public function updateCartItem(Request $request, $id)
    {
        if (session()->has('coupon')) {
            return response()->json(['error' => 'You have applied a coupon on your cart. If you want to delete any item form your cart please remove the coupon first.']);
        }

        $cart_item = Cart::findorFail($id);

        $attributes = $cart_item->attributes ?? null;
        if ($attributes !== null) {
            sort($attributes);
            $attributes = json_encode($attributes);
        }
        if ($cart_item->product->show_in_frontend && $cart_item->product->track_inventory) {
            $stock_qty  = ProductStock::showAvailableStock($cart_item->product_id, $attributes);

            if ($request->quantity > $stock_qty) {
                return response()->json(['error' => 'Sorry! your requested amount of quantity is not available in our stock', 'qty' => $stock_qty]);
            }
        }

        if ($request->quantity == 0) {
            return response()->json(['error' => 'Quantity must be greater than  0']);
        }
        $cart_item->quantity = $request->quantity;
        $cart_item->save();
        return response()->json(['success' => 'Quantity updated']);
    }

    public function removeCartItem($id)
    {
        $this->deleteCartitem($id);
        return response()->json(['success' => 'Item Deleted Successfully']);
    }

    public function checkout()
    {
        $user_id = auth()->user()->id ?? null;

        if ($user_id) {
            $data = Cart::where('user_id', $user_id)->get();
        } else {
            $data = Cart::where('session_id', session('session_id'))->get();
        }
        if ($data->count() == 0) {
            $notify[] = ['success', 'No product in your cart'];
            return back()->withNotify($notify);
        }
        $shipping_methods = ShippingMethod::where('status', 1)->get();
        $pageTitle = 'Checkout';
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        return view(activeTemplate() . 'checkout', compact('pageTitle', 'shipping_methods', 'countries'));
    }
}
