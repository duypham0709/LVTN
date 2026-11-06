<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function showFormAddProduct()
    {
        $categories = Category::all();

        return view('admin.pages.product-add', compact('categories'));
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'qty' => 'required|numeric|gt:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'required|date|after_or_equal:' . now()->addDays(7)->toDateString(),
            'images' => 'required',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'name.unique' => 'Tên sản phẩm này đã tồn tại, vui lòng chọn tên khác.',
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.max' => 'Tên sản phẩm không được quá 255 ký tự.',

            'category_id.required' => 'Vui lòng chọn danh mục sản phẩm.',

            'price.required' => 'Vui lòng nhập giá tiền.',
            'price.numeric' => 'Giá tiền phải là dạng số.',
            'price.min' => 'Giá tiền không được nhỏ hơn 0.',

            'stock.required' => 'Vui lòng nhập số lượng tồn kho.',
            'stock.integer' => 'Số lượng phải là số nguyên.',
            'stock.min' => 'Số lượng không được nhỏ hơn 0.',

            'qty.required' => 'Vui lòng nhập khối lượng.',
            'qty.gt' => 'Khối lượng phải lớn hơn 0.',

            'unit.required' => 'Vui lòng chọn đơn vị tính.',

            'images.required' => 'Vui lòng chọn ít nhất một hình ảnh.',
            'images.*.image' => 'File tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh phải có đuôi: jpeg, png, jpg, gif, svg.',
            'images.*.max' => 'Dung lượng ảnh không được quá 2MB.',

            'expiry_date.required' => 'Vui lòng nhập hạn sử dụng.',
            'expiry_date.date' => 'Hạn sử dụng không hợp lệ.',
            'expiry_date.after_or_equal' => 'Hạn sử dụng ít nhất là 7 ngày kế tiếp.',
        ]);

        $slug = Str::slug($request->name) . '-' . time();

        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock ?? 0,
            'qty' => $request->qty,
            'unit' => $request->unit ?? 'kg',
            'status' => 'in_stock',
            'expiry_date' => $request->expiry_date, // <--- LƯU HẠN SỬ DỤNG
        ]);

        // xử lý tải hình ảnh

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = 'uploads/products/' . $imageName;

                $resizedImage = Image::fromUpload($image)
                    ->scale(600, 600)
                    ->toBytes(); // tiết kiệm dung lượng ảnh

                Storage::disk('public')->put($path, $resizedImage);

                ProductImage::create([ // quan hệ 1 nhiều
                    'product_id' => $product->id, // khóa ngoại để biết tấm hình này thuộc sản phẩm nào
                    'image' => $path,
                ]);
            }
        }

        return redirect()->route('admin.product.add')->with('success', 'Sản phẩm đã được thêm thành công!');
    }

    public function index(Request $request)
    {
        // khởi tạo query để lấy sản phẩm
        $query = Product::with(['category', 'firstImage'])->orderBy('id', 'desc');

        // kiểm tra nếu có lọc danh mục
        if ($request->has('category_id') && $request->category_id != null) {
            $query->where('category_id', $request->category_id);
        }

        // lọc trạng thái
        if ($request->has('status') && $request->status != null) {
            $query->where('status', $request->status);
        }

        // lấy dữ liệu
        $products = $query->get();

        foreach ($products as $product) {
            if ($product->firstImage) {
                $product->image_url = asset('storage/' . $product->firstImage->image);
            } else {
                $product->image_url = asset('storage/uploads/products/default-product.png');
            }
        }

        $categories = Category::all();

        return view('admin.pages.products', compact('products', 'categories'));
    }

    public function updateProduct(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'qty' => 'required|numeric|gt:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:in_stock,out_of_stock',
            'expiry_date' => 'required|date|after_or_equal:' . now()->addDays(7)->toDateString(),
        ], [
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.max' => 'Tên sản phẩm không được quá 255 ký tự.',

            'category_id.required' => 'Vui lòng chọn danh mục sản phẩm.',
            'price.required' => 'Vui lòng nhập giá tiền.',
            'price.numeric' => 'Giá tiền phải là dạng số.',
            'price.min' => 'Giá tiền không được nhỏ hơn 0.',

            'stock.required' => 'Vui lòng nhập số lượng tồn kho.',
            'stock.integer' => 'Số lượng phải là số nguyên.',
            'stock.min' => 'Số lượng không được nhỏ hơn 0.',
            'qty.required' => 'Vui lòng nhập khối lượng.',
            'qty.numeric' => 'Khối lượng phải là dạng số.',
            'qty.gt' => 'Khối lượng phải lớn hơn 0.',
            'expiry_date.required' => 'Vui lòng nhập ngày hết hạn.',
            'expiry_date.date' => 'Ngày hết hạn không hợp lệ.',
            'expiry_date.after_or_equal' => 'Hạn sử dụng ít nhất là 7 ngày kế tiếp.',
        ]);

        $product = Product::findOrFail($request->id);

        $newStatus = $request->status; // lấy cái admin đang chọn trong ô select

        // Chỉ can thiệp tự động trong 1 trường hợp duy nhất:
        // Nếu kho thực sự hết hàng (<=0) thì dù Admin chọn "Còn hàng" cũng phải ép về "Hết hàng"
        if ($request->stock <= 0) {
            $newStatus = 'out_of_stock';
        }

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock ?? 0,
            'unit_quantity' => $request->qty,
            'unit' => $request->unit ?? 'kg',
            'status' => $newStatus,
            'expiry_date' => $request->expiry_date,
        ]);

        // nếu có ảnh mới tải lên
        if ($request->hasFile('images')) {

            $oldImages = ProductImage::where('product_id', $product->id)->get();
            foreach ($oldImages as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
            }

            ProductImage::where('product_id', $product->id)->delete();

            foreach ($request->file('images') as $image) {

                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = 'uploads/products/' . $imageName;
                $resizedImage = Image::make($image)->resize(600, 600)->encode();
                Storage::disk('public')->put($path, $resizedImage);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                ]);
            }
        }

        // trả về dữ liệu mới nhất cho bên client
        $product->load('category', 'images');
        $firstImage = $product->images->first();
        $imageUrl = $firstImage ? asset('storage/' . $firstImage->image) : asset('storage/uploads/products/default-product.png');

        // --- TÍNH TOÁN MÀU SẮC ĐỂ TRẢ VỀ CHO AJAX ---
        // $rowStyle = '';
        $expiryBadge = '';

        if ($product->expiry_date) {
            $expiry = Carbon::parse($product->expiry_date);
            $now = Carbon::now();

            if ($now->gt($expiry)) {
                // $rowStyle = 'background-color: #ffe6e6;'; // Đỏ (Hết hạn)
                $expiryBadge = '<br><span class="badge badge-danger">ĐÃ HẾT HẠN</span>';
            } elseif ($now->diffInDays($expiry) < 3) {
                // $rowStyle = 'background-color: #fff3cd;'; // Vàng (Sắp hết)
                $expiryBadge = '<br><span class="badge badge-warning">CẬN DATE</span>';
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật sản phẩm thành công.',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'category_name' => $product->category->name, // Sửa lại key cho khớp với JS bên dưới
                'description' => $product->description,
                'price' => $product->price,
                'price_formatted' => number_format($product->price, 0, ',', '.'),
                'stock' => $product->stock,
                'unit_quantity' => $product->unit_quantity,
                'unit_label' => $product->unit_label,
                'unit' => $product->unit,
                'status' => $product->status == 'in_stock' ? 'Còn hàng' : 'Hết hàng',
                'image_url' => $imageUrl,

                // Dữ liệu mới cho Hạn sử dụng
                'expiry_date' => $product->expiry_date,
                'expiry_date_formatted' => $product->expiry_date ? date('d/m/Y', strtotime($product->expiry_date)) : 'N/A',
                // 'row_style' => $rowStyle, // Màu nền
                'expiry_badge' => $expiryBadge, // Nhãn cảnh báo
            ],
        ]);
    }

    public function deleteProduct(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->id);

        if ($product->status == 'in_stock') {
            return response()->json([
                'status' => false,
                'message' => 'Không thể xóa! Sản phẩm đang trạng thái "Còn hàng". Vui lòng chuyển sang "Hết hàng" trước khi xóa.',
            ]);
        }

        // kiểm tra nếu sản phẩm đã có trong đơn hàng thì không cho xóa
        if ($product->orderItems()->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể xóa! Sản phẩm này đã có trong đơn hàng của khách. Vui lòng chỉ chuyển sang trạng thái "Ngừng kinh doanh".',
            ]);
        }

        $images = ProductImage::where('product_id', $product->id)->get();
        foreach ($images as $image) {
            if (Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }
        }

        ProductImage::where('product_id', $product->id)->delete();

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sản phẩm đã được xóa thành công.',
        ]);
    }
}
