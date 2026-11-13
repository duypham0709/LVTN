<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'total_price', 'status', 'shipping_address_id'];

    // Mỗi đơn hàng thuộc về một user
    public function user()
    {
        return $this->belongsTo(User::class);   // nhánh 2 chân
    }

    // Một đơn hàng có nhiều order item (sản phẩm trong đơn)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);   // nhánh chân gà
    }

    // Mỗi đơn hàng thuộc về một địa chỉ giao hàng
    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_address_id')->withTrashed();   // nhánh 2 chân
    }

    // Mỗi đơn hàng có một phương thức thanh toán payment riêng
    public function payment()
    {
        return $this->hasOne(Payment::class);   // nhánh 1-1
    }

    // Một đơn hàng có nhiều lịch sử trạng thái (pending, shipping, completed,...)
    public function orderStatusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);   // nhánh chân gà
    }
}
