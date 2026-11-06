@extends('layouts.client')

@section('title', 'Tài khoản')
@section('breadcrumb', 'Tài khoản')

@section('content')

    <!-- WISHLIST AREA START -->
    <div class="liton__wishlist-area pb-70">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- PRODUCT TAB AREA START -->
                    <div class="ltn__product-tab-area">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="ltn__tab-menu-list mb-50">
                                        <div class="nav">
                                            <a class="active show" data-bs-toggle="tab" href="#liton_tab_dashboard">Bảng
                                                điều
                                                khiển <i class="fas fa-home"></i></a>
                                            <a data-bs-toggle="tab" href="#liton_tab_orders">Đơn hàng <i
                                                    class="fas fa-file-alt"></i></a>

                                            <a data-bs-toggle="tab" href="#liton_tab_address">Địa chỉ <i
                                                    class="fas fa-map-marker-alt"></i></a>
                                            <a data-bs-toggle="tab" href="#liton_tab_account">Chi tiết tài khoản <i
                                                    class="fas fa-user"></i></a>
                                            <a data-bs-toggle="tab" href="#liton_tab_password">Đổi mật khẩu <i
                                                    class="fas fa-user"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="tab-content">
                                        <div class="tab-pane fade active show" id="liton_tab_dashboard">
                                            <div class="ltn__myaccount-tab-content-inner">
                                                <form id="account-logout-form" action="{{ route('logout') }}" method="POST"
                                                    class="d-none">
                                                    @csrf
                                                </form>
                                                <p>Xin chào <strong>{{ $user->name }}</strong> (không phải là bạn?
                                                    <small><button type="submit" form="account-logout-form"
                                                            class="logout-submit">Đăng xuất</button></small> )
                                                </p>
                                                <p>Từ bảng điều khiển tài khoản của bạn <span>các đơn hàng gần đây</span>,
                                                    quản lý <span>địa chỉ giao hàng và thanh toán của bạn,
                                                    </span> và <span>thay đổi mật khẩu và thông tin chi tiết.</span>.</p>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="liton_tab_orders">
                                            <div class="ltn__myaccount-tab-content-inner">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Đơn hàng</th>
                                                                <th>Ngày đặt</th>
                                                                <th>Trạng thái</th>
                                                                <th>Tổng tiền</th>
                                                                <th>Thao tác</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>1</td>
                                                                <td>Jun 22, 2019</td>
                                                                <td>Pending</td>
                                                                <td>$3000</td>
                                                                <td><a href="cart.html">View</a></td>
                                                            </tr>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- phần địa chỉ-->
                                        <div class="tab-pane fade" id="liton_tab_address">
                                            <div class="ltn__myaccount-tab-content-inner">
                                                <p>Các địa chỉ sau sẽ được sử dụng trên trang thanh toán theo mặc định.</p>
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 15%;">Tên người nhận</th>
                                                                <th>Địa chỉ</th>
                                                                <th>Thành phố</th>
                                                                <th>Số điện thoại</th>
                                                                <th>Mặc định</th>
                                                                <th>Thao tác</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if (isset($addresses) && $addresses->count() > 0)
                                                                @foreach ($addresses as $address)
                                                                    <tr>
                                                                        <td>{{ $address->full_name }}</td>
                                                                        <td>{{ $address->address }}</td>
                                                                        <td>{{ $address->city }}</td>
                                                                        <td>{{ $address->phone }}</td>
                                                                        <td>
                                                                            @if ($address->default)
                                                                                <span class="badge bg-success">Mặc
                                                                                    định</span>
                                                                            @else
                                                                                <!-- nút chọn địa chỉ mặc định -->
                                                                                <form
                                                                                    action="{{ route('account.addresses.update', $address->id) }}"
                                                                                    method="POST"
                                                                                    class="d-inline form-set-default">
                                                                                    @csrf
                                                                                    @method('PUT')
                                                                                    <button type="submit"
                                                                                        class="btn btn-effect-1 btn-warning btn-sm">Chọn</button>
                                                                                </form>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            <!-- nút xóa địa chỉ-->
                                                                            <form
                                                                                action="{{ route('account.addresses.delete', $address->id) }}"
                                                                                method="POST"
                                                                                class="d-inline form-delete-address">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-danger">Xóa</button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="6" class="text-center">Bạn chưa có địa
                                                                        chỉ nào.</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <button class="btn theme-btn-1 btn-effect-1 mt-3" data-bs-toggle="modal"
                                                    data-bs-target="#addAddressModal">Thêm địa chỉ mới</button>
                                            </div>
                                        </div>

                                        <!-- modal thêm địa chỉ -->
                                        <div class="modal fade" id="addAddressModal" tabindex="-1"
                                            aria-labelledby="addAddressModalLabel" aria-hidden="true">

                                            <div class="modal-dialog">
                                                <div class="modal-content" style="padding: 5px 10px">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="addAddressModalLabel">Thêm địa chỉ mới
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('account.addresses.add') }}" method="POST"
                                                            id="addAddressForm">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <label class="form-lable">Tên người dùng</label>
                                                                <input type="text" class="form-control" name="full_name"
                                                                    required placeholder="Nhập họ tên">
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-lable">Số điện thoại</label>
                                                                <input type="number" class="form-control" name="phone"
                                                                    required placeholder="Nhập số điện thoại"
                                                                    pattern="[0-9]+" title="Chỉ được nhập số">
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-lable">Tỉnh/Thành phố</label>
                                                                {{-- Khóa không cho chọn (disabled) và mặc định là TP.HCM --}}
                                                                <select class="form-control nice-select disabled"
                                                                    id="tinh" name="tinh"
                                                                    style="width: 100%; pointer-events: none; background: #e9ecef;">
                                                                    <option value="79"
                                                                        data-name="Thành phố Hồ Chí Minh" selected>Thành
                                                                        phố Hồ Chí Minh</option>
                                                                </select>
                                                                {{-- Input ẩn để gửi dữ liệu về server vì thẻ select disabled sẽ không gửi value --}}
                                                                <input type="hidden" name="tinh"
                                                                    value="Thành phố Hồ Chí Minh">
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-lable">Quận/Huyện</label>
                                                                        <select class="form-control nice-select"
                                                                            id="quan" name="quan"
                                                                            style="width: 100%;">
                                                                            <option value="0">Quận Huyện</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-lable">Phường/Xã</label>
                                                                        <select class="form-control nice-select"
                                                                            id="phuong" name="phuong"
                                                                            style="width: 100%;">
                                                                            <option value="0">Phường Xã</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-lable">Địa chỉ cụ thể</label>
                                                                <input type="text" class="form-control"
                                                                    id="address_detail" required
                                                                    placeholder="Số nhà, tên đường...">
                                                            </div>

                                                            <input type="hidden" name="address" id="final_address">
                                                            <input type="hidden" name="city" id="final_city">

                                                            <div class="mb-3 form-check">
                                                                <input type="checkbox" class="form-check-input"
                                                                    id="default" name="default">
                                                                <label for="default" class="form-lable">Đặt làm địa chỉ
                                                                    mặc định</label>
                                                            </div>

                                                            <button type="submit" id="btn-add-address"
                                                                class="btn theme-btn-1 btn btn-block">Lưu địa chỉ</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab Chi tiết tài khoản -->
                                        {{-- <div class="tab-pane fade" id="liton_tab_account">

                                            <div class="ltn__myaccount-tab-content-inner">
                                                <div class="ltn__form-box">
                                                    <form id="update-account-form" action="">
                                                        <!-- các input -->
                                                        <div class="row mb-50">
                                                            <div class="col-md-6">
                                                                <label for="ltn__name">Họ và tên:</label>
                                                                <input type="text" name="ltn__name" id="ltn__name"
                                                                    value="{{ $user->name }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="ltn__phone_number">Số điện thoại:</label>
                                                                <input type="number" name="ltn__phone_number"
                                                                    id="ltn__phone_number"
                                                                    value="{{ $user->phone_number ?? '' }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="ltn__email">Email (không được thay đổi)</label>
                                                                <input type="email" name="ltn__email" id="ltn__email"
                                                                    value="{{ $user->email }}" readonly>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="ltn__address">Địa chỉ:</label>
                                                                <input type="text" name="ltn__address"
                                                                    id="ltn__address" value="{{ $user->address ?? '' }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="btn-wrapper">
                                                            <button type="submit" id="btn-update-info"
                                                                class="btn theme-btn-1 btn-effect-1 text-uppercase">Cập
                                                                nhật</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div> --}}

                                        <!-- Tab Đổi mật khẩu -->
                                        {{-- <div class="tab-pane fade" id="liton_tab_password">

                                            <div class="ltn__myaccount-tab-content-inner">
                                                <div class="ltn__form-box">
                                                    <form action="{{ route('account.change-password') }}" method="POST"
                                                        id="change-password-form">

                                                        <fieldset>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label>Mật khẩu hiện tại</label>
                                                                    <input type="password" name="current_password"
                                                                        required>
                                                                    <label>Mật khẩu mới:</label>
                                                                    <input type="password" name="new_password" required>
                                                                    <label>Xác nhận mật khẩu mới:</label>
                                                                    <input type="password"
                                                                        name="new_password_confirmation"
                                                                        autocomplete="new-password" required>
                                                                </div>
                                                            </div>
                                                        </fieldset>
                                                        <div class="btn-wrapper">
                                                            <button type="submit" id="btn-change-password"
                                                                class="btn theme-btn-1 btn-effect-1 text-uppercase">Đổi mật
                                                                khẩu</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div> --}}

                                    </div>

                                    <div class="tab-pane fade" id="liton_tab_account">
                                        <div class="ltn__myaccount-tab-content-inner">
                                            <div class="ltn__form-box">
                                                <form action="#" method="POST" id="update-account"
                                                    enctype="multipart/form-data">

                                                    @method('PUT')

                                                    <div class="row mb-50">
                                                        <div class="col-md-12 text-center mb-3">
                                                            <div class="profile-pic-container">
                                                                <img src="{{ $user->avatar }}" alt="Avatar"
                                                                    id="preview-image" class="profile-pic">
                                                                <input type="file" name="avatar" id="avatar"
                                                                    accept="image/*" class="d-none">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="ltn__name">Họ và tên:</label>
                                                            <input type="text" name="ltn__name" id="ltn__name"
                                                                value="{{ $user->name }}" required>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label for="ltn__phone_number">Số điện thoại:</label>
                                                            <input type="number" name="ltn__phone_number"
                                                                id="ltn__phone_number" value="{{ $user->phone_number }}"
                                                                required>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label for="ltn__email">Email (không thay đổi):</label>
                                                            <input type="text" name="ltn__email" id="ltn__email"
                                                                value="{{ $user->email }}" readonly>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label for="ltn__address">Địa chỉ:</label>
                                                            <input type="text" name="ltn__address" id="ltn__address"
                                                                value="{{ $user->address }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="btn-wrapper">
                                                        <button type="submit"
                                                            class="btn theme-btn-1 btn-effect-1 text-uppercase">Cập
                                                            nhật</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="liton_tab_password">
                                        <div class="ltn__myaccount-tab-content-inner">
                                            <div class="ltn__form-box">
                                                <form action="#" method="POST" id="change-password-form">
                                                    <fieldset>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label>Mật khẩu hiện tại:</label>
                                                                <input type="password" name="current_password" required>
                                                                <label>Mật khẩu mới:</label>
                                                                <input type="password" name="new_password" required>
                                                                <label>Xác nhận mật khẩu mới:</label>
                                                                <input type="password" name="confirm_new_password"
                                                                    autocomplete="new-password" required>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                    <div class="btn-wrapper">
                                                        <button type="submit"
                                                            class="btn theme-btn-1 btn-effect-1 text-uppercase">Thay
                                                            đổi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- PRODUCT TAB AREA END -->
            </div>
        </div>
    </div>
    </div>
