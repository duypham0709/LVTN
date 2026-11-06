<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image'];

    // Mỗi hình ảnh thuộc về một sản phẩm
    // Ví dụ như là hình “apple_1.png” thuộc về sản phẩm “Táo đỏ”
    public function product()
    {
        return $this->belongsTo(Product::class);  // nhánh 2 chân
    }
}
