<?php

namespace Tests\Feature\Clients;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_result_links_to_product_with_category_and_product_slugs(): void
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
            'stock' => 5,
            'status' => 'in_stock',
            'unit_quantity' => 1,
            'unit' => 'bó',
        ]);

        $response = $this->get(route('search', ['keyword' => 'Rau muống']));

        $response->assertOk()
            ->assertSee($product->name)
            ->assertSee(route('product.detail', [
                'categorySlug' => $category->slug,
                'productSlug' => $product->slug,
            ]), false);
    }

    public function test_search_does_not_return_products_that_only_match_in_the_description(): void
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
        ]);
        $matchedProduct = Product::create([
            'name' => 'Cà chua',
            'slug' => 'ca-chua',
            'category_id' => $category->id,
            'description' => 'Cà chua tươi.',
            'price' => 10000,
            'stock' => 5,
            'status' => 'in_stock',
            'unit_quantity' => 1,
            'unit' => 'kg',
        ]);
        $descriptionOnlyMatch = Product::create([
            'name' => 'Dưa hấu',
            'slug' => 'dua-hau',
            'category_id' => $category->id,
            'description' => 'Ăn kèm cà chua.',
            'price' => 20000,
            'stock' => 5,
            'status' => 'in_stock',
            'unit_quantity' => 1,
            'unit' => 'kg',
        ]);

        $response = $this->get(route('search', ['keyword' => 'cà']));

        $response->assertOk()
            ->assertSee($matchedProduct->name)
            ->assertDontSee($descriptionOnlyMatch->name);
    }

    public function test_homepage_search_form_targets_search_route_with_keyword(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('action="'.route('search').'"', false)
            ->assertSee('name="keyword"', false);
    }
}
