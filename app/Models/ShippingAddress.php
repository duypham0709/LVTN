<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingAddress extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'full_name', 'phone', 'address', 'city', 'default'];

    // Mỗi địa chỉ giao hàng thuộc về 1 người dùng cụ thể
    // Ví dụ như địa chỉ "123 Đường A, Quận B" thuộc về user "Nguyễn Văn A"
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
