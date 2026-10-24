@extends('layouts.client')

@section('title', 'Câu hỏi thường gặp')

@section('breadcrumb', 'Những Câu Hỏi Thường Gặp')

@section('content')

    <div class="ltn__faq-area mb-100" style="margin-left: 20px ; margin-top: -80px">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="ltn__faq-inner ltn__faq-inner-2">
                        <div id="accordion_2">
                            <div class="card">
                                <h6 class="collapsed ltn__card-title" data-bs-toggle="collapse" data-bs-target="#faq-item-2-1"
                                    aria-expanded="false">
                                    Làm thế nào để đặt hàng rau củ sạch?
                                </h6>
                                <div id="faq-item-2-1" class="collapse" data-parent="#accordion_2">
                                    <div class="card-body">
                                        <p>Rất đơn giản! Bạn chỉ cần truy cập trang "Cửa hàng" hoặc tìm kiếm sản phẩm mong
                                            muốn. Sau đó, thêm sản phẩm vào giỏ hàng, chọn số lượng và tiến hành thanh toán.
                                            Chúng tôi hỗ trợ nhiều phương thức thanh toán linh hoạt để thuận tiện nhất cho
                                            bạn.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <h6 class="ltn__card-title" data-bs-toggle="collapse" data-bs-target="#faq-item-2-2"
                                    aria-expanded="true">
                                    Làm sao để bảo quản rau củ tươi lâu hơn?
                                </h6>
                                <div id="faq-item-2-2" class="collapse show" data-parent="#accordion_2">
                                    <div class="card-body">
                                        <p>Để rau củ tươi lâu, bạn nên loại bỏ các phần hư hỏng trước khi cất giữ. Đa số các
                                            loại rau lá xanh nên được bọc trong giấy báo hoặc túi nilon có lỗ thoát khí và
                                            để trong ngăn mát tủ lạnh. Các loại củ quả như khoai tây, hành tây nên để nơi
                                            khô ráo, thoáng mát, tránh ánh nắng trực tiếp.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <h6 class="collapsed ltn__card-title" data-bs-toggle="collapse"
                                    data-bs-target="#faq-item-2-3" aria-expanded="false">
                                    Tôi là người mới, tôi nên bắt đầu từ đâu?
                                </h6>
                                <div id="faq-item-2-3" class="collapse" data-parent="#accordion_2">
                                    <div class="card-body">
                                        <p>Chào mừng bạn đến với cửa hàng rau củ sạch của chúng tôi! Bạn có thể bắt đầu bằng
                                            cách tham khảo danh mục "Sản phẩm mới" hoặc "Rau củ theo mùa" để chọn những thực
                                            phẩm tươi ngon nhất. Đừng quên đăng ký tài khoản để tích điểm và nhận ưu đãi đặc
                                            biệt nhé.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <h6 class="collapsed ltn__card-title" data-bs-toggle="collapse"
                                    data-bs-target="#faq-item-2-4" aria-expanded="false">
                                    Phí vận chuyển được tính như thế nào?
                                </h6>
                                <div id="faq-item-2-4" class="collapse" data-parent="#accordion_2">
                                    <div class="card-body">
                                        <p>Phí vận chuyển sẽ được tính dựa trên khoảng cách từ cửa hàng đến địa chỉ giao
                                            hàng của bạn. Chúng tôi thường xuyên có các chương trình miễn phí vận chuyển cho
                                            đơn hàng đạt giá trị tối thiểu. Chi phí cụ thể sẽ hiển thị rõ ràng tại bước
                                            thanh toán.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <h6 class="collapsed ltn__card-title" data-bs-toggle="collapse"
                                    data-bs-target="#faq-item-2-5" aria-expanded="false">
                                    Thông tin cá nhân của tôi có được bảo mật không?
                                </h6>
                                <div id="faq-item-2-5" class="collapse" data-parent="#accordion_2">
                                    <div class="card-body">
                                        <p>Tuyệt đối an toàn. Chúng tôi cam kết bảo mật mọi thông tin cá nhân của khách hàng
                                            và chỉ sử dụng cho mục đích giao hàng và chăm sóc khách hàng. Chúng tôi không
                                            chia sẻ thông tin của bạn với bất kỳ bên thứ ba nào.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <h6 class="collapsed ltn__card-title" data-bs-toggle="collapse"
                                    data-bs-target="#faq-item-2-6" aria-expanded="false">
                                    Nguồn gốc rau củ của cửa hàng ở đâu?
                                </h6>
                                <div id="faq-item-2-6" class="collapse" data-parent="#accordion_2">
                                    <div class="card-body">
                                        <p>Chúng tôi hợp tác trực tiếp với các nông trại đạt chuẩn VietGAP và Organic tại Đà
                                            Lạt và các vùng trồng rau sạch lân cận. Quy trình trồng trọt được kiểm soát
                                            nghiêm ngặt, không sử dụng thuốc trừ sâu hóa học hay chất kích thích tăng
                                            trưởng, đảm bảo an toàn tuyệt đối cho sức khỏe của bạn và gia đình.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <h6 class="collapsed ltn__card-title" data-bs-toggle="collapse"
                                    data-bs-target="#faq-item-2-7" aria-expanded="false">
                                    Tôi có thể thanh toán bằng những hình thức nào?
                                </h6>
                                <div id="faq-item-2-7" class="collapse" data-parent="#accordion_2">
                                    <div class="card-body">
                                        <p>Chúng tôi hỗ trợ đa dạng các hình thức thanh toán bao gồm: Thanh toán khi nhận
                                            hàng (COD), Chuyển khoản ngân hàng, và thanh toán qua ví điện tử VNPay. Bạn có
                                            thể lựa chọn phương thức thuận tiện nhất tại bước thanh toán.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="need-support text-center mt-100">
                            <h2>Cần hỗ trợ thêm ? Liên hệ với chúng tôi 24/7:</h2>
                            <div class="btn-wrapper mb-30">
                                <a href="#" class="theme-btn-1 btn">Liên Hệ Ngay</a>
                            </div>
                            {{-- <h3><i class="fas fa-phone"></i> +0999-999-999</h3> --}}
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <aside class="sidebar-area ltn__right-sidebar">
                        {{-- <div class="widget ltn__search-widget ltn__newsletter-widget">
                            <h6 class="ltn__widget-sub-title">// đăng ký</h6>
                            <h4 class="ltn__widget-title">Nhận Bản Tin</h4>
                            <form action="#">
                                <input type="text" name="search" placeholder="Nhập email của bạn">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                            <div class="ltn__newsletter-bg-icon">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                        </div> --}}

                        {{-- <div class="widget ltn__banner-widget">
                            <a href="{{ route('products.index') }}"><img
                                    src="{{ asset('assets/user/img/banner/banner-3.jpg') }}" alt="Banner Image"></a>
                        </div> --}}

                    </aside>
                </div>
            </div>
        </div>
    </div>
    <div class="ltn__counterup-area bg-image bg-overlay-theme-black-80 pt-200 pb-150"
        data-bg="{{ asset('assets/clients/img/bg/0.png') }}">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6 align-self-center">
                    {{-- <div class="ltn__counterup-item-3 text-color-white text-center">
                        <div class="counter-icon"> <img src="{{ asset('assets/user/img/icons/icon-img/2.png') }}"
                                alt="#"> </div>
                        <h1><span class="counter">733</span><span class="counterUp-icon">+</span> </h1>
                        <h6>Khách Hàng Hài Lòng</h6>
                    </div> --}}
                </div>
                <div class="col-md-3 col-sm-6 align-self-center">
                    {{-- <div class="ltn__counterup-item-3 text-color-white text-center">
                        <div class="counter-icon"> <img src="{{ asset('assets/user/img/icons/icon-img/3.png') }}"
                                alt="#"> </div>
                        <h1><span class="counter">33</span><span class="counterUp-letter">K</span><span
                                class="counterUp-icon">+</span> </h1>
                        <h6>Loại Rau Củ Sạch</h6>
                    </div> --}}
                </div>
                <div class="col-md-3 col-sm-6 align-self-center">
                    {{-- <div class="ltn__counterup-item-3 text-color-white text-center">
                        <div class="counter-icon"> <img src="{{ asset('assets/user/img/icons/icon-img/4.png') }}"
                                alt="#"> </div>
                        <h1><span class="counter">100</span><span class="counterUp-icon">+</span> </h1>
                        <h6>Sản Phẩm Hữu Cơ</h6>
                    </div> --}}
                </div>
                <div class="col-md-3 col-sm-6 align-self-center">
                    {{-- <div class="ltn__counterup-item-3 text-color-white text-center">
                        <div class="counter-icon"> <img src="{{ asset('assets/user/img/icons/icon-img/5.png') }}"
                                alt="#"> </div>
                        <h1><span class="counter">250</span><span class="counterUp-icon">+</span> </h1>
                        <h6>Đối Tác Cung Cấp</h6>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

@endsection
