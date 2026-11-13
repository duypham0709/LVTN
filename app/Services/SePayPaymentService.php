<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnexpectedValueException;

class SePayPaymentService
{
    private const SIGNED_FIELDS = [
        'order_amount',
        'merchant',
        'currency',
        'operation',
        'order_description',
        'order_invoice_number',
        'customer_id',
        'payment_method',
        'success_url',
        'error_url',
        'cancel_url',
    ];

    public function isConfigured(): bool
    {
        return filled(config('services.sepay.merchant_id'))
            && filled(config('services.sepay.secret_key'));
    }

    /**
     * @return array<string, string>
     */
    public function checkoutFields(Order $order, Payment $payment): array
    {
        if (! $this->isConfigured()) {
            throw new InvalidArgumentException('SePay merchant credentials are not configured.');
        }

        $amount = (float) $payment->amount;

        if ($amount <= 0 || floor($amount) !== $amount) {
            throw new InvalidArgumentException('SePay requires a positive whole-number VND amount.');
        }

        $fields = [
            'order_amount' => (string) (int) $amount,
            'merchant' => (string) config('services.sepay.merchant_id'),
            'currency' => 'VND',
            'operation' => 'PURCHASE',
            'order_description' => 'Thanh toan don hang #'.$order->getKey(),
            'order_invoice_number' => (string) $payment->transaction_id,
            'lang' => 'vi',
        ];

        $fields['signature'] = $this->sign($fields);

        return $fields;
    }

    public function checkoutUrl(): string
    {
        return $this->isProduction()
            ? 'https://pay.sepay.vn/v1/checkout/init'
            : 'https://pay-sandbox.sepay.vn/v1/checkout/init';
    }

    /**
     * Check the order status with SePay and persist only a verified result.
     *
     * @throws ConnectionException
     * @throws RequestException
     * @throws UnexpectedValueException
     */
    public function refreshPaymentStatus(Order $order, Payment $payment): string
    {
        if ($payment->status !== 'pending') {
            return $payment->status;
        }

        if (! $this->isConfigured()) {
            throw new UnexpectedValueException('SePay credentials are not configured.');
        }

        $invoiceNumber = (string) $payment->transaction_id;
        $response = Http::withBasicAuth(
            (string) config('services.sepay.merchant_id'),
            (string) config('services.sepay.secret_key'),
        )
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(8)
            ->get($this->apiBaseUrl().'/v1/order/detail/'.rawurlencode($invoiceNumber))
            ->throw()
            ->json('data');

        if (
            ! is_array($response)
            || ($response['order_invoice_number'] ?? null) !== $invoiceNumber
            || ($response['order_currency'] ?? null) !== 'VND'
            || ! $this->amountsMatch($response['order_amount'] ?? null, $payment->amount)
        ) {
            throw new UnexpectedValueException('SePay returned an order that does not match the local payment.');
        }

        $approvedTransaction = collect($response['transactions'] ?? [])
            ->first(fn (mixed $transaction): bool => is_array($transaction)
                && ($transaction['transaction_type'] ?? null) === 'PAYMENT'
                && ($transaction['transaction_status'] ?? null) === 'APPROVED'
                && ($transaction['transaction_currency'] ?? null) === 'VND'
                && $this->amountsMatch($transaction['transaction_amount'] ?? null, $payment->amount));

        if (($response['order_status'] ?? null) === 'CAPTURED' && is_array($approvedTransaction)) {
            return DB::transaction(function () use ($order, $payment, $approvedTransaction): string {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedPayment->status !== 'pending') {
                    return $lockedPayment->status;
                }

                $lockedOrder = Order::query()
                    ->whereKey($order->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedPayment->update([
                    'status' => 'completed',
                    'transaction_id' => $approvedTransaction['transaction_id']
                        ?? $approvedTransaction['id']
                        ?? $lockedPayment->transaction_id,
                    'paid_at' => now(),
                ]);
                $lockedOrder->update(['status' => 'paid']);

                return 'completed';
            });
        }

        if (($response['order_status'] ?? null) === 'CANCELLED') {
            return DB::transaction(function () use ($order, $payment): string {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedPayment->status !== 'pending') {
                    return $lockedPayment->status;
                }

                $lockedPayment->update(['status' => 'failed']);
                Order::query()
                    ->whereKey($order->getKey())
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->update(['status' => 'cancelled']);

                return 'failed';
            });
        }

        return 'pending';
    }

    private function sign(array $fields): string
    {
        $signedFields = [];

        foreach (self::SIGNED_FIELDS as $field) {
            if (! empty($fields[$field])) {
                $signedFields[] = $field.'='.$fields[$field];
            }
        }

        return base64_encode(hash_hmac(
            'sha256',
            implode(',', $signedFields),
            (string) config('services.sepay.secret_key'),
            true,
        ));
    }

    private function isProduction(): bool
    {
        return config('services.sepay.environment') === 'production';
    }

    private function apiBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://pgapi.sepay.vn'
            : 'https://pgapi-sandbox.sepay.vn';
    }

    private function amountsMatch(mixed $actual, mixed $expected): bool
    {
        return is_numeric($actual)
            && is_numeric($expected)
            && (int) round((float) $actual * 100) === (int) round((float) $expected * 100);
    }
}
