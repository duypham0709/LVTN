<?php

namespace Tests\Feature\Clients;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_a_sepay_order_and_get_a_signed_checkout_form(): void
    {
        Config::set([
            'services.sepay.merchant_id' => 'sandbox-merchant',
            'services.sepay.secret_key' => 'sandbox-secret',
            'services.sepay.environment' => 'sandbox',
        ]);
        [$user, $address, $product] = $this->createCheckoutData();

        $response = $this->actingAs($user)->post(route('checkout.placeOrder'), [
            'address_id' => $address->id,
            'payment_method' => 'sepay',
            'amount' => 1,
        ]);
        $order = Order::query()->firstOrFail();
        $successUrl = route('checkout.sepay.return', [
            'orderId' => $order->id,
            'result' => 'success',
        ]);
        $errorUrl = route('checkout.sepay.return', [
            'orderId' => $order->id,
            'result' => 'error',
        ]);
        $cancelUrl = route('checkout.sepay.return', [
            'orderId' => $order->id,
            'result' => 'cancel',
        ]);

        $expectedSignature = base64_encode(hash_hmac(
            'sha256',
            'order_amount=50000,merchant=sandbox-merchant,currency=VND,operation=PURCHASE,'
                .'order_description=Thanh toan don hang #'.$order->id
                .',order_invoice_number=LVTN-'.$order->id
                .',success_url='.$successUrl
                .',error_url='.$errorUrl
                .',cancel_url='.$cancelUrl,
            'sandbox-secret',
            true,
        ));

        $response->assertOk()
            ->assertViewIs('clients.pages.sepay_checkout')
            ->assertSee('https://pay-sandbox.sepay.vn/v1/checkout/init')
            ->assertSee('name="order_amount" value="50000"', false)
            ->assertSee('name="signature" value="'.$expectedSignature.'"', false)
            ->assertSee('name="success_url" value="'.$successUrl.'"', false)
            ->assertSee('name="error_url" value="'.$errorUrl.'"', false)
            ->assertSee('name="cancel_url" value="'.$cancelUrl.'"', false)
            ->assertSee('target="_blank"', false)
            ->assertDontSee('sandbox-secret', false);
        $response->assertSee('name="order_invoice_number" value="LVTN-'.$order->id.'"', false);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
            'total_price' => 50000,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'sepay',
            'transaction_id' => 'LVTN-'.$order->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 25000,
        ]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_can_place_a_cash_order_without_calling_sepay(): void
    {
        Http::preventStrayRequests();
        [$user, $address] = $this->createCheckoutData();

        $response = $this->actingAs($user)->post(route('checkout.placeOrder'), [
            'address_id' => $address->id,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('account'));
        $order = Order::query()->firstOrFail();
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'transaction_id' => null,
            'status' => 'pending',
        ]);
    }

    public function test_sepay_checkout_requires_credentials_and_preserves_the_cart(): void
    {
        Config::set([
            'services.sepay.merchant_id' => null,
            'services.sepay.secret_key' => null,
        ]);
        [$user, $address] = $this->createCheckoutData();

        $response = $this->actingAs($user)->from(route('checkout'))
            ->post(route('checkout.placeOrder'), [
                'address_id' => $address->id,
                'payment_method' => 'sepay',
            ]);

        $response->assertRedirect(route('checkout'));
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_local_sepay_checkout_does_not_send_unreachable_return_urls(): void
    {
        Config::set('app.env', 'local');
        $this->configureSePay();
        [$user, $address] = $this->createCheckoutData();

        $response = $this->actingAs($user)->post(route('checkout.placeOrder'), [
            'address_id' => $address->id,
            'payment_method' => 'sepay',
        ]);

        $response->assertOk()
            ->assertViewIs('clients.pages.sepay_checkout')
            ->assertDontSee('name="success_url"', false)
            ->assertDontSee('name="error_url"', false)
            ->assertDontSee('name="cancel_url"', false);
    }

    public function test_customer_cannot_create_an_order_for_another_users_shipping_address(): void
    {
        [$user] = $this->createCheckoutData();
        $otherUser = $this->createCustomer('other-checkout-customer@example.test');
        $otherAddress = ShippingAddress::create([
            'user_id' => $otherUser->id,
            'full_name' => $otherUser->name,
            'phone' => '0900000001',
            'address' => '2 Example Street',
            'city' => 'Ho Chi Minh City',
            'default' => true,
        ]);

        $response = $this->actingAs($user)->from(route('checkout'))
            ->post(route('checkout.placeOrder'), [
                'address_id' => $otherAddress->id,
                'payment_method' => 'cash',
            ]);

        $response->assertSessionHasErrors('address_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_can_poll_a_pending_sepay_payment(): void
    {
        $this->configureSePay();
        $user = $this->createCustomer();
        [$order, $payment] = $this->createPendingSePayOrder($user);
        Http::preventStrayRequests();
        Http::fake([
            'pgapi-sandbox.sepay.vn/v1/order/detail/*' => Http::response([
                'data' => $this->sePayOrderResponse($payment->transaction_id, 'AUTHENTICATION_NOT_NEEDED'),
            ]),
        ]);

        $response = $this->actingAs($user)->post(route('checkout.sepay.status', $order->id));

        $response->assertOk()
            ->assertJsonPath('status', 'pending');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
            'transaction_id' => $payment->transaction_id,
        ]);
        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'GET'
            && $request->url() === 'https://pgapi-sandbox.sepay.vn/v1/order/detail/'.$payment->transaction_id
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('sandbox-merchant:sandbox-secret')));
    }

    public function test_polling_marks_the_order_paid_only_after_a_matching_captured_transaction(): void
    {
        $this->configureSePay();
        $user = $this->createCustomer();
        [$order, $payment] = $this->createPendingSePayOrder($user);
        Http::preventStrayRequests();
        Http::fake([
            'pgapi-sandbox.sepay.vn/v1/order/detail/*' => Http::response([
                'data' => $this->sePayOrderResponse($payment->transaction_id, 'CAPTURED', [
                    [
                        'id' => 'sepay-transaction-uuid',
                        'transaction_id' => 'sepay-transaction-123',
                        'transaction_type' => 'PAYMENT',
                        'transaction_status' => 'APPROVED',
                        'transaction_amount' => '50000',
                        'transaction_currency' => 'VND',
                    ],
                ]),
            ]),
        ]);

        $response = $this->actingAs($user)->post(route('checkout.sepay.status', $order->id));

        $response->assertOk()
            ->assertJsonPath('status', 'completed');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
            'transaction_id' => 'sepay-transaction-123',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }

    public function test_sepay_return_checks_the_provider_before_marking_the_order_paid(): void
    {
        $this->configureSePay();
        $user = $this->createCustomer();
        [$order, $payment] = $this->createPendingSePayOrder($user);
        Http::preventStrayRequests();
        Http::fake([
            'pgapi-sandbox.sepay.vn/v1/order/detail/*' => Http::response([
                'data' => $this->sePayOrderResponse($payment->transaction_id, 'CAPTURED', [
                    [
                        'transaction_type' => 'PAYMENT',
                        'transaction_status' => 'APPROVED',
                        'transaction_amount' => '50000',
                        'transaction_currency' => 'VND',
                    ],
                ]),
            ]),
        ]);

        $response = $this->actingAs($user)->get(route('checkout.sepay.return', [
            'orderId' => $order->id,
            'result' => 'success',
        ]));

        $response->assertRedirect(route('account'));
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }

    public function test_customer_cannot_return_from_sepay_to_another_users_order(): void
    {
        $this->configureSePay();
        $user = $this->createCustomer();
        $otherUser = $this->createCustomer('other-return-customer@example.test');
        [$order] = $this->createPendingSePayOrder($otherUser);
        Http::preventStrayRequests();

        $response = $this->actingAs($user)->get(route('checkout.sepay.return', [
            'orderId' => $order->id,
            'result' => 'success',
        ]));

        $response->assertNotFound();
    }

    public function test_polling_does_not_confirm_a_captured_order_with_a_wrong_amount(): void
    {
        $this->configureSePay();
        $user = $this->createCustomer();
        [$order, $payment] = $this->createPendingSePayOrder($user);
        $providerOrder = $this->sePayOrderResponse($payment->transaction_id, 'CAPTURED', [
            [
                'transaction_type' => 'PAYMENT',
                'transaction_status' => 'APPROVED',
                'transaction_amount' => '49999',
                'transaction_currency' => 'VND',
            ],
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'pgapi-sandbox.sepay.vn/v1/order/detail/*' => Http::response([
                'data' => $providerOrder,
            ]),
        ]);

        $response = $this->actingAs($user)->post(route('checkout.sepay.status', $order->id));

        $response->assertOk()
            ->assertJsonPath('status', 'pending');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_customer_cannot_poll_another_users_sepay_payment(): void
    {
        $this->configureSePay();
        $user = $this->createCustomer();
        $otherUser = $this->createCustomer('other-customer@example.test');
        [$order] = $this->createPendingSePayOrder($otherUser);
        Http::preventStrayRequests();

        $response = $this->actingAs($user)->post(route('checkout.sepay.status', $order->id));

        $response->assertNotFound();
    }

    public function test_polling_reports_provider_connection_errors_without_changing_payment(): void
    {
        $this->configureSePay();
        $user = $this->createCustomer();
        [$order, $payment] = $this->createPendingSePayOrder($user);
        Http::preventStrayRequests();
        Http::fake([
            'pgapi-sandbox.sepay.vn/v1/order/detail/*' => Http::failedConnection(),
        ]);

        $response = $this->actingAs($user)->post(route('checkout.sepay.status', $order->id));

        $response->assertServiceUnavailable()
            ->assertJsonPath('status', 'unavailable');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
        ]);
    }

    public function test_polling_marks_a_cancelled_sepay_order_as_failed(): void
    {
        $this->configureSePay();
        $user = $this->createCustomer();
        [$order, $payment] = $this->createPendingSePayOrder($user);
        Http::preventStrayRequests();
        Http::fake([
            'pgapi-sandbox.sepay.vn/v1/order/detail/*' => Http::response([
                'data' => $this->sePayOrderResponse($payment->transaction_id, 'CANCELLED'),
            ]),
        ]);

        $response = $this->actingAs($user)->post(route('checkout.sepay.status', $order->id));

        $response->assertOk()
            ->assertJsonPath('status', 'failed');
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    private function configureSePay(): void
    {
        Config::set([
            'services.sepay.merchant_id' => 'sandbox-merchant',
            'services.sepay.secret_key' => 'sandbox-secret',
            'services.sepay.environment' => 'sandbox',
        ]);
    }

    private function createCustomer(string $email = 'checkout-customer@example.test'): User
    {
        $role = Role::firstOrCreate(['name' => 'customer']);

        return User::create([
            'name' => 'Checkout Customer',
            'email' => $email,
            'password' => Hash::make('password'),
            'status' => 'active',
            'role_id' => $role->id,
        ]);
    }

    /**
     * @return array{User, ShippingAddress, Product}
     */
    private function createCheckoutData(): array
    {
        $user = $this->createCustomer();
        $address = ShippingAddress::create([
            'user_id' => $user->id,
            'full_name' => 'Checkout Customer',
            'phone' => '0900000000',
            'address' => '1 Example Street',
            'city' => 'Ho Chi Minh City',
            'default' => true,
        ]);
        $category = Category::create([
            'name' => 'Vegetables',
            'slug' => 'vegetables',
        ]);
        $product = Product::create([
            'name' => 'Spinach',
            'slug' => 'spinach',
            'category_id' => $category->id,
            'price' => 25000,
            'stock' => 10,
            'status' => 'in_stock',
            'unit_quantity' => 1,
            'unit' => 'bunch',
        ]);
        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        return [$user, $address, $product];
    }

    /**
     * @return array{Order, Payment}
     */
    private function createPendingSePayOrder(User $user): array
    {
        $address = ShippingAddress::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'phone' => '0900000000',
            'address' => '1 Example Street',
            'city' => 'Ho Chi Minh City',
            'default' => true,
        ]);
        $order = Order::create([
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
            'total_price' => 50000,
            'status' => 'pending',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'sepay',
            'transaction_id' => 'LVTN-'.$order->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        return [$order, $payment];
    }

    /**
     * @param  list<array<string, string>>  $transactions
     * @return array<string, mixed>
     */
    private function sePayOrderResponse(
        string $invoiceNumber,
        string $status,
        array $transactions = [],
    ): array {
        return [
            'order_invoice_number' => $invoiceNumber,
            'order_status' => $status,
            'order_amount' => '50000.00',
            'order_currency' => 'VND',
            'transactions' => $transactions,
        ];
    }
}
