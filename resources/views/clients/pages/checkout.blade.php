@extends('layouts.client')

@section('title', 'Đặt hàng')
@section('breadcrumb', 'Đặt hàng')

@section('content')
    <div class="ltn__checkout-area mb-105">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__checkout-inner">

                        <div class="ltn__checkout-single-content mt-50">
                            <h4 class="title-2">Chi tiết đặt hàng</h4>

                            <div class="select-address">
                                <div>
                                    <h6>Chọn địa chỉ khác</h6>
                                </div>

                                <div>
                                    <select name="address_id" id="list_address" class="input-item">
                                        @foreach ($addresses as $address)
                                            <option value="{{ $address->id }}" {{ $address->default ? 'selected' : '' }}>
                                                {{ $address->full_name }} - {{ $address->address }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <a href="{{ route('account') }}" class="btn theme-btn-1 text-uppercase">Thêm địa chỉ
                                        mới</a>
                                </div>

                            </div>

                            <div class="ltn__checkout-single-content-info">

                                <h6>Thông tin cá nhân</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-item input-item-name ltn__custom-icon">
                                            <input type="text" name="ltn__name" placeholder="Họ và tên"
                                                value="{{ $defaultAddress->full_name }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-item input-item-phone ltn__custom-icon">
                                            <input type="text" name="ltn__phone" placeholder="Số điện thoại"
                                                value="{{ $defaultAddress->phone }}" readonly>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <h6>Địa chỉ</h6>
                                        <div class="input-item">
                                            <input type="text" name="ltn__address" placeholder="Số nhà và tên đường"
                                                value="{{ $defaultAddress->address }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <h6>Thành phố</h6>
                                        <div class="input-item">
                                            <input type="text" name="ltn__city" placeholder="Thành phố"
                                                value="{{ $defaultAddress->city }}" readonly>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ltn__checkout-payment-method mt-50">
                        <h4 class="title-2">Phương thức thanh toán</h4>
                        <form action="{{ route('checkout.placeOrder') }}" method="POST">
                            @csrf
                            <input type="hidden" name="address_id" id="hidden_address_id"
                                value="{{ $defaultAddress->id }}">

                            <div id="checkout_payment">

                                <div class="card">

                                    <h5 class="ltn__card-title">
                                        <input type="radio" name="payment_method" value="cash" id="payment_cod" checked>
                                        <label for="payment_cod">
                                            Thanh toán khi giao<img src="{{ asset('assets/clients/img/icons/cash.png') }}"
                                                alt="javascript:void(0)">
                                        </label>
                                    </h5>

                                </div>

                                <div class="card">

                                    <h5 class="collapsed ltn__card-title">
                                        <input type="radio" name="payment_method" value="sepay" id="payment_sepay">

                                        <label for="payment_sepay">
                                            SePay
                                            <img src="{{ asset('assets/clients/img/icons/payment-3.png') }}"
                                                alt="javascript:void(0)">
                                        </label>
                                    </h5>

                                </div>
                            </div>
                            <div class="ltn__payment-note mt-30 mb-30">
                                <p>XIN QUÝ KHÁCH VUI LÒNG KIỂM TRA LẠI THÔNG TIN, SỐ SẢN PHẨM MUA , ĐỊA CHỈ HOẶC SỐ ĐIỆN
                                    THOẠI ĐỂ TRÁNH NHẦM LẪN .</p>
                            </div>
                            <button class="btn theme-btn-1 text-uppercase" type="submit" id="order_button_cash">Đặt
                                hàng</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="shoping-cart-total mt-50">
                        <h4>Tổng Giỏ Hàng</h4>
                        <table class="table">
                            <tbody>
                                @foreach ($cartItems as $item)
                                    <tr>
                                        <td>
                                            {{ $item['name'] }}
                                            <strong>× {{ $item['quantity'] }}</strong>
                                        </td>
                                        <td>
                                            {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} đ
                                        </td>
                                    </tr>
                                @endforeach

                                <tr>
                                    <td colspan="2" style="padding: 0;">
                                        <hr style="margin: 10px 0;">
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Tổng Tiền Hàng:</strong></td>
                                    <td>
                                        <strong>
                                            <span class="cart-total">{{ number_format($totalPrice, 0, ',', '.') }}
                                                đ</span>
                                        </strong>
                                    </td>
                                </tr>
                                {{-- Hiển thị khoảng cách ngay từ đầu --}}
                                {{-- <tr>
                                    <td>
                                        Phí Vận Chuyển:
                                        <br>
                                        <small id="distance-info" class="text-muted text-success font-weight-bold">
                                            @if (isset($distance) && $distance > 0)
                                                (Khoảng cách: {{ $distance }} km)
                                            @endif
                                        </small>
                                    </td>

                                    <td id="shipping-fee">
                                        <strong>{{ number_format($shippingFee, 0, ',', '.') }} đ</strong>
                                    </td>
                                </tr> --}}
                                {{-- Hiển thị phí ship từ Controller --}}
                                <tr>
                                    <td><strong>Tổng Cộng:</strong></td>
                                    <td>
                                        <strong>
                                            <span class="cart-grand-total" id="total-price-display"
                                                style="color: var(--ltn__secondary-color); font-size: 18px;">
                                                {{-- Cộng tổng tiền hàng + Phí ship tính được --}}
                                                {{ number_format($totalPrice, 0, ',', '.') }} đ
                                            </span>
                                        </strong>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('list_address')?.addEventListener('change', function() {
            document.getElementById('hidden_address_id').value = this.value;
        });
    </script>
@endpush

{{-- @push('scripts')
    <script>
        $(document).ready(function() {

            // Biến lưu tổng tiền hàng (cố định)
            const cartTotal = {{ $totalPrice }};

            // Lấy phí ship ban đầu từ Controller truyền sang (Mặc định 30k nếu lỗi)
            let currentShippingFee = {{ $shippingFee ?? 30000 }};

            // Hàm tính phí ship khi chọn địa chỉ
            function calculateShipping(addressId) {
                // Hiệu ứng loading
                $('#shipping-fee').html('<span class="text-muted">Đang tính...</span>');
                $('#total-price-display').css('opacity', 0.5);

                $.ajax({
                    url: '{{ route('checkout.getShippingFee') }}',
                    type: 'GET',
                    data: {
                        address_id: addressId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Cập nhật biến toàn cục để dùng nếu cần
                            currentShippingFee = response.fee;

                            // 1. Cập nhật phí ship (Text đã format từ server)
                            $('#shipping-fee').html('<strong>' + response.fee_formatted + '</strong>');

                            // 2. Cập nhật khoảng cách
                            if (response.distance_text) {
                                $('#distance-info').text(response.distance_text);
                            }

                            // 3. Cập nhật tổng tiền (Lấy luôn từ server cho chuẩn)
                            $('#total-price-display').text(response.grand_total);
                            $('#total-price-display').css('opacity', 1);
                        }
                    },
                    error: function() {
                        toastr.error('Lỗi tính phí vận chuyển.');
                        $('#shipping-fee').text('Liên hệ sau');
                        $('#total-price-display').css('opacity', 1);
                    }
                });
            }

            // Khi chọn địa chỉ khác -> Gọi hàm tính
            $('#list_address').change(function() {
                const addressId = $(this).val();
                $('#hidden_address_id').val(addressId); // Cập nhật input hidden cho form submit
                calculateShipping(addressId);
            });

            // Lưu ý: Không cần gọi calculateShipping() khi vừa load trang nữa
            // Vì ở Bước 1 (Controller@index) ta đã tính sẵn và hiển thị rồi.
        });
    </script>
@endpush --}}
