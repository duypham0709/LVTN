<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductExpiryValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_product_rejects_an_expiry_date_in_the_past(): void
    {
        $this->travelTo(Carbon::parse('2026-11-01'));
        $this->withoutMiddleware();
        $category = $this->createCategory();

        $response = $this->postJson(route('admin.product.add'), [
                'name' => 'Product with past expiry',
                'category_id' => $category->id,
                'price' => 10000,
                'stock' => 5,
                'qty' => 1,
                'unit' => 'kg',
                'expiry_date' => '2026-10-31',
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.expiry_date.0', 'Hạn sử dụng không được là ngày trong quá khứ.');
    }

    public function test_update_product_returns_expiry_validation_error_for_a_past_date(): void
    {
        $this->travelTo(Carbon::parse('2026-11-01'));
        $this->withoutMiddleware();
        $category = $this->createCategory();
        $product = Product::create([
            'name' => 'Product to update',
            'slug' => 'product-to-update',
            'category_id' => $category->id,
            'price' => 10000,
            'stock' => 5,
            'status' => 'in_stock',
            'unit' => 'kg',
            'expiry_date' => '2026-11-10',
        ]);

        $response = $this->postJson('/admin/product/update', [
            'id' => $product->id,
            'name' => $product->name,
            'category_id' => $category->id,
            'price' => 10000,
            'stock' => 5,
            'qty' => 1,
            'unit' => 'kg',
            'status' => 'in_stock',
            'expiry_date' => '2026-10-31',
        ]);

        $response->dump()
            ->assertUnprocessable()
            ->assertJsonPath('errors.expiry_date.0', 'Ngày hết hạn không được là ngày trong quá khứ.');
    }

    public function test_update_product_accepts_expiry_date_equal_to_today(): void
    {
        $this->travelTo(Carbon::parse('2026-11-01'));
        $this->withoutMiddleware();
        $category = $this->createCategory();
        $product = Product::create([
            'name' => 'Product to update today',
            'slug' => 'product-to-update-today',
            'category_id' => $category->id,
            'price' => 10000,
            'stock' => 5,
            'status' => 'in_stock',
            'unit' => 'kg',
            'expiry_date' => '2026-11-10',
        ]);

        $response = $this->postJson('/admin/product/update', [
            'id' => $product->id,
            'name' => $product->name,
            'category_id' => $category->id,
            'price' => 10000,
            'stock' => 5,
            'qty' => 1,
            'unit' => 'kg',
            'status' => 'in_stock',
            'expiry_date' => '2026-11-01',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'Test category',
            'slug' => 'test-category',
        ]);
    }
}
