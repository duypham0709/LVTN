<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'slug', 'category_id', 'description', 'price', 'stock', 'status', 'unit', 'expiry_date'];

    protected $appends = ['average_rating', 'image_url'];

    // 1. Mỗi sản phẩm thuộc về 1 danh mục (Category)
    public function category()
    {
        return $this->belongsTo(Category::class);   // nhánh 2 chân
    }

    // 2. Một sản phẩm có nhiều hình ảnh (Products_Images)
    public function images()
    {
        return $this->hasMany(ProductImage::class);  // nhánh chân gà
    }

    // 3. Một sản phẩm có thể nằm trong nhiều giỏ hàng (Cart_Items)
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);   // nhánh chân gà
    }

    // 4. Một sản phẩm có thể xuất hiện trong nhiều đơn hàng (Order_Items)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);   // nhánh chân gà
    }

    public function firstImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('id', 'asc');
    }
}
