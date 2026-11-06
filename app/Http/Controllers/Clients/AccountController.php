<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $addresses = ShippingAddress::where('user_id', Auth::id())->get();
        return view('clients.pages.account', compact('user', 'addresses'));
    }

    public function addAddress(Request $request)
    {

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => ['required', 'digits:10', 'regex:/(0)[0-9]{9}/'],
            'address' => 'required|string|min:10',
            'city' => 'required|string|min:2',
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên người nhận.',
            'phone.required'     => 'Vui lòng nhập số điện thoại.',
            'phone.digits'       => 'Số điện thoại phải có đúng 10 chữ số.',
            'phone.regex'        => 'Số điện thoại không đúng định dạng (VD: 0912345678).',
            'address.required'   => 'Địa chỉ không được để trống.',
            'address.min'        => 'Địa chỉ quá ngắn, vui lòng nhập chi tiết hơn.',
            'city.required'      => 'Vui lòng chọn Tỉnh/Thành phố.',
        ]);

        if ($request->has('default')) {
            ShippingAddress::where('user_id', Auth::id())->update(['default' => 0]);
        }

        ShippingAddress::create([
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'default' => $request->has('default') ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Địa chỉ đã được thêm thành công!',
        ]);
    }

    // Đặt địa chỉ mặc định
    public function updatePrimaryAddress($id)
    {
        $address = ShippingAddress::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Đặt tất cả địa chỉ của người dùng là không mặc định
        ShippingAddress::where('user_id', Auth::id())->update(['default' => 0]);

        // Đặt địa chỉ được chọn là mặc định
        $address->default = 1;
        $address->save();

        return response()->json([
            'success' => true,
            'message' => 'Địa chỉ mặc định đã được cập nhật thành công!',
        ]);
    }

    public function deleteAddress($id)
    {
        $address = ShippingAddress::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Nếu là địa chỉ mặc định, không cho xóa
        if ($address->default) {
            return response()->json([
                'message' => 'Không thể xóa địa chỉ mặc định. Vui lòng chọn địa chỉ khác làm mặc định trước.',
            ], 422);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Địa chỉ đã được xóa thành công!',
        ]);
    }
}
