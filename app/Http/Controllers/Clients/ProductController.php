<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::with('products')->get();
        $products = Product::with(['firstImage', 'category'])
            ->where('status', 'in_stock')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString(); // giữ tham số trên url

        foreach ($products as $product) {
            $product->image_url = $product->firstImage?->image
                ? asset('storage/'.$product->firstImage->image)
                : asset('storage/uploads/products/default-product.png');
        }

        return view('clients.pages.products', compact('categories', 'products'));
    }

    // Lọc sản phẩm
    public function filter(Request $request)
    {
        // Lấy danh mục để hiển thị trên trang
        $query = Product::with(['firstImage', 'category'])
            ->where('status', 'in_stock');
        // Lọc theo danh mục nếu tồn tại

        // if ($request->has('category_id') && $request->category_id != '') {
        //     $query->where('category_id', $request->category_id);
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->has('sort_by')) {
            // switch ($request->sort_by) {
            //     case 'price_asc':
            //         $query->orderBy('price', 'asc');
            //         break;
            //     case 'price_desc':
            //         $query->orderBy('price', 'desc');
            //         break;
            //     case 'latest':
            //         $query->orderBy('created_at', 'desc');
            //         break;
            //     default:
            //         $query->orderBy('id', 'desc');
            //         break;
            switch ($request->input('sort_by', 'default')) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'latest':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderByDesc('id');
                    break;
            }
        }
        $products = $query->paginate(9)->withQueryString();

        foreach ($products as $product) {
            $product->image_url = $product->firstImage?->image
                ? asset('storage/'.$product->firstImage->image)
                : asset('storage/uploads/products/default-product.png');
        }

        return response()->json([
            'products' => view('clients.components.products_grid', compact('products'))->render(),
            'pagination' => $products->links('clients.components.pagination.pagination_custom')->toHtml(),
        ]);
    }

    // Lọc sản phẩm theo danh mục
    public function filterByCategory(string $slug): View
    {
        $categories = Category::withCount('products')->get();
        // Lấy danh mục được chọn dựa trên slug
        $selectedCategory = Category::where('slug', $slug)->firstOrFail();

        $products = Product::with(['firstImage', 'category'])
            ->where('category_id', $selectedCategory->id)
            ->whereIn('status', ['in_stock'])
            ->orderByDesc('id')
            ->paginate(9);

        /** @var Product $product */
        foreach ($products as $product) {
            $product->image_url = $product->firstImage?->image
                ? asset('storage/'.$product->firstImage->image)
                : asset('storage/uploads/products/default-product.png');
        }

        return view('clients.pages.products', compact('categories', 'products', 'selectedCategory'));
    }

    // Chi tiết sản phẩm
    public function detail(string $categorySlug, string $productSlug): View
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $product = Product::with(['category', 'images'])
            ->where('category_id', $category->id)
            ->where('slug', $productSlug)
            ->firstOrFail();

        // Lấy hình ảnh đầu tiên hợp lệ của sản phẩm hoặc sử dụng hình ảnh mặc định nếu không có
        $fallbackImage = $product->images->first(
            fn ($image) => filled($image->image) && Storage::disk('public')->exists($image->image),
        );
        $product->image_url = $fallbackImage
            ? asset('storage/'.$fallbackImage->image)
            : asset('storage/uploads/products/default-product.png');

        // Lấy sản phẩm liên quan từ cùng một danh mục
        $relatedProducts = Product::with(['firstImage', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(6)
            ->get();

        foreach ($relatedProducts as $related) {
            $related->image_url = $related->firstImage
                ? asset('storage/'.$related->firstImage->image)
                : asset('storage/uploads/products/default-product.png');
        }

        return view('clients.pages.product_detail', compact('product', 'relatedProducts'));
    }

    // public function legacyDetail(string $slug): RedirectResponse
    // {
    //     $product = Product::with('category')->where('slug', $slug)->firstOrFail();

    //     return redirect()->route('product.detail', [
    //         'categorySlug' => $product->category->slug,
    //         'productSlug' => $product->slug,
    //     ], 301);
    // }
}
