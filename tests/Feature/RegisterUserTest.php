<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_displays_the_profile_fields(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('name="surname"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="cpf"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="age"', false);
    }

    public function test_valid_registration_creates_a_user_with_trimmed_profile_data(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => '  Ana  ',
            'surname' => '  Silva Santos  ',
            'address' => '  Rua das Flores, 123  ',
            'email' => '  ana@example.com  ',
            'cpf' => '529.982.247-25',
            'phone' => '(11) 91234-5678',
            'age' => '18',
            'password' => 'senha-segura-123',
            'password_confirmation' => 'senha-segura-123',
        ]);

        $response->assertRedirectToRoute('register')
            ->assertSessionHas('status', 'Cadastro realizado com sucesso.');

        $this->assertDatabaseHas('users', [
            'name' => 'Ana',
            'surname' => 'Silva Santos',
            'address' => 'Rua das Flores, 123',
            'email' => 'ana@example.com',
            'cpf' => '52998224725',
            'phone' => '11912345678',
            'age' => 18,
        ]);

        $user = User::query()->sole();

        $this->assertNotSame('senha-segura-123', $user->password);
        $this->assertTrue(Hash::check('senha-segura-123', $user->password));
    }

    public function test_registration_requires_all_profile_fields_and_password(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'name' => 'Informe seu nome.',
                'surname' => 'Informe seu sobrenome.',
                'address' => 'Informe seu endereço.',
                'email' => 'Informe seu e-mail.',
                'cpf' => 'Informe seu CPF.',
                'phone' => 'Informe seu telefone celular.',
                'age' => 'Informe sua idade.',
                'password' => 'Informe uma senha.',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_text_fields_outside_the_three_to_one_hundred_character_range(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'ab',
                'surname' => str_repeat('a', 101),
                'address' => '   ',
                'email' => 'ana@example.com',
                'age' => '30',
                'password' => 'senha-segura-123',
                'password_confirmation' => 'senha-segura-123',
            ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'name' => 'O nome deve ter pelo menos 3 caracteres.',
                'surname' => 'O sobrenome pode ter no máximo 100 caracteres.',
                'address' => 'Informe seu endereço.',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_an_invalid_email_address(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), $this->validRegistrationData([
                'email' => 'nao-e-um-email',
            ]))
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'email' => 'Informe um endereço de e-mail válido.',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_an_email_that_is_already_registered(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'ana@example.com',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), $this->validRegistrationData([
                'email' => $existingUser->email,
            ]))
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'email' => 'Este e-mail já está cadastrado.',
            ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_accepts_masked_and_unmasked_cpfs_and_stores_only_digits(): void
    {
        foreach ([
            'ana@example.com' => ['cpf' => '52998224725', 'stored' => '52998224725'],
            'bia@example.com' => ['cpf' => '111.444.777-35', 'stored' => '11144477735'],
        ] as $email => $cpf) {
            $this->post(route('register.store'), $this->validRegistrationData([
                'email' => $email,
                'cpf' => $cpf['cpf'],
            ]))->assertRedirectToRoute('register');

            $this->assertDatabaseHas('users', [
                'email' => $email,
                'cpf' => $cpf['stored'],
            ]);
        }

        $this->assertDatabaseCount('users', 2);
    }

    public function test_registration_rejects_cpfs_with_invalid_format_or_check_digits(): void
    {
        foreach ([
            '1234567890' => 'O CPF informado é inválido.',
            '5299822472a' => 'O CPF informado é inválido.',
            '529.982.247-24' => 'O CPF informado é inválido.',
            '111.111.111-11' => 'O CPF informado é inválido.',
            '22222222222' => 'O CPF informado é inválido.',
            '529.982247-25' => 'O CPF informado é inválido.',
        ] as $cpf => $message) {
            $this->from(route('register'))
                ->post(route('register.store'), $this->validRegistrationData([
                    'cpf' => $cpf,
                ]))
                ->assertRedirectBack()
                ->assertSessionHasErrors([
                    'cpf' => $message,
                ]);
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_a_duplicate_cpf_with_a_different_mask(): void
    {
        User::factory()->create([
            'cpf' => '52998224725',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), $this->validRegistrationData([
                'cpf' => '529.982.247-25',
            ]))
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'cpf' => 'Este CPF já está cadastrado.',
            ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_accepts_masked_and_unmasked_mobile_numbers(): void
    {
        foreach ([
            'ana@example.com' => [
                'cpf' => '52998224725',
                'phone' => '(11) 91234-5678',
            ],
            'bia@example.com' => [
                'cpf' => '11144477735',
                'phone' => '21987654321',
            ],
        ] as $email => $contact) {
            $this->post(route('register.store'), $this->validRegistrationData([
                'email' => $email,
                'cpf' => $contact['cpf'],
                'phone' => $contact['phone'],
            ]))->assertRedirectToRoute('register');

            $this->assertDatabaseHas('users', [
                'email' => $email,
                'phone' => preg_replace('/\D/', '', $contact['phone']),
            ]);
        }

        $this->assertDatabaseCount('users', 2);
    }

    public function test_registration_rejects_mobile_numbers_with_invalid_digits_or_format(): void
    {
        foreach ([
            '1191234567' => 'Informe um celular válido com DDD e 11 dígitos, começando com 9.',
            '11812345678' => 'Informe um celular válido com DDD e 11 dígitos, começando com 9.',
            '11a12345678' => 'Informe um celular válido com DDD e 11 dígitos, começando com 9.',
            '(11) 81234-5678' => 'Informe um celular válido com DDD e 11 dígitos, começando com 9.',
            '11 91234-5678' => 'Informe um celular válido com DDD e 11 dígitos, começando com 9.',
        ] as $phone => $message) {
            $this->from(route('register'))
                ->post(route('register.store'), $this->validRegistrationData([
                    'phone' => $phone,
                ]))
                ->assertRedirectBack()
                ->assertSessionHasErrors([
                    'phone' => $message,
                ]);
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_ages_outside_the_allowed_integer_range(): void
    {
        foreach ([
            '17' => 'A idade mínima permitida é 18 anos.',
            '101' => 'A idade máxima permitida é 100 anos.',
            '18.5' => 'A idade deve ser um número inteiro.',
            '-1' => 'A idade mínima permitida é 18 anos.',
            'abc' => 'A idade deve ser um número inteiro.',
        ] as $age => $message) {
            $this->from(route('register'))
                ->post(route('register.store'), $this->validRegistrationData([
                    'age' => $age,
                ]))
                ->assertRedirectBack()
                ->assertSessionHasErrors([
                    'age' => $message,
                ]);
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_a_password_confirmation_that_does_not_match(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), $this->validRegistrationData([
                'password_confirmation' => 'outra-senha-segura',
            ]))
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'password' => 'A confirmação da senha não corresponde.',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validRegistrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana',
            'surname' => 'Silva Santos',
            'address' => 'Rua das Flores, 123',
            'email' => 'ana@example.com',
            'cpf' => '52998224725',
            'phone' => '11912345678',
            'age' => '30',
            'password' => 'senha-segura-123',
            'password_confirmation' => 'senha-segura-123',
        ], $overrides);
    }
}
