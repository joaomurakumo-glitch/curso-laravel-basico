<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_the_email_and_password_fields(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_invalid_credentials_do_not_start_a_session(): void
    {
        $this->post(route('login.store'), [
            'email' => 'ana@example.com',
            'password' => 'senha-incorreta',
        ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'email' => 'E-mail ou senha incorretos.',
            ]);

        $this->assertGuest();
    }

    public function test_valid_credentials_start_a_session_and_redirect_to_the_account(): void
    {
        $user = User::factory()->create([
            'password' => 'senha-segura-123',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'senha-segura-123',
        ])
            ->assertRedirectToRoute('account.show');

        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_ends_the_authenticated_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirectToRoute('login');

        $this->assertGuest();
    }
}
