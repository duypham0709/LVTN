<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPurchaseAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_hides_purchase_controls_for_expired_product(): void
    {
        $this->travelTo('2026-11-01 12:00:00');
        $product = $this->createProduct([
            'expiry_date' => '2026-10-31',
        ]);

        $response = $this->get(route('product.detail', [
            'categorySlug' => $product->category->slug,
            'productSlug' => $product->slug,
        ]));

        $response->assertOk();
        $response->assertSee('Sản phẩm đã hết hạn');
        $response->assertDontSee('add-to-cart-btn');
        $response->assertDontSee('cart-plus-minus');
    }

    public function test_product_detail_hides_purchase_controls_when_expiry_date_is_today(): void
    {
        $this->travelTo('2026-11-01 12:00:00');
        $product = $this->createProduct([
            'expiry_date' => '2026-11-01',
        ]);

        $response = $this->get(route('product.detail', [
            'categorySlug' => $product->category->slug,
            'productSlug' => $product->slug,
        ]));

        $response->assertOk();
        $response->assertSee('Sản phẩm đã hết hạn');
        $response->assertDontSee('add-to-cart-btn');
        $response->assertDontSee('cart-plus-minus');
    }

    public function test_product_detail_hides_purchase_controls_when_product_is_out_of_stock(): void
    {
        $product = $this->createProduct([
            'stock' => 10,
            'status' => 'out_of_stock',
        ]);

        $response = $this->get(route('product.detail', [
            'categorySlug' => $product->category->slug,
            'productSlug' => $product->slug,
        ]));

        $response->assertOk();
        $response->assertSee('Tạm Hết Hàng');
        $response->assertDontSee('add-to-cart-btn');
        $response->assertDontSee('cart-plus-minus');
    }

    public function test_cart_displays_expired_product_message_and_blocks_checkout(): void
    {
        $this->travelTo('2026-11-01 12:00:00');
        $product = $this->createProduct([
            'expiry_date' => '2026-11-01',
        ]);

        $response = $this->withSession([
            'cart' => [
                $product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => 1,
                    'stock' => $product->stock,
                    'image' => 'uploads/products/default-product.png',
                ],
            ],
        ])->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Sản phẩm đã hết hạn');
        $response->assertSee('Vui lòng xóa sản phẩm lỗi để thanh toán');
        $response->assertSee('class="dec qtybutton"', false);
        $response->assertSee('class="inc qtybutton"', false);
        $this->assertSame(3, preg_match_all('/<button[^>]*\bdisabled\b[^>]*>/', $response->getContent()));
    }

    public function test_cart_disables_quantity_controls_when_cart_quantity_exceeds_stock(): void
    {
        $product = $this->createProduct([
            'stock' => 2,
        ]);

        $response = $this->withSession([
            'cart' => [
                $product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => 3,
                    'stock' => $product->stock,
                    'image' => 'uploads/products/default-product.png',
                ],
            ],
        ])->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Kho chỉ còn', false);
        $response->assertSee('class="dec qtybutton"', false);
        $response->assertSee('class="inc qtybutton"', false);
        $this->assertSame(3, preg_match_all('/<button[^>]*\bdisabled\b[^>]*>/', $response->getContent()));
    }

    public function test_cart_disables_quantity_controls_when_product_is_out_of_stock(): void
    {
        $product = $this->createProduct([
            'stock' => 0,
            'status' => 'out_of_stock',
        ]);

        $response = $this->withSession([
            'cart' => [
                $product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => 1,
                    'stock' => $product->stock,
                    'image' => 'uploads/products/default-product.png',
                ],
            ],
        ])->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Sản phẩm đã ngưng kinh', false);
        $response->assertSee('class="dec qtybutton"', false);
        $response->assertSee('class="inc qtybutton"', false);
        $this->assertSame(3, preg_match_all('/<button[^>]*\bdisabled\b[^>]*>/', $response->getContent()));
    }

    public function test_add_to_cart_rejects_expired_product_without_changing_session(): void
    {
        $this->travelTo('2026-11-01 12:00:00');
        $product = $this->createProduct([
            'expiry_date' => '2026-10-31',
        ]);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('message', 'Sản phẩm hiện không thể mua.');
        $response->assertSessionMissing('cart');
    }

    public function test_add_to_cart_rejects_product_expiring_today_without_changing_session(): void
    {
        $this->travelTo('2026-11-01 12:00:00');
        $product = $this->createProduct([
            'expiry_date' => '2026-11-01',
        ]);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('message', 'Sản phẩm hiện không thể mua.');
        $response->assertSessionMissing('cart');
    }

    public function test_add_to_cart_rejects_out_of_stock_product_without_changing_session(): void
    {
        $product = $this->createProduct([
            'stock' => 0,
        ]);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('message', 'Sản phẩm hiện không thể mua.');
        $response->assertSessionMissing('cart');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createProduct(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);

        return Product::create(array_merge([
            'name' => 'Rau muống',
            'slug' => 'rau-muong',
            'category_id' => $category->id,
            'price' => 10000,
            'stock' => 10,
            'status' => 'in_stock',
            'unit_quantity' => 1,
            'unit' => 'bó',
        ], $overrides));
    }
}