@endsection

<!-- ============================================= -->
<!-- Script custom.js -->
<!-- ============================================= -->
@push('scripts')
    <!-- jQuery Nice Select CSS & JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>

    <style>
        .nice-select .list {
            z-index: 9999 !important;
            max-height: 250px;
            overflow-y: auto;
            width: 100%;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .nice-select {
            width: 100%;
            margin-bottom: 15px;
            float: none;
        }
    </style>

    <script>
        $(document).ready(function() {

            setTimeout(function() {
                $('select.nice-select').niceSelect();
            }, 500);

            let idHCM = '79';
            $("#final_city").val('Thành phố Hồ Chí Minh');
            // 1.1. Load Tỉnh/Thành
            fetch(`https://esgoo.net/api-tinhthanh/2/${idHCM}.htm`)
                .then(response => response.json())
                .then(data => {
                    if (data.error === 0) {
                        $("#quan").html('<option value="0">Chọn Quận/Huyện</option>');
                        data.data.forEach(val => {
                            $("#quan").append(
                                `<option value="${val.id}" data-name="${val.full_name}">${val.full_name}</option>`
                            );
                        });
                        $("#quan").niceSelect('update');
                    }
                })
                .catch(error => console.error('Lỗi tải Quận:', error));

            // 1.2. Chọn Tỉnh -> Load Quận/Huyện
            $("#tinh").change(function() {
                let idtinh = $(this).val();
                let tenTinh = $("#tinh option:selected").data('name');
                $("#final_city").val(tenTinh); // Lưu tên tỉnh

                // Reset Quận/Huyện & Phường/Xã
                $("#quan").html('<option value="0">Chọn Quận/Huyện</option>');
                $("#phuong").html('<option value="0">Chọn Phường/Xã</option>');
                $("#quan").niceSelect('update');
                $("#phuong").niceSelect('update');

                if (idtinh == 0) return;

                fetch(`https://esgoo.net/api-tinhthanh/2/${idtinh}.htm`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error === 0) {
                            data.data.forEach(val => {
                                $("#quan").append(
                                    `<option value="${val.id}" data-name="${val.full_name}">${val.full_name}</option>`
                                );
                            });
                            $("#quan").niceSelect('update');
                        }
                    })
                    .catch(error => console.error('Lỗi tải Quận:', error));
            });

            // 1.3. Chọn Quận -> Load Phường/Xã
            $("#quan").change(function() {
                let idquan = $(this).val();

                // Reset Phường/Xã
                $("#phuong").html('<option value="0">Chọn Phường/Xã</option>');
                $("#phuong").niceSelect('update');

                if (idquan == 0) return;

                fetch(`https://esgoo.net/api-tinhthanh/3/${idquan}.htm`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error === 0) {
                            data.data.forEach(val => {
                                $("#phuong").append(
                                    `<option value="${val.id}" data-name="${val.full_name}">${val.full_name}</option>`
                                );
                            });
                            $("#phuong").niceSelect('update');
                        }
                    })
                    .catch(error => console.error('Lỗi tải Phường:', error));
            });


            // =======================================================
            // 2. XỬ LÝ LƯU ĐỊA CHỈ (Submit Form)
            // =======================================================
            $('#addAddressForm').on('submit', function(e) {
                e.preventDefault();

                // Validate chọn đủ 3 cấp
                if ($("#tinh").val() == 0 || $("#quan").val() == 0 || $("#phuong").val() == 0) {
                    toastr.error("Vui lòng chọn đầy đủ Tỉnh, Huyện, Xã!");
                    return;
                }

                // Gộp địa chỉ chuẩn: "Số nhà, Phường, Quận"
                let soNha = $('#address_detail').val();
                let phuong = $("#phuong option:selected").data('name');
                let quan = $("#quan option:selected").data('name');

                let fullAddress = `${soNha}, ${phuong}, ${quan}`;
                $('#final_address').val(fullAddress);

                // Gửi Ajax về Server
                let form = $(this);
                let btn = $('#btn-add-address');
                let originalText = btn.text();
                btn.prop('disabled', true).text('Đang lưu...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#addAddressModal').modal('hide');
                            form[0].reset();
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function(xhr) {
                        // Nếu lỗi Validate (422) từ Laravel trả về
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            // Lặp qua từng lỗi và hiện Toastr
                            $.each(errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            // Lỗi Server khác (500, 404...)
                            toastr.error('Có lỗi xảy ra, vui lòng thử lại sau.');
                        }

                        // Mở lại nút bấm
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });



            // cập nhật thông tin
            $('#update-account-form').on('submit', function(e) {
                e.preventDefault();
                let btn = $('#btn-update-info');
                let originalText = btn.text();
                btn.prop('disabled', true).text('Đang lưu...');
                let formData = new FormData(this);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Thành công!');
                            $('input[name="ltn__name"]').val(response.user.name);
                            $('input[name="ltn__phone_number"]').val(response.user
                                .phone_number);
                            $('input[name="ltn__address"]').val(response.user.address);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                toastr.error(value[0], 'Lỗi nhập liệu');
                            });
                        } else {
                            toastr.error('Có lỗi xảy ra, vui lòng thử lại.', 'Lỗi hệ thống');
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });


            // đổi mật khẩu
            $('#change-password-form').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = $('#btn-change-password');
                let originalText = btn.text();
                btn.prop('disabled', true).text('Đang xử lý...');
                let formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Thành công!');
                            form[0].reset();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                toastr.error(value[0], 'Lỗi');
                            });
                        } else {
                            toastr.error('Có lỗi xảy ra, vui lòng thử lại.', 'Lỗi hệ thống');
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });



            // chọn địa chỉ mặc định
            $(document).on('submit', '.form-set-default', function(e) {
                e.preventDefault();

                let form = $(this);
                let btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).text('Đang chọn...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Thành công!');
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        toastr.error('Có lỗi xảy ra, vui lòng thử lại.', 'Lỗi hệ thống');
                        btn.prop('disabled', false).text('Chọn');
                    }
                });
            });


            // xóa địa chỉ
            $(document).on('submit', '.form-delete-address', function(e) {
                e.preventDefault();

                if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) {
                    return false;
                }

                let form = $(this);
                let btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).text('Đang xóa...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Thành công!');
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message, 'Lỗi');
                        } else {
                            toastr.error('Có lỗi xảy ra, vui lòng thử lại.', 'Lỗi hệ thống');
                        }
                        btn.prop('disabled', false).text('Xóa');
                    }
                });
            });

        });
    </script>
@endpush
