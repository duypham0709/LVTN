<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    // Mỗi order item thuộc về một đơn hàng
    public function order()
    {
        return $this->belongsTo(Order::class);   // nhánh 2 chân
    }

    // OrderItem là chi tiết đơn hàng, còn Order là đơn hàng tổng.

    // Một đơn hàng có thể gồm nhiều dòng sản phẩm (nhiều order item).

    // Nhưng mỗi order item chỉ nằm trong đúng 1 đơn hàng thôi.

    // Mỗi order item thuộc về một sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class);   // nhánh 2 chân
    }

    // Mỗi dòng trong chi tiết đơn hàng (order item) là một sản phẩm cụ thể.

    // Do đó, order item cần biết “tôi đang nói đến sản phẩm nào” → nên có product_id.

    // Vì vậy, mỗi order item thuộc về đúng 1 sản phẩm.
}
