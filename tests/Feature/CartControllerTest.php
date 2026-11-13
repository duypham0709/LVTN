<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_uses_legacy_session_shape_and_returns_cart_count(): void
    {
        $product = $this->createProduct();

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('cart_count', 2);
        $this->assertSame(2, session("cart.{$product->id}.quantity"));
        $this->assertSame($product->name, session("cart.{$product->id}.name"));
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_authenticated_cart_is_stored_in_legacy_cart_items_table(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();

        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('cart_count', 2);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_guest_cannot_add_more_than_available_stock(): void
    {
        $product = $this->createProduct(['stock' => 2]);
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Số lượng vượt quá tồn kho.');
        $this->assertSame(1, session("cart.{$product->id}.quantity"));
    }

    public function test_guest_can_update_and_remove_a_legacy_cart_item(): void
    {
        $product = $this->createProduct();
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $updateResponse = $this->postJson(route('cart.update'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('quantity', 3)
            ->assertJsonPath('total', '30.000')
            ->assertJsonPath('cart_count', 3);

        $removeResponse = $this->postJson('/cart/remove-cart', [
            'product_id' => $product->id,
        ]);

        $removeResponse->assertOk()
            ->assertJsonPath('total', '0')
            ->assertJsonPath('cart_count', 0);
        $this->assertSame([], session('cart', []));
    }

    public function test_legacy_guest_cart_is_rendered_without_changing_its_session_format(): void
    {
        $product = $this->createProduct();
        $sessionCart = [
            $product->id => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 2,
                'stock' => $product->stock,
                'image' => 'uploads/products/default-product.png',
            ],
        ];

        $response = $this->withSession(['cart' => $sessionCart])
            ->get(route('cart.index'));

        $response->assertOk()
            ->assertSee($product->name);
        $response->assertSee('href="'.route('login').'"', false);
        $this->assertSame($sessionCart, session('cart'));
    }

    public function test_guest_cart_is_merged_into_legacy_database_cart_when_user_logs_in(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();
        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $this->withSession([
            'cart' => [
                $product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => 2,
                    'stock' => $product->stock,
                    'image' => 'uploads/products/default-product.png',
                ],
            ],
        ]);

        Auth::login($user);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $this->assertNull(session('cart'));
    }

    public function test_mini_cart_renders_legacy_guest_items(): void
    {
        $product = $this->createProduct();
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $response = $this->getJson(route('cart.mini'));

        $response->assertOk()
            ->assertJsonPath('status', true);
        $this->assertStringContainsString($product->name, $response->json('html'));
        $this->assertStringContainsString('href="'.route('login').'"', $response->json('html'));
    }

    public function test_authenticated_cart_quantity_cannot_exceed_stock(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct(['stock' => 2]);
        $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $response = $this->postJson(route('cart.update'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('error', 'Quá số lượng tồn kho');
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_cart_quantity_cannot_change_after_product_expires(): void
    {
        $product = $this->createProduct();
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();
        $product->update(['expiry_date' => today()->subDay()]);

        $response = $this->postJson(route('cart.update'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('error', 'Sản phẩm hiện không thể thay đổi số lượng.');
        $this->assertSame(1, app(CartService::class)->quantityFor($product->id));
    }

    private function createUser(): User
    {
        $role = Role::create(['name' => 'customer']);

        return User::create([
            'name' => 'Cart User',
            'email' => 'cart-user@example.test',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
        ]);
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
