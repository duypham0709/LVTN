@extends('layouts.client')

@section('title', 'Thanh toán SePay')
@section('breadcrumb', 'Thanh toán SePay')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card p-4">
                    <h2>Thanh toán đơn hàng #{{ $order->id }}</h2>
                    <p>
                        Số tiền cần thanh toán:
                        <strong>{{ number_format($payment->amount, 0, ',', '.') }} đ</strong>
                    </p>
                    <p>
                        Bấm nút bên dưới để mở cổng SePay trong thẻ mới. Hãy giữ trang này mở để hệ thống tự kiểm tra
                        kết quả thanh toán.
                    </p>

                    <form action="{{ $checkoutUrl }}" method="POST" target="_blank" id="sepay-form">
                        @foreach ($checkoutFields as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <button class="btn theme-btn-1 text-uppercase" type="submit" id="sepay-submit">
                            Mở cổng thanh toán SePay
                        </button>
                    </form>

                    <p id="sepay-status" class="mt-4" role="status" aria-live="polite">
                        Đang kiểm tra trạng thái thanh toán...
                    </p>
                    <button class="btn btn-outline-secondary d-none" id="sepay-check-again" type="button">
                        Kiểm tra lại
                    </button>
                    <a class="btn theme-btn-1 mt-3 d-none" href="{{ route('account') }}" id="sepay-account-link">
                        Xem tài khoản
                    </a>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            (() => {
                const statusElement = document.getElementById('sepay-status');
                const retryButton = document.getElementById('sepay-check-again');
                const accountLink = document.getElementById('sepay-account-link');
                const checkoutButton = document.getElementById('sepay-submit');
                const checkoutForm = document.getElementById('sepay-form');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const statusUrl = @json($statusUrl);
                let attempts = 0;
                let checking = false;
                let checkoutStarted = false;

                async function checkPaymentStatus() {
                    if (checking) {
                        return;
                    }

                    checking = true;
                    retryButton.classList.add('d-none');

                    try {
                        const response = await fetch(statusUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });
                        const result = await response.json();

                        if (response.ok && result.status === 'completed') {
                            statusElement.textContent = result.message;
                            accountLink.classList.remove('d-none');
                            checkoutButton.disabled = true;

                            return;
                        }

                        if (response.ok && result.status === 'failed') {
                            statusElement.textContent = result.message;
                            checkoutButton.disabled = true;

                            return;
                        }

                        statusElement.textContent = response.ok ?
                            result.message :
                            'Chưa thể kiểm tra thanh toán. Hệ thống sẽ thử lại.';
                    } catch {
                        statusElement.textContent = 'Mất kết nối khi kiểm tra. Hệ thống sẽ thử lại.';
                    } finally {
                        checking = false;
                    }

                    attempts++;

                    if (attempts < 60) {
                        window.setTimeout(checkPaymentStatus, 5000);
                    } else {
                        statusElement.textContent += ' Bạn có thể bấm “Kiểm tra lại” để tiếp tục.';
                        retryButton.classList.remove('d-none');
                    }
                }

                checkoutForm.addEventListener('submit', (event) => {
                    if (checkoutStarted) {
                        event.preventDefault();

                        return;
                    }

                    checkoutStarted = true;
                    checkoutButton.disabled = true;
                    statusElement.textContent = 'Đang chờ SePay xác nhận thanh toán...';
                    attempts = 0;
                    window.setTimeout(checkPaymentStatus, 5000);
                });

                retryButton.addEventListener('click', () => {
                    attempts = 0;
                    checkPaymentStatus();
                });
            })();
        </script>
    @endpush

@endsection
