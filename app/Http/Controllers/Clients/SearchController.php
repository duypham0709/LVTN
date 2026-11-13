<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|min:2|max:50',
        ], [
            'keyword.required' => 'Vui lòng nhập từ khóa tìm kiếm.',
            'keyword.min' => 'Từ khóa phải có ít nhất 2 ký tự.',
            'keyword.max' => 'Từ khóa quá dài.',
        ]);

        $keyword = $request->input('keyword');

        // tìm kiếm sản phẩm theo từ khóa
        $products = Product::query()
            ->where('name', 'LIKE', '%' . $keyword . '%')
            ->whereIn('status', ['in_stock', 'out_of_stock'])
            ->with(['firstImage', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(8)
            ->withQueryString();

        foreach ($products as $product) {
            $product->image_url = $product->firstImage?->image
                ? asset('storage/' . $product->firstImage->image)
                : asset('storage/uploads/products/default-product.png');
        }

        return view('clients.pages.products_search', compact('products', 'keyword'));
    }
}
