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
                $product = $item['product'];
                $quantity = $item['quantity'];

                $subtotal += $item['price'] * $quantity;

                $imagePath = asset('storage/' . $item['image']);

                // Tính toán Hết hạn (Tính ngầm)
                $isExpired = $product?->expiry_date?->isBefore(today()->addDay()) ?? false;

                // Cập nhật biến chặn nếu gặp lỗi (Hết hàng OR Thiếu kho OR Hết hạn)
                $isOutOfStock = !$product || $product->status === 'out_of_stock';
                if ($isOutOfStock || $product->stock < $quantity || $isExpired) {
                    $blockCheckout = true;
                }
            @endphp
            <div class="mini-cart-item clearfix">
                <div class="mini-cart-img">
                    <a href="javascript:void(0)"><img src="{{ $imagePath }}" alt="Image"></a>
                    <span class="mini-cart-item-delete" data-id="{{ $item['product_id'] }}">
                        <i class="icon-cancel"></i></span>
                </div>
                <div class="mini-cart-info">
                    <h6><a href="#">{{ $item['name'] }}</a></h6>
                    <span class="mini-cart-quantity">{{ $quantity }} x
                        {{ number_format($item['price'], 0, ',', '.') }}</span>

                    {{-- Điều kiện: Hết kho HOẶC Hết hạn đều hiện chữ "Hết hàng" --}}
                    @if ($isOutOfStock || $isExpired)
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
        @if ($blockCheckout)
            <a class="theme-btn-2 btn btn-effect-2" disabled
                style="background-color: #ccc; cursor: not-allowed; border-color: #ccc;">Vui lòng xóa sản phẩm lỗi.
            </a>
        @else
            <a href="{{ auth()->check() ? 'cart.html' : route('login') }}"
                class="theme-btn-2 btn btn-effect-2">Thanh Toán</a>
        @endif
    </div>
</div>
