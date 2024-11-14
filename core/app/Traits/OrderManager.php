<?php

namespace App\Traits;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Exceptions\CheckoutException;
use App\Models\AppliedCoupon;
use App\Models\AssignProductAttribute;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Deposit;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ShippingMethod;
use App\Models\StockLog;
use App\Models\User;

trait OrderManager
{
    public function checkout($request, $type)
    {
        $user = auth()->user();
        if (!$user) {
            throw new CheckoutException('User is not authenticated');
        }

        $general = GeneralSetting::first();
        $request->validate(['address' => 'required|max:50', 'payment' => 'required|in:1,2']);

        $payment_status = $this->getPaymentStatus($request->payment, $general);

        $carts = $this->getCartsForSessionOrUser($user->id);
        $totalAmount = $carts->sum(fn($cart) => $cart->offer_price * $cart->quantity);

        if ($totalAmount > $user->wallet->balance) {
            throw new CheckoutException('Insufficient funds in wallet');
        }

        $allOrders = collect();
        foreach ($carts as $cart) {
            $order = $this->createOrderForCart($cart, $user->id, $type, $payment_status);
            $allOrders->push($order);
            $this->updateProductInventory($cart);
        }

        $this->clearSessionData();
//        $this->clearCartData($user->id);

        return $allOrders;
    }

    private function getPaymentStatus(int $payment, $general): int
    {
        if ($payment === 2 && !$general->cod) {
            throw new CheckoutException('Cash on delivery is currently unavailable');
        }
        return $payment === 2 ? 2 : 0;
    }

    private function getCartsForSessionOrUser($userId)
    {
        return Cart::query()
            ->where('session_id', session('session_id'))
            ->orWhere('user_id', $userId)
            ->where('status', CartStatus::ACCEPTED)
            ->with('product')
            ->get();
    }

    private function createOrderForCart($cart, $userId, $type, $payment_status)
    {
        $order = Order::create([
            'order_number' => getTrx(),
            'user_id' => $userId,
            'seller_id' => $cart->product->seller_id,
            'order_type' => $type,
            'payment_status' => $payment_status,
            'total_amount' => getAmount($cart->offer_price * $cart->quantity),
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $cart->product_id,
            'quantity' => $cart->quantity,
            'base_price' => $cart->offer_price,
            'seller_id' => $cart->product->seller_id,
        ]);

        return $order;
    }

    private function updateProductInventory($cart)
    {
        $product = Product::find($cart->product_id);
        $product->track_inventory -= $cart->quantity;
        if ($product->track_inventory <= 0) {
            $product->status = ProductStatus::DELIST;
        }
        $product->save();
    }

    private function clearSessionData()
    {
        session()->forget('coupon');
    }

    private function clearCartData($userId)
    {
        Cart::where('session_id', session('session_id'))
            ->orWhere('user_id', $userId)
            ->delete();
    }
}

