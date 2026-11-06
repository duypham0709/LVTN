<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantity'];

    // Mỗi cart cart-item thuộc về một user (vì có cột user_id)
    public function user()
    {
        return $this->belongsTo(User::class);   // nhánh 2 chân
    }

    // Mỗi cart-item thuộc về một product (vì có cột product_id)
    public function product()
    {
        return $this->belongsTo(Product::class);   // nhánh 2 chân
    }
}
