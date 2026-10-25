<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::with('products')->get();
        $products = Product::with('firstImage')
            ->where('status', 'in_stock')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString(); // giữ tham số trên url

        foreach ($products as $product) {
            $product->image_url = $product->firstImage?->image
                ? asset('storage/' . $product->firstImage->image)
                : asset('storage/uploads/products/default-product.png');
        }

        return view('clients.pages.products', compact('categories', 'products'));
    }

    public function filter(Request $request)
    {
        //$query = Product::query();
        $query = Product::with('firstImage')
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
                ? asset('storage/' . $product->firstImage->image)
                : asset('storage/uploads/products/default-product.png');
        }

        return response()->json([
            'products' => view('clients.components.products_grid', compact('products'))->render(),
        ]);
    }

    public function detail($slug)
    {
        $product = Product::with(['category', 'images'])->where('slug', $slug)->firstOrFail();

        // Lấy sản phẩm liên quan từ cùng một danh mục
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(6)
            ->get();

        return view('clients.pages.product_detail', compact('product', 'relatedProducts'));
    }
}
