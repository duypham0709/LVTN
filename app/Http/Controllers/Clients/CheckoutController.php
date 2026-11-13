<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingAddress;
use App\Services\CartService;
use App\Services\SePayPaymentService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use UnexpectedValueException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly SePayPaymentService $sePayPaymentService,
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $addresses = ShippingAddress::where('user_id', $user->id)->get();
        $defaultAddress = $addresses->where('default', 1)->first();

        if (! $defaultAddress) {
            toastr()->error('Vui lòng thêm địa chỉ giao hàng.');

            return redirect()->route('account');
        }

        $cartItems = $this->cartService->items();
        $totalPrice = $this->cartService->total();

        return view('clients.pages.checkout', compact('addresses', 'defaultAddress', 'cartItems', 'totalPrice'));
    }

    public function getAddress(Request $request): JsonResponse
    {
        $address = ShippingAddress::where('id', $request->address_id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $address) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy địa chỉ'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address,
        ]);
    }

    public function placeOrder(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'address_id' => [
                'required',
                'integer',
                Rule::exists('shipping_addresses', 'id')->where('user_id', $user->id),
            ],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'sepay'])],
        ]);
        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            toastr()->error('Giỏ hàng trống.');

            return redirect()->route('cart.index');
        }

        $totalPrice = $this->cartService->total();

        if ($validated['payment_method'] === 'sepay') {
            if (! $this->sePayPaymentService->isConfigured()) {
                toastr()->error('Vui lòng cấu hình thông tin SePay trước khi thanh toán.');

                return redirect()->route('checkout');
            }

            if ($totalPrice <= 0 || floor($totalPrice) !== $totalPrice) {
                toastr()->error('SePay chỉ hỗ trợ thanh toán số tiền VND nguyên dương.');

                return redirect()->route('checkout');
            }
        }

        [$order, $payment] = DB::transaction(function () use ($cartItems, $totalPrice, $user, $validated): array {
            $order = Order::create([
                'user_id' => $user->id,
                'shipping_address_id' => $validated['address_id'],
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['payment_method'] === 'sepay'
                    ? 'LVTN-'.$order->id
                    : null,
                'amount' => $order->total_price,
                'status' => 'pending',
                'paid_at' => null,
            ]);

            CartItem::where('user_id', $user->id)->delete();

            return [$order, $payment];
        });

        if ($payment->payment_method === 'sepay') {
            return $this->handleSePayPayment($order, $payment);
        }

        toastr()->success('Đặt hàng thành công.');

        return redirect()->route('account');
    }

    private function handleSePayPayment(Order $order, Payment $payment): View
    {
        $returnUrls = [];

        if (config('app.env') !== 'local') {
            $returnUrls = [
                'success_url' => route('checkout.sepay.return', [
                    'orderId' => $order->id,
                    'result' => 'success',
                ]),
                'error_url' => route('checkout.sepay.return', [
                    'orderId' => $order->id,
                    'result' => 'error',
                ]),
                'cancel_url' => route('checkout.sepay.return', [
                    'orderId' => $order->id,
                    'result' => 'cancel',
                ]),
            ];
        }

        return view('clients.pages.sepay_checkout', [
            'order' => $order,
            'payment' => $payment,
            'checkoutUrl' => $this->sePayPaymentService->checkoutUrl(),
            'checkoutFields' => $this->sePayPaymentService->checkoutFields($order, $payment, $returnUrls),
            'statusUrl' => route('checkout.sepay.status', $order->id),
        ]);
    }

    public function sePayReturn(int $orderId, string $result): RedirectResponse
    {
        $order = Order::query()
            ->whereKey($orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('payment_method', 'sepay')
            ->firstOrFail();

        try {
            $status = $this->sePayPaymentService->refreshPaymentStatus($order, $payment);
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            Log::warning('Unable to verify returned SePay payment.', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);
            toastr()->error('Chưa thể xác minh thanh toán. Vui lòng kiểm tra lại trong tài khoản.');

            return redirect()->route('account');
        }

        if ($status === 'completed') {
            toastr()->success('Thanh toán thành công.');
        } elseif ($status === 'failed') {
            toastr()->error('Giao dịch đã bị hủy trên SePay.');
        } else {
            $message = match ($result) {
                'error' => 'SePay chưa xác nhận thanh toán thành công.',
                'cancel' => 'Bạn đã hủy giao dịch hoặc giao dịch chưa hoàn tất.',
                default => 'Thanh toán đang chờ SePay xác nhận.',
            };
            toastr()->info($message);
        }

        return redirect()->route('account');
    }

    public function sePayStatus(int $orderId): JsonResponse
    {
        $order = Order::query()
            ->whereKey($orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('payment_method', 'sepay')
            ->firstOrFail();

        try {
            $status = $this->sePayPaymentService->refreshPaymentStatus($order, $payment);
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            Log::warning('Unable to verify SePay payment status.', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'unavailable',
                'message' => 'Chưa thể kiểm tra thanh toán. Vui lòng thử lại.',
            ], 503);
        }

        return response()->json([
            'status' => $status,
            'message' => match ($status) {
                'completed' => 'Thanh toán thành công.',
                'failed' => 'Giao dịch đã bị hủy trên SePay.',
                default => 'Đang chờ SePay xác nhận thanh toán.',
            },
        ]);
    }
}
