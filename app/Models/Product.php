<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'price',
        'stock',
        'status',
        'unit_quantity',
        'unit',
        'expiry_date',
    ];

    protected $appends = ['average_rating', 'image_url'];

    protected $casts = [
        'unit_quantity' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function getUnitLabelAttribute(): string
    {
        $value = (float) $this->unit_quantity;

        $label = floor($value) == $value
            ? (string) (int) $value
            : rtrim(rtrim((string) $value, '0'), '.');

        return $label.' '.$this->unit;
    }

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

    // 5. Một sản phẩm có thể có nhiều đánh giá từ người dùng (Reviews)
    public function reviews()
    {
        return $this->hasMany(Review::class);   // nhánh chân gà
    }

    // 6. Một sản phẩm có thể thuộc về nhiều công thức nấu ăn (Recipes) thông qua bảng trung gian Product_Recipe
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'product_recipes')
            ->withPivot('quantity');
    }

    public function firstImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('id', 'asc');
    }

    // Accessor tự động lấy ảnh đầu tiên hoặc ảnh default
    public function getImageUrlAttribute()
    {
        // Kiểm tra relation firstImage đã được load chưa
        if ($this->relationLoaded('firstImage') && $this->firstImage) {
            return asset('storage/'.$this->firstImage->image);
        }

        // Nếu chưa load relation hoặc không có ảnh, check lại trong DB (phòng hờ)
        // Lưu ý: Nếu bạn luôn dùng with('firstImage') ở controller thì đoạn dưới này ít khi chạy, tốt cho performance
        $image = $this->firstImage()->first();

        return $image
            ? asset('storage/'.$image->image)
            : asset('storage/uploads/products/default-product.png');
    }
}
