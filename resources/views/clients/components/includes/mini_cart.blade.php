<div class="ltn__utilize-menu-head">
    <span class="ltn__utilize-menu-title">Giỏ Hàng</span>
    <button class="ltn__utilize-close">×</button>
</div>
@php
    $subtotal = 0;
    // Khởi tạo biến chặn thanh toán
    $blockCheckout = false;
@endphp
<div class="mini-cart-product-area ltn__scrollbar">
    @if (!empty($cartItems) && count($cartItems) > 0)

        @foreach ($cartItems as $item)
            @php
                $product = auth()->check() ? $item->product : \App\Models\Product::find($item['product_id']);
                $quantity = auth()->check() ? $item->quantity : $item['quantity'];

                $subtotal += $product->price * $quantity;

                $firstImage = $product->images->first();
                $imagePath = $firstImage
                    ? asset('storage/' . $firstImage->image)
                    : asset('storage/uploads/products/default-product.png');

                // Tính toán Hết hạn (Tính ngầm)
                $isExpired = false;
                if ($product->expiry_date) {
                    $isExpired = \Carbon\Carbon::parse($product->expiry_date)->isBefore(today()->addDay());
                }

                // Cập nhật biến chặn nếu gặp lỗi (Hết hàng OR Thiếu kho OR Hết hạn)
                if ($product->status == 'out_of_stock' || $product->stock < $quantity || $isExpired) {
                    $blockCheckout = true;
                }
            @endphp
            <div class="mini-cart-item clearfix">
                <div class="mini-cart-img">
                    <a href="javascript:void(0)"><img src="{{ $imagePath }}" alt="Image"></a>
                    <span class="mini-cart-item-delete" data-id="{{ $product->id }}">
                        <i class="icon-cancel"></i></span>
                </div>
                <div class="mini-cart-info">
                    <h6><a href="#">{{ $product->name }}</a></h6>
                    <span class="mini-cart-quantity">{{ $quantity }} x
                        {{ number_format($product->price, 0, ',', '.') }}</span>

                    {{-- Điều kiện: Hết kho HOẶC Hết hạn đều hiện chữ "Hết hàng" --}}
                    @if ($product->status == 'out_of_stock' || $isExpired)
                        <br><span style="color: red; font-size: 11px; font-weight:bold;">(Hết hàng)</span>
                    @elseif ($product->stock < $quantity)
                        <br><span style="color: red; font-size: 11px; font-weight:bold;">(Không đủ hàng)</span>
                    @endif
                </div>
            </div>
        @endforeach
    @else
    @endif

</div>
<div class="mini-cart-footer">
    <div class="mini-cart-sub-total">
        <h5>Tổng tiền: <span>{{ number_format($subtotal, 0, ',', '.') }}đ</span></h5>
    </div>
    <div class="btn-wrapper">
        <a href="{{ route('cart.index') }}" class="theme-btn-1 btn btn-effect-1">Xem Giỏ Hàng</a>
        <a href="cart.html" class="theme-btn-2 btn btn-effect-2">Thanh Toán</a>
    </div>
</div>
