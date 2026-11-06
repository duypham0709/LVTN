<?php

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

        // Nếu đã đăng nhập sẽ lưu db
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
            //  nếu chưa đăng nhập thì lưu sp ở session
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

            // Chưa login
            $cart = session()->get('cart', []);
            unset($cart[$request->product_id]);
            session()->put('cart', $cart);

            // Tính tổng mảng
            $cartCount = array_sum(array_column($cart, 'quantity'));
        }

        return response()->json([
            'status' => true,
            'cart_count' => $cartCount,
        ]);
    }

    // trang giỏ hàng lớn
    public function viewCart()
    {
        if (Auth::check()) {
            // Neu nguoi dung dang nhap thi minh se lay gio hang tu database
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
            // Nguoc lai thi lay gio hang tu session
            $cartItems = session()->get('cart', []);
        }

        return view('clients.pages.cart', compact('cartItems'));
    }

    // Xử lý cập nhật số lượng sản phẩm trong giỏ hàng
    public function updateCart(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity;

        // 1. Cập nhật dữ liệu vào DB/Session
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

        // 2. TÍNH TOÁN LẠI MỌI THỨ (Server tính, JS không cần tính)
        $subtotal = $quantity * $product->price; // Thành tiền của món đó
        $total = $this->calculateCartTotal();    // Tổng tiền hàng
        //$grandTotal = $total + 15000;            // Tổng cộng (kèm ship)

        // Cập nhật số lượng icon giỏ hàng
        if (Auth::check()) {
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
        } else {
            $cart = session()->get('cart', []);
            // trả về tổng số lượng sản phẩm trong giỏ hàng
            $cartCount = array_sum(array_column($cart, 'quantity'));
        }

        return response()->json([
            'status' => true,
            'quantity' => $quantity,
            // Trả về chuỗi đã format sẵn
            'subtotal' => number_format($subtotal, 0, ',', '.'),
            'total' => number_format($total, 0, ',', '.'),
            //'grandTotal' => number_format($grandTotal, 0, ',', '.'),
            'cart_count' => $cartCount, // Trả về số lượng để update icon
        ]);
    }

    public function calculateCartTotal() // Hàm tính tổng tiền hàng
    {
        if (Auth::check()) {
            return CartItem::where('user_id', Auth::id())->with('product')->get()->sum(fn($item) => $item->quantity * $item->product->price);
        } else {
            $cart = session()->get('cart', []);

            return collect($cart)->sum(fn($item) => $item['quantity'] * $item['price']);
        }
    }

    // Xử lý xóa sản phẩm trong giỏ hàng
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

        // Tính toán lại
        $total = $this->calculateCartTotal();
        //$grandTotal = $total + 15000;

        // Cập nhật số lượng icon giỏ hàng
        if (Auth::check()) {
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
        } else {
            $cart = session()->get('cart', []);
            // trả về tổng số lượng sản phẩm trong giỏ hàng
            $cartCount = array_sum(array_column($cart, 'quantity'));
        }

        return response()->json([
            'status' => true,
            'total' => number_format($total, 0, ',', '.'),
            //'grandTotal' => number_format($grandTotal, 0, ',', '.'),
            'cart_count' => $cartCount, // Trả về để JS update icon
        ]);
    }
}
