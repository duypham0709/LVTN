<?php

namespace Tests\Feature\Clients;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_redirect_to_google(): void
    {
        Socialite::fake('google');

        $response = $this->get(route('google.redirect'));

        $response->assertRedirect();
    }

    public function test_login_page_displays_the_google_sign_in_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee(route('google.redirect'));
    }

    public function test_verified_google_user_can_log_in_to_existing_customer_account(): void
    {
        $user = $this->createCustomer();
        Socialite::fake('google', $this->googleUser([
            'id' => 'google-customer-id',
            'email' => $user->email,
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'google-customer-id',
        ]);
    }

    public function test_google_id_can_log_in_to_an_already_linked_account(): void
    {
        $user = $this->createCustomer(googleId: 'already-linked-google-id');
        Socialite::fake('google', $this->googleUser([
            'id' => 'already-linked-google-id',
            'email' => 'changed-google-email@example.test',
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'already-linked-google-id',
        ]);
    }

    public function test_verified_google_user_can_create_a_customer_account(): void
    {
        $role = Role::create(['name' => 'customer']);
        Socialite::fake('google', $this->googleUser([
            'id' => 'new-google-id',
            'name' => 'New Google Customer',
            'email' => 'new-customer@example.test',
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'New Google Customer',
            'email' => 'new-customer@example.test',
            'google_id' => 'new-google-id',
            'status' => 'active',
            'role_id' => $role->id,
        ]);
    }

    public function test_google_login_does_not_create_an_account_for_an_unverified_email(): void
    {
        Role::create(['name' => 'customer']);
        Socialite::fake('google', $this->googleUser([
            'id' => 'unverified-new-google-id',
            'email' => 'unverified-new-customer@example.test',
            'email_verified' => false,
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_login_does_not_create_an_account_when_customer_role_is_missing(): void
    {
        Socialite::fake('google', $this->googleUser([
            'id' => 'missing-role-google-id',
            'email' => 'missing-role@example.test',
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_login_rejects_an_unverified_email(): void
    {
        $user = $this->createCustomer();
        Socialite::fake('google', $this->googleUser([
            'id' => 'unverified-google-id',
            'email' => $user->email,
            'email_verified' => false,
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => null,
        ]);
    }

    public function test_google_login_rejects_an_inactive_customer(): void
    {
        $user = $this->createCustomer('pending');
        Socialite::fake('google', $this->googleUser([
            'id' => 'pending-google-id',
            'email' => $user->email,
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => null,
        ]);
    }

    public function test_google_login_rejects_a_non_customer_account(): void
    {
        $user = $this->createCustomer(roleName: 'admin');
        Socialite::fake('google', $this->googleUser([
            'id' => 'admin-google-id',
            'email' => $user->email,
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => null,
        ]);
    }

    public function test_google_login_rejects_conflicting_google_id_and_email_accounts(): void
    {
        $googleIdOwner = $this->createCustomer(googleId: 'already-linked-google-id');
        $emailOwner = $this->createCustomer();
        Socialite::fake('google', $this->googleUser([
            'id' => 'already-linked-google-id',
            'email' => $emailOwner->email,
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $googleIdOwner->id,
            'google_id' => 'already-linked-google-id',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $emailOwner->id,
            'google_id' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function googleUser(array $attributes): SocialiteUser
    {
        return SocialiteUser::fake(array_merge([
            'email_verified' => true,
        ], $attributes));
    }

    private function createCustomer(
        string $status = 'active',
        string $roleName = 'customer',
        ?string $googleId = null,
    ): User {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::create([
            'name' => 'Google Login User',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'status' => $status,
            'role_id' => $role->id,
            'google_id' => $googleId,
        ]);
    }
}
