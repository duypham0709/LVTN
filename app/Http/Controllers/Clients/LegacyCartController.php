<?php

/*
Legacy cart implementation retained as commented source:

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->merge(['quantity' => (int) $request->quantity]);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $isExpired = $product->expiry_date?->isBefore(today()->addDay()) ?? false;
        if ($product->stock < 1 || $product->status !== 'in_stock' || $isExpired) {
            return response()->json(['message' => 'Sản phẩm hiện không thể mua.'], 400);
        }

        if (Auth::check()) {
            $cartItem = CartItem::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->first();

            $newQuantity = ($cartItem?->quantity ?? 0) + $request->quantity;
            if ($newQuantity > $product->stock) {
                return response()->json(['message' => 'Số lượng vượt quá tồn kho.'], 400);
            }

            if ($cartItem) {
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                CartItem::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'quantity' => $newQuantity,
                ]);
            }

            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
        } else {
            $cart = session()->get('cart', []);
            $newQuantity = ($cart[$request->product_id]['quantity'] ?? 0) + $request->quantity;
            if ($newQuantity > $product->stock) {
                return response()->json(['message' => 'Số lượng vượt quá tồn kho.'], 400);
            }

            if (isset($cart[$request->product_id])) {
                $cart[$request->product_id]['quantity'] = $newQuantity;
            } else {
                $cart[$request->product_id] = [
                    'product_id' => $request->product_id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $newQuantity,
                    'stock' => $product->stock,
                    'image' => $product->images->first()->image ?? 'uploads/products/default-product.png',
                ];
            }

            session()->put('cart', $cart);
            $cartCount = array_sum(array_column($cart, 'quantity'));
        }

        return response()->json([
            'message' => true,
            'cart_count' => $cartCount,
        ]);
    }

    public function loadMiniCart()
    {
        $cartItems = [];

        if (auth()->check()) {
            $cartItems = CartItem::with('product')->where('user_id', auth()->id())->get();
        } else {
            $cartItems = session('cart', []);
        }

        return response()->json([
            'status' => true,
            'html' => view('clients.components.includes.mini_cart', compact('cartItems'))->render(),
        ]);
    }

    public function removeFromMiniCart(Request $request)
    {
        $request->validate(['product_id' => 'required']);

        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->where('product_id', $request->product_id)->delete();
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
        } else {
            $cart = session()->get('cart', []);
            unset($cart[$request->product_id]);
            session()->put('cart', $cart);
            $cartCount = array_sum(array_column($cart, 'quantity'));
        }

        return response()->json([
            'status' => true,
            'cart_count' => $cartCount,
        ]);
    }

    public function viewCart()
    {
        if (Auth::check()) {
            $cartItems = CartItem::where('user_id', Auth::id())->with('product')->get()->map(function ($item) {
                return [
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'stock' => $item->product->stock,
                    'image' => $item->product->images->first()->image ?? 'uploads/products/default-product.png',
                ];
            })->toArray();
        } else {
            $cartItems = session()->get('cart', []);
        }

        return view('clients.pages.cart', compact('cartItems'));
    }

    public function updateCart(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity;

        if (Auth::check()) {
            $cartItem = CartItem::where('user_id', Auth::id())->where('product_id', $productId)->first();
            if (! $cartItem) {
                return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
            }

            $product = Product::find($productId);
            if ($quantity > $product->stock) {
                return response()->json(['error' => 'Quá số lượng tồn kho'], 400);
            }

            $cartItem->quantity = $quantity;
            $cartItem->save();
        } else {
            $cart = session()->get('cart', []);
            if (! isset($cart[$productId])) {
                return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
            }

            $product = Product::find($productId);
            if ($quantity > $product->stock) {
                return response()->json(['error' => 'Quá số lượng tồn kho'], 400);
            }

            $cart[$productId]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }

        $subtotal = $quantity * $product->price;
        $total = $this->calculateCartTotal();

        if (Auth::check()) {
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
        } else {
            $cart = session()->get('cart', []);
            $cartCount = array_sum(array_column($cart, 'quantity'));
        }

        return response()->json([
            'status' => true,
            'quantity' => $quantity,
            'subtotal' => number_format($subtotal, 0, ',', '.'),
            'total' => number_format($total, 0, ',', '.'),
            'cart_count' => $cartCount,
        ]);
    }

    public function calculateCartTotal()
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id())->with('product')->get()->sum(fn ($item) => $item->quantity * $item->product->price);
        }

        $cart = session()->get('cart', []);

        return collect($cart)->sum(fn ($item) => $item['quantity'] * $item['price']);
    }

    public function removeCartItem(Request $request)
    {
        $productId = $request->product_id;

        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->where('product_id', $productId)->delete();
        } else {
            $cart = session()->get('cart', []);
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        $total = $this->calculateCartTotal();

        if (Auth::check()) {
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
        } else {
            $cart = session()->get('cart', []);
            $cartCount = array_sum(array_column($cart, 'quantity'));
        }

        return response()->json([
            'status' => true,
            'total' => number_format($total, 0, ',', '.'),
            'cart_count' => $cartCount,
        ]);
    }
}
*/
