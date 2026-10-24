<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = (Category::with('products'))->get();
        foreach ($categories as $item => $category) {
            foreach ($category->products as $product) {
                $product->image_url = $product->firstImage?->image
                    ? asset('storage/' . $product->firstImage->image)
                    : asset('storage/uploads/products/default-product.png');
            }
        }


        $bestSellingProducts = Product::with('firstImage')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->select('products.*') // Chọn tất cả cột từ bảng products
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            // Đơn giản hóa Group By, chỉ cần group by ID của product
            ->groupBy(
                'products.id',
                'products.name',
                'products.slug',
                'products.category_id',
                'products.description',
                'products.price',
                'products.stock',
                'products.status',
                'products.unit',
                'products.expiry_date',
                'products.created_at',
                'products.updated_at'
            )
            ->orderByDesc('total_sold')
            ->limit(8)
            ->get();


        return view('clients.pages.home', compact('categories', 'bestSellingProducts'));
    }
}
