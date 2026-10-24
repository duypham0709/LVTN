@extends('layouts.client')

@section('title', 'Về chúng tôi')

@section('breadcrumb', 'Về Chúng Tôi')

@section('content')

    <!-- ABOUT INTRO START -->
    <section class="about-section pt-60 pb-60">
        <div class="container">
            <h2 class="mb-4 fw-bold" style="margin-left: 350px;margin-top: -80px">GIỚI THIỆU VỀ NÔNG SẢN SẠCH</h2>
            <br>

            <p>
                Nông Sản Sạch là hệ thống cung cấp các sản phẩm nông sản an toàn, có nguồn gốc xuất xứ rõ ràng,
                được tuyển chọn kỹ lưỡng từ các vùng trồng đạt tiêu chuẩn trên khắp cả nước. Chúng tôi hướng đến
                việc mang lại cho người tiêu dùng Việt Nam những giải pháp mua sắm thực phẩm tiện lợi, hiện đại
                nhưng vẫn đảm bảo chất lượng và độ tin cậy cao. Với phương châm “Sức khỏe khách hàng là ưu tiên hàng đầu”,
                Nông Sản Sạch không ngừng cải tiến quy trình kiểm soát chất lượng, bảo quản và phân phối sản phẩm
                nhằm giữ trọn giá trị dinh dưỡng và độ tươi ngon của từng mặt hàng.
            </p>

            <img src="{{ asset('assets/clients/img/others/winmart.jpg') }}" style="margin-left: 50px" alt="Về chúng tôi Image">

            <p>
                Khi mua sắm tại hệ thống Nông sản sạch, khách hàng không chỉ được trải nghiệm không gian mua sắm
                hiện đại, thân thiện và tiện nghi mà còn được tiếp cận với đa dạng các mặt hàng thiết yếu cho cuộc sống
                hằng ngày. Bên cạnh đó, hệ thống thường xuyên triển khai nhiều chương trình khuyến mãi, ưu đãi hấp dẫn,
                giúp khách hàng tiết kiệm chi phí mà vẫn đảm bảo chất lượng sản phẩm và dịch vụ tốt nhất.
            </p>
        </div>
    </section>
    <!-- ABOUT INTRO END -->


    <!-- VISION START -->
    <section class="about-section pt-60 pb-60">
        <div class="container">
            <h3 class="fw-bold mb-3" style="margin-top: -120px">TẦM NHÌN</h3>

            <p>
                Nông Sản Sạch hướng tới trở thành hệ thống phân phối nông sản sạch hàng đầu tại Việt Nam,
                là lựa chọn đáng tin cậy của mọi gia đình trong việc chăm sóc sức khỏe thông qua bữa ăn hằng ngày.
                Chúng tôi mong muốn xây dựng một chuỗi cung ứng bền vững, kết nối trực tiếp người nông dân với người tiêu
                dùng,
                góp phần nâng cao giá trị nông sản Việt và thúc đẩy nền nông nghiệp sạch, an toàn và thân thiện với môi
                trường.
            </p>

            <img src="{{ asset('assets/clients/img/others/winmart2.jpg') }}" style="margin-left: 50px"
                alt="Về chúng tôi Image">

            <p>
                Trong tương lai, Nông sản sạch sẽ tiếp tục mở rộng mạng lưới cửa hàng trên toàn quốc,
                ứng dụng công nghệ vào quản lý và phục vụ khách hàng nhằm mang đến trải nghiệm mua sắm nhanh chóng,
                thuận tiện và thông minh hơn. Qua đó, chúng tôi kỳ vọng có thể đáp ứng ngày càng tốt hơn
                nhu cầu tiêu dùng đa dạng của khách hàng ở mọi khu vực.
            </p>
        </div>
    </section>
    <!-- VISION END -->


    <!-- COMMITMENT START -->
    <section class="about-section pt-60 pb-60">
        <div class="container">
            <h3 class="fw-bold mb-3" style="margin-top: -120px">CAM KẾT VỚI KHÁCH HÀNG</h3>

            <p>
                Nông Sản Sạch cam kết chỉ cung cấp những sản phẩm đạt tiêu chuẩn chất lượng,
                được kiểm tra nghiêm ngặt về nguồn gốc, vệ sinh an toàn thực phẩm và quy trình bảo quản.
                Chúng tôi luôn minh bạch thông tin sản phẩm, giá cả rõ ràng và đặt quyền lợi của khách hàng lên hàng đầu.
                Mỗi sản phẩm đến tay người tiêu dùng đều là kết quả của sự chọn lọc kỹ càng và trách nhiệm cao đối với cộng
                đồng.
            </p>

            <img src="{{ asset('assets/clients/img/others/winmart3.jpg') }}" style="margin-left: 50px"
                alt="Về chúng tôi Image">

            <p>
                Bên cạnh chất lượng sản phẩm, Nông sản sạch còn chú trọng nâng cao chất lượng dịch vụ,
                lắng nghe ý kiến phản hồi và không ngừng cải thiện để mang đến sự hài lòng cao nhất cho khách hàng.
                Chúng tôi tin rằng sự tin tưởng và đồng hành của khách hàng chính là nền tảng vững chắc
                cho sự phát triển bền vững của Nông Sản Sạch trong tương lai.
            </p>
        </div>
    </section>
    <!-- COMMITMENT END -->

@endsection
