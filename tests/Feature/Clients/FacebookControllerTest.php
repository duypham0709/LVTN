<?php

namespace Tests\Feature\Clients;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class FacebookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_redirect_to_facebook(): void
    {
        Socialite::fake('facebook');

        $response = $this->get(route('facebook.redirect'));

        $response->assertRedirect();
    }

    public function test_login_page_displays_facebook_sign_in_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee(route('facebook.redirect'));
    }

    public function test_facebook_user_can_create_and_log_in_to_customer_account(): void
    {
        $role = Role::create(['name' => 'customer']);
        Socialite::fake('facebook', $this->facebookUser([
            'id' => 'new-facebook-id',
            'name' => 'New Facebook Customer',
            'email' => 'new-facebook-customer@example.test',
        ]));

        $response = $this->get(route('facebook.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'New Facebook Customer',
            'email' => 'new-facebook-customer@example.test',
            'facebook_id' => 'new-facebook-id',
            'status' => 'active',
            'role_id' => $role->id,
        ]);
    }

    public function test_facebook_id_can_log_in_to_an_already_linked_account(): void
    {
        $user = $this->createCustomer(facebookId: 'already-linked-facebook-id');
        Socialite::fake('facebook', $this->facebookUser([
            'id' => 'already-linked-facebook-id',
            'email' => 'changed-facebook-email@example.test',
        ]));

        $response = $this->get(route('facebook.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'facebook_id' => 'already-linked-facebook-id',
        ]);
    }

    public function test_facebook_login_rejects_an_existing_email_without_linking_the_account(): void
    {
        $user = $this->createCustomer();
        Socialite::fake('facebook', $this->facebookUser([
            'id' => 'unlinked-facebook-id',
            'email' => $user->email,
        ]));

        $response = $this->get(route('facebook.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'facebook_id' => null,
        ]);
    }

    public function test_facebook_login_rejects_email_conflicting_with_another_facebook_account(): void
    {
        $facebookIdOwner = $this->createCustomer(facebookId: 'existing-facebook-id');
        $emailOwner = $this->createCustomer();
        Socialite::fake('facebook', $this->facebookUser([
            'id' => 'existing-facebook-id',
            'email' => $emailOwner->email,
        ]));

        $response = $this->get(route('facebook.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $facebookIdOwner->id,
            'facebook_id' => 'existing-facebook-id',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $emailOwner->id,
            'facebook_id' => null,
        ]);
    }

    public function test_facebook_login_does_not_create_an_account_when_customer_role_is_missing(): void
    {
        Socialite::fake('facebook', $this->facebookUser([
            'id' => 'missing-role-facebook-id',
            'email' => 'missing-role@example.test',
        ]));

        $response = $this->get(route('facebook.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_facebook_login_rejects_an_inactive_customer(): void
    {
        $user = $this->createCustomer(status: 'pending', facebookId: 'pending-facebook-id');
        Socialite::fake('facebook', $this->facebookUser([
            'id' => 'pending-facebook-id',
            'email' => $user->email,
        ]));

        $response = $this->get(route('facebook.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'facebook_id' => 'pending-facebook-id',
        ]);
    }

    public function test_facebook_login_rejects_a_non_customer_account(): void
    {
        $user = $this->createCustomer(roleName: 'admin', facebookId: 'admin-facebook-id');
        Socialite::fake('facebook', $this->facebookUser([
            'id' => 'admin-facebook-id',
            'email' => $user->email,
        ]));

        $response = $this->get(route('facebook.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'facebook_id' => 'admin-facebook-id',
        ]);
    }

    public function test_facebook_login_does_not_create_an_account_without_an_email(): void
    {
        Role::create(['name' => 'customer']);
        Socialite::fake('facebook', $this->facebookUser([
            'id' => 'facebook-without-email',
            'email' => null,
        ]));

        $response = $this->get(route('facebook.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function facebookUser(array $attributes): SocialiteUser
    {
        return SocialiteUser::fake($attributes);
    }

    private function createCustomer(
        string $status = 'active',
        string $roleName = 'customer',
        ?string $facebookId = null,
    ): User {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::create([
            'name' => 'Facebook Login User',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'status' => $status,
            'role_id' => $role->id,
            'facebook_id' => $facebookId,
        ]);
    }
}
