<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    public function test_authenticated_user_can_log_out_with_post(): void
    {
        $user = new User([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_logout_does_not_accept_get_requests(): void
    {
        $response = $this->get('/logout');

        $response->assertStatus(405);
    }
}
