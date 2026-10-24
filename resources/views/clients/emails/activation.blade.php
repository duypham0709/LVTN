<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Kích hoạt tài khoản</title>
</head>

<body>
    <h1>Xin chào, {{ $user->name }}</h1>

    <p>Cảm ơn bạn đã đăng ký tại website của chúng tôi. Để kích hoạt tài khoản của bạn, vui lòng nhấp vào liên kết dưới
        đây:</p>

    <a href="{{ url('/activate/' . $token) }}"
        style="display: inline-block; padding: 10px 15px; background-color: green; color: #fff; text-decoration: none; border-radius: 5px;">
        Kích hoạt tài khoản
    </a>

    <p>Trân trọng,</p>
    <p>Đội ngũ hỗ trợ khách hàng</p>
</body>

</html>
