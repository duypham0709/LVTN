<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeCartAfterLogin
{
    public function __construct(private readonly CartService $cartService) {}

    public function handle(Login $event): void
    {
        $this->cartService->mergeGuestCartIntoUser($event->user);
    }

    /*
    Previous session-to-cart_items merge implementation:
    $user = $event->user;
    $seessionCart = Session::get('cart', []);

    if (! empty($seessionCart)) {
        foreach ($seessionCart as $productId => $cartItem) {
            $existingCartItem = CartItem::where('user_id', $user->id)
                ->where('product_id', $productId)->first();

            if ($existingCartItem) {
                $existingCartItem->quantity += $cartItem['quantity'];
                $existingCartItem->save();
            } else {
                CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $cartItem['quantity'],
                ]);
            }
        }
    }

    Session::forget('cart');
    */
}
