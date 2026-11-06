<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_links_to_categories_and_products_by_slug(): void
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);
        $product = Product::create([
            'name' => 'Rau muống',
            'slug' => 'rau-muong',
            'category_id' => $category->id,
            'price' => 10000,
            'unit_quantity' => 1,
            'unit' => 'bó',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee(route('products.byCategory', $category->slug), false);
        $response->assertSee(route('product.detail', [
            'categorySlug' => $category->slug,
            'productSlug' => $product->slug,
        ]), false);
    }

    public function test_homepage_category_count_includes_only_in_stock_products(): void
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);
        Product::create([
            'name' => 'Rau muống',
            'slug' => 'rau-muong',
            'category_id' => $category->id,
            'price' => 10000,
            'status' => 'in_stock',
            'unit_quantity' => 1,
            'unit' => 'bó',
        ]);
        Product::create([
            'name' => 'Bí đỏ',
            'slug' => 'bi-do',
            'category_id' => $category->id,
            'price' => 15000,
            'stock' => 0,
            'status' => 'out_of_stock',
            'unit_quantity' => 1,
            'unit' => 'kg',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Rau củ');
        $response->assertSee('(1 sản phẩm)');
    }

    public function test_category_slug_displays_products_in_that_category(): void
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);
        $otherCategory = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
        ]);
        Product::create([
            'name' => 'Rau muống',
            'slug' => 'rau-muong',
            'category_id' => $category->id,
            'price' => 10000,
            'unit_quantity' => 1,
            'unit' => 'bó',
        ]);
        Product::create([
            'name' => 'Dưa hấu',
            'slug' => 'dua-hau',
            'category_id' => $otherCategory->id,
            'price' => 20000,
            'unit_quantity' => 1,
            'unit' => 'kg',
        ]);
        Product::create([
            'name' => 'Bí đỏ',
            'slug' => 'bi-do',
            'category_id' => $category->id,
            'price' => 15000,
            'stock' => 0,
            'status' => 'out_of_stock',
            'unit_quantity' => 1,
            'unit' => 'kg',
        ]);

        $response = $this->get(route('products.byCategory', $category->slug));

        $response->assertOk();
        $response->assertSee('Rau muống');
        $response->assertDontSee('Bí đỏ');
        $response->assertDontSee('Dưa hấu');
        $response->assertSee('data-filter-url="'.route('products.filter').'"', false);
    }

    public function test_filter_endpoint_returns_products_for_a_category_and_sort_order(): void
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);
        $otherCategory = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
        ]);
        Product::create([
            'name' => 'Rau muống',
            'slug' => 'rau-muong',
            'category_id' => $category->id,
            'price' => 10000,
            'unit_quantity' => 1,
            'unit' => 'bó',
        ]);
        Product::create([
            'name' => 'Bí đỏ',
            'slug' => 'bi-do',
            'category_id' => $category->id,
            'price' => 20000,
            'stock' => 0,
            'status' => 'out_of_stock',
            'unit_quantity' => 1,
            'unit' => 'kg',
        ]);
        Product::create([
            'name' => 'Dưa hấu',
            'slug' => 'dua-hau',
            'category_id' => $otherCategory->id,
            'price' => 30000,
            'unit_quantity' => 1,
            'unit' => 'kg',
        ]);

        $response = $this->getJson(route('products.filter', [
            'category_id' => $category->id,
            'sort_by' => 'price_asc',
            'page' => 1,
        ]));

        $response->assertOk();
        $response->assertJsonStructure(['products', 'pagination']);
        $response->assertJsonPaths([
            'products' => fn (string $html): bool => str_contains($html, 'Rau muống')
                && ! str_contains($html, 'Bí đỏ')
                && ! str_contains($html, 'Dưa hấu'),
        ]);
    }

    public function test_legacy_category_id_url_redirects_to_the_category_slug_url(): void
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);

        $response = $this->get(route('products.byCategory', $category->id));

        $response->assertRedirect(route('products.byCategory', $category->slug));
        $response->assertStatus(301);
    }

    public function test_product_detail_uses_category_and_product_slugs(): void
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);
        $product = Product::create([
            'name' => 'Rau muống',
            'slug' => 'rau-muong',
            'category_id' => $category->id,
            'price' => 10000,
            'unit_quantity' => 1,
            'unit' => 'bó',
        ]);

        $response = $this->get(route('product.detail', [
            'categorySlug' => $category->slug,
            'productSlug' => $product->slug,
        ]));

        $response->assertOk();
        $response->assertSee('Rau muống');
    }

    public function test_product_detail_returns_not_found_for_a_different_category_slug(): void
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);
        $otherCategory = Category::create([
            'name' => 'Đồ khô',
            'slug' => 'do-kho',
        ]);
        Product::create([
            'name' => 'Rau muống',
            'slug' => 'rau-muong',
            'category_id' => $category->id,
            'price' => 10000,
            'unit_quantity' => 1,
            'unit' => 'bó',
        ]);

        $response = $this->get(route('product.detail', [
            'categorySlug' => $otherCategory->slug,
            'productSlug' => 'rau-muong',
        ]));

        $response->assertNotFound();
    }

    public function test_legacy_product_url_redirects_to_the_nested_slug_url(): void
    {
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
        ]);
        $product = Product::create([
            'name' => 'Rau muống',
            'slug' => 'rau-muong',
            'category_id' => $category->id,
            'price' => 10000,
            'unit_quantity' => 1,
            'unit' => 'bó',
        ]);

        $response = $this->get(route('product.detail.legacy', $product->slug));

        $response->assertRedirect(route('product.detail', [
            'categorySlug' => $category->slug,
            'productSlug' => $product->slug,
        ]));
        $response->assertStatus(301);
    }
}
