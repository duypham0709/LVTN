<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

class CartService
{
    // cộng số lượng của tất cả sản phẩm trong giỏ
    public function count(): int
    {
        if (auth()->check()) {
            return CartItem::where('user_id', auth()->id())->sum('quantity');
        }

        $count = 0;
        foreach (session('cart', []) as $item) {
            $count += $item['quantity'] ?? 0;
        }

        return $count;
    }

    // trả về tất cả sản phẩm trong giỏ
    public function items(): array
    {
        if (auth()->check()) {
            $cartItems = CartItem::query()
                ->with('product.firstImage')
                ->where('user_id', auth()->id())
                ->get();
            $items = [];

            foreach ($cartItems as $cartItem) { // duyệt qua từng sản phẩm trong giỏ hàng của người dùng
                $items[] = $this->formatItem( // định dạng thông tin sản phẩm trong giỏ hàng
                    $cartItem->product,
                    $cartItem->product_id,
                    $cartItem->quantity,
                );
            }

            return $items;
        }

        // Lấy giỏ hàng từ session của khách
        $sessionCart = session('cart', []);
        $items = [];
        foreach ($sessionCart as $productId => $item) { // duyệt qua từng sản phẩm trong giỏ hàng của khách
            $productId = $item['product_id'] ?? $productId; // lấy product_id của sản phẩm hiện tại, nếu không có thì lấy key của sản phẩm trong giỏ hàng
            $product = Product::query() // lấy thông tin sản phẩm từ csdl
                ->with('firstImage')
                ->find($productId);

            $items[] = $this->formatItem(
                $product,
                $productId,
                $item['quantity'] ?? 0,
                $item,
            );
        }

        return $items;
    }

    // trả về số lượng của một sản phẩm trong giỏ
    public function quantityFor(int $productId): int
    {
        if (auth()->check()) {
            return CartItem::query()
                ->where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->value('quantity') ?? 0;
        }

        $cart = session('cart', []);

        return $cart[$productId]['quantity'] ?? 0;
    }

    // thêm hoặc cập nhật số lượng của một sản phẩm trong giỏ
    public function put(Product $product, int $quantity): void
    {
        // Cập nhật giỏ hàng đã đăng nhập
        if (auth()->check()) {
            $cartItem = CartItem::firstOrNew([
                'user_id' => auth()->id(),
                'product_id' => $product->getKey(),
            ]);
            $cartItem->quantity = $quantity;
            $cartItem->save();

            return;
        }

        // Cập nhật giỏ hàng trong session cho khách
        $cart = session('cart', []);
        if (isset($cart[$product->getKey()])) {
            $cart[$product->getKey()]['quantity'] = $quantity;
        } else {
            $cart[$product->getKey()] = [
                'product_id' => $product->getKey(),
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'stock' => $product->stock,
                'image' => $product->firstImage?->image ?? 'uploads/products/default-product.png',
            ];
        }

        session()->put('cart', $cart);
    }

    public function remove(int $productId): void
    {
        if (auth()->check()) {
            CartItem::query()
                ->where('user_id', auth()->id())
                ->where('product_id', $productId)
                ->delete();

            return;
        }

        $cart = session('cart', []);
        unset($cart[$productId]);
        session()->put('cart', $cart);
    }

    public function total(): float
    {
        $total = 0;
        foreach ($this->items() as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    // hợp nhất giỏ hàng của khách vào giỏ hàng của người dùng khi đăng nhập
    public function mergeGuestCartIntoUser(User $user): void
    {
        $sessionCart = session('cart', []); // Lấy giỏ hàng từ session của khách
        if ($sessionCart === []) {
            return;
        }

        foreach ($sessionCart as $productId => $item) { // duyệt qua từng sản phẩm trong giỏ hàng của khách
            $productId = $item['product_id'] ?? $productId; // lấy product_id của sản phẩm hiện tại
            $quantity = $item['quantity'] ?? 0; // lấy số lượng của sản phẩm hiện tại

            if ($quantity < 1 || ! Product::whereKey($productId)->exists()) {
                continue;
            }

            $cartItem = CartItem::firstOrNew([
                'user_id' => $user->getKey(),
                'product_id' => $productId,
            ]);
            $cartItem->quantity = $cartItem->quantity + $quantity;
            $cartItem->save();
        }

        session()->forget('cart');
    }

    // định dạng thông tin sản phẩm trong giỏ hàng để hiển thị ra giao diện người dùng
    private function formatItem(?Product $product, int $productId, int $quantity, array $fallback = []): array
    {
        // $product?->loadMissing('firstImage');

        return [
            'product_id' => $productId,
            'name' => $product?->name ?? $fallback['name'] ?? 'Sản phẩm không còn tồn tại',
            'price' => $product?->price ?? $fallback['price'] ?? 0,
            'quantity' => $quantity,
            'stock' => $product?->stock ?? $fallback['stock'] ?? 0,
            'image' => $product?->firstImage?->image
                ?? $fallback['image'] ?? 'uploads/products/default-product.png',
            'product' => $product,
        ];
    }
}
