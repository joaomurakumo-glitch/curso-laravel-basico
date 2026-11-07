<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_before_opening_the_account(): void
    {
        $this->get(route('account.show'))
            ->assertRedirectToRoute('login');
    }

    public function test_dashboard_shows_my_account_only_for_authenticated_users(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('account.show'), false);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('account.show'), false)
            ->assertSee('Minha conta');
    }

    public function test_account_requires_recent_password_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertRedirectToRoute('password.confirm');
    }

    public function test_correct_password_confirmation_displays_the_saved_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana',
            'surname' => 'Silva Santos',
            'address' => 'Rua das Flores, 123',
            'cep' => '01001000',
            'email' => 'ana@example.com',
            'cpf' => '52998224725',
            'phone' => '11912345678',
            'age' => 30,
            'password' => 'senha-atual-segura',
        ]);

        $this->actingAs($user)
            ->post(route('password.confirm.store'), [
                'password' => 'senha-atual-segura',
            ])
            ->assertRedirectToRoute('account.show');

        $this->get(route('account.show'))
            ->assertOk()
            ->assertSee('Minha conta')
            ->assertSee('Ana')
            ->assertSee('Silva Santos')
            ->assertSee('Rua das Flores, 123')
            ->assertSee('ana@example.com')
            ->assertSee('01001-000')
            ->assertSee('529.982.247-25')
            ->assertSee('11912345678')
            ->assertSee('30 anos')
            ->assertSee('name="cep"', false)
            ->assertSee('name="cpf"', false)
            ->assertSee('aria-readonly="true"', false);
    }

    public function test_wrong_password_does_not_confirm_access_to_the_account(): void
    {
        $user = User::factory()->create([
            'password' => 'senha-atual-segura',
        ]);

        $this->actingAs($user)
            ->post(route('password.confirm.store'), [
                'password' => 'senha-incorreta',
            ])
            ->assertSessionHasErrors([
                'password' => 'A senha informada está incorreta.',
            ]);

        $this->get(route('account.show'))
            ->assertRedirectToRoute('password.confirm');
    }

    public function test_password_change_requires_the_current_password_and_keeps_profile_fields_locked(): void
    {
        $user = User::factory()->create([
            'cep' => '01001000',
            'cpf' => '52998224725',
            'password' => 'senha-atual-segura',
        ]);

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => now()->unix()])
            ->put(route('account.password.update'), [
                'current_password' => 'senha-errada',
                'password' => 'nova-senha-segura',
                'password_confirmation' => 'nova-senha-segura',
                'cep' => '20000000',
                'cpf' => '11144477735',
            ])
            ->assertSessionHasErrors([
                'current_password' => 'A senha atual está incorreta.',
            ]);

        $user->refresh();

        $this->assertSame('01001000', $user->cep);
        $this->assertSame('52998224725', $user->cpf);
        $this->assertTrue(Hash::check('senha-atual-segura', $user->password));
    }

    public function test_valid_password_change_saves_a_hash_for_the_new_password(): void
    {
        $user = User::factory()->create([
            'password' => 'senha-atual-segura',
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'senha-atual-segura',
                'password' => 'nova-senha-segura',
                'password_confirmation' => 'nova-senha-segura',
            ])
            ->assertRedirectToRoute('account.show')
            ->assertSessionHas('status', 'Senha alterada com sucesso.');

        $this->assertTrue(Hash::check('nova-senha-segura', $user->fresh()->password));
    }
}
