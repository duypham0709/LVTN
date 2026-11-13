<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function addToCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        // thêm số lượng mới vào số lượng hiện tại của sản phẩm trong giỏ hàng
        $newQuantity = $this->cartService->quantityFor($product->getKey()) + $validated['quantity'];

        if (! $this->isAvailableForPurchase($product)) {
            return response()->json(['message' => 'Sản phẩm hiện không thể mua.'], 400);
        }

        if ($newQuantity > $product->stock) {
            return response()->json(['message' => 'Số lượng vượt quá tồn kho.'], 400);
        }

        $this->cartService->put($product, $newQuantity);

        return response()->json([
            'message' => true,
            'cart_count' => $this->cartService->count(),
        ]);
    }

    public function loadMiniCart(): JsonResponse
    {
        $cartItems = $this->cartService->items();

        return response()->json([
            'status' => true,
            'html' => view('clients.components.includes.mini_cart', compact('cartItems'))->render(),
        ]);
    }

    public function removeFromMiniCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartService->remove((int) $validated['product_id']);

        return response()->json([
            'status' => true,
            'cart_count' => $this->cartService->count(),
        ]);
    }

    public function viewCart(): View
    {
        $cartItems = $this->cartService->items();

        return view('clients.pages.cart', compact('cartItems'));
    }

    public function updateCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $currentQuantity = $this->cartService->quantityFor($product->getKey());

        // kiểm tra số lượng hiện tại của sản phẩm trong giỏ hàng, nếu bằng 0 thì trả về lỗi
        if ($currentQuantity === 0) {
            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        }

        // kiểm tra xem sản phẩm có thể mua được hay không, nếu không thì trả về lỗi
        if (! $this->isAvailableForPurchase($product) || $currentQuantity > $product->stock) {
            return response()->json(['error' => 'Sản phẩm hiện không thể thay đổi số lượng.'], 400);
        }

        // kiểm tra xem số lượng mới có vượt quá tồn kho hay không, nếu có thì trả về lỗi
        if ($validated['quantity'] > $product->stock) {
            return response()->json(['error' => 'Quá số lượng tồn kho'], 400);
        }

        $this->cartService->put($product, $validated['quantity']);
        $subtotal = $validated['quantity'] * $product->price;

        return response()->json([
            'status' => true,
            'quantity' => $validated['quantity'],
            'subtotal' => number_format($subtotal, 0, ',', '.'),
            'total' => number_format($this->cartService->total(), 0, ',', '.'),
            'cart_count' => $this->cartService->count(),
        ]);
    }

    public function removeCartItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
        ]);

        $this->cartService->remove((int) $validated['product_id']);

        return response()->json([
            'status' => true,
            'total' => number_format($this->cartService->total(), 0, ',', '.'),
            'cart_count' => $this->cartService->count(),
        ]);
    }

    // kiểm tra xem sản phẩm có thể mua được hay không, dựa trên các điều kiện như tồn kho, trạng thái và ngày hết hạn
    private function isAvailableForPurchase(Product $product): bool
    {
        return $product->stock > 0
            && $product->status === 'in_stock'
            && ! ($product->expiry_date?->isBefore(today()->addDay()) ?? false);
    }
}
