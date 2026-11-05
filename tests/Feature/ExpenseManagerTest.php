<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_summarizes_the_month_and_filters_the_transaction_list(): void
    {
        $salary = Category::factory()->create([
            'name' => 'Salário',
            'type' => 'income',
        ]);
        $food = Category::factory()->create([
            'name' => 'Alimentação',
            'type' => 'expense',
        ]);
        $utilities = Category::factory()->create([
            'name' => 'Contas',
            'type' => 'expense',
        ]);

        Transaction::factory()->for($salary)->create([
            'description' => 'Salário mensal',
            'amount' => 4500,
            'type' => 'income',
            'date' => '2026-05-01',
        ]);
        Transaction::factory()->for($food)->create([
            'description' => 'Supermercado',
            'amount' => 210,
            'type' => 'expense',
            'date' => '2026-05-10',
        ]);
        Transaction::factory()->for($utilities)->create([
            'description' => 'Conta de luz',
            'amount' => 130,
            'type' => 'expense',
            'date' => '2026-05-12',
        ]);
        Transaction::factory()->for($food)->create([
            'description' => 'Compra de abril',
            'amount' => 90,
            'type' => 'expense',
            'date' => '2026-04-29',
        ]);

        $response = $this->get(route('dashboard', [
            'month' => '2026-05',
            'type' => 'expense',
            'category' => $food->id,
        ]));

        $response->assertOk()
            ->assertSee('Supermercado')
            ->assertDontSee('Conta de luz')
            ->assertDontSee('Compra de abril')
            ->assertSee('R$ 4.500,00')
            ->assertSee('R$ 340,00')
            ->assertSee('R$ 4.160,00')
            ->assertSee('3 lançamentos no período');
    }

    public function test_escapes_transaction_descriptions_in_the_dashboard(): void
    {
        $category = Category::factory()->create([
            'name' => 'Lazer',
            'type' => 'expense',
        ]);

        Transaction::factory()->for($category)->create([
            'description' => '<script>alert(1)</script>',
            'type' => 'expense',
            'date' => '2026-05-12',
        ]);

        $response = $this->get(route('dashboard', ['month' => '2026-05']));

        $response->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_creates_updates_and_removes_a_transaction(): void
    {
        $category = Category::factory()->create([
            'name' => 'Freelance',
            'type' => 'income',
        ]);

        $response = $this->post(route('transactions.store', ['month' => '2026-05']), [
            'description' => '  Projeto de identidade visual  ',
            'amount' => '1250.00',
            'type' => 'income',
            'date' => '2026-05-14',
            'category_id' => $category->id,
        ]);

        $response->assertRedirectToRoute('dashboard', ['month' => '2026-05'])
            ->assertSessionHas('status', 'Lançamento adicionado.');

        $transaction = Transaction::query()->sole();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => 'Projeto de identidade visual',
            'amount' => '1250.00',
            'category_id' => $category->id,
        ]);

        $this->put(route('transactions.update', [
            'transaction' => $transaction,
            'month' => '2026-05',
        ]), [
            'description' => '  Projeto atualizado  ',
            'amount' => '1400.00',
            'type' => 'income',
            'date' => '2026-05-15',
            'category_id' => $category->id,
        ])->assertRedirectToRoute('dashboard', ['month' => '2026-05'])
            ->assertSessionHas('status', 'Lançamento atualizado.');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => 'Projeto atualizado',
            'amount' => '1400.00',
        ]);

        $this->delete(route('transactions.destroy', [
            'transaction' => $transaction,
            'month' => '2026-05',
        ]))->assertRedirectToRoute('dashboard', ['month' => '2026-05'])
            ->assertSessionHas('status', 'Lançamento removido.')
            ->assertSessionHas('undo_transaction_id', $transaction->id);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
            'deleted_at' => null,
        ]);

        $this->get(route('dashboard', ['month' => '2026-05']))
            ->assertOk()
            ->assertSee('Desfazer');

        $this->post(route('transactions.restore', [
            'transaction' => $transaction->id,
            'month' => '2026-05',
        ]))->assertRedirectToRoute('dashboard', ['month' => '2026-05'])
            ->assertSessionHas('status', 'Lançamento restaurado.')
            ->assertSessionHas('redo_transaction_id', $transaction->id);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'deleted_at' => null,
        ]);

        $this->get(route('dashboard', ['month' => '2026-05']))
            ->assertOk()
            ->assertSee('Refazer');

        $this->delete(route('transactions.destroy', [
            'transaction' => $transaction->id,
            'month' => '2026-05',
        ]))->assertRedirectToRoute('dashboard', ['month' => '2026-05'])
            ->assertSessionHas('status', 'Lançamento removido.')
            ->assertSessionHas('undo_transaction_id', $transaction->id);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cannot_restore_a_transaction_without_its_undo_session(): void
    {
        $category = Category::factory()->create([
            'type' => 'expense',
        ]);
        $transaction = Transaction::factory()->for($category)->create();
        $transaction->delete();

        $this->post(route('transactions.restore', ['transaction' => $transaction->id]))
            ->assertNotFound();

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
            'deleted_at' => null,
        ]);
    }

    public function test_rejects_a_transaction_category_with_a_different_type(): void
    {
        $category = Category::factory()->create([
            'name' => 'Alimentação',
            'type' => 'expense',
        ]);

        $response = $this->followingRedirects()
            ->from(route('dashboard', ['month' => '2026-05']))
            ->post(route('transactions.store', ['month' => '2026-05']), [
                'description' => 'Entrada incompatível',
                'amount' => '100.00',
                'type' => 'income',
                'date' => '2026-05-14',
                'category_id' => $category->id,
            ]);

        $response->assertOk()
            ->assertSee('Escolha uma categoria compatível com o tipo do lançamento.')
            ->assertSee('Corrija os campos indicados')
            ->assertSee('transaction-category-error')
            ->assertSee('aria-invalid="true"', false);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_creates_categories_and_rejects_duplicate_names_for_the_same_type(): void
    {
        $response = $this->post(route('categories.store', ['month' => '2026-05']), [
            'name' => '  Pets  ',
            'type' => 'expense',
            'color' => '#66804f',
        ]);

        $response->assertRedirectToRoute('dashboard', ['month' => '2026-05'])
            ->assertSessionHas('status', 'Categoria criada.');

        $this->assertDatabaseHas('categories', [
            'name' => 'Pets',
            'type' => 'expense',
            'color' => '#66804f',
        ]);

        $response = $this->from(route('dashboard', ['month' => '2026-05']))
            ->post(route('categories.store', ['month' => '2026-05']), [
                'name' => 'Pets',
                'type' => 'expense',
                'color' => '#66804f',
            ]);

        $response->assertRedirectBack()
            ->assertSessionHasErrors([
                'name' => 'Já existe uma categoria com esse nome para esse tipo.',
            ]);

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_rejects_transaction_descriptions_shorter_than_three_characters(): void
    {
        $category = Category::factory()->create([
            'type' => 'expense',
        ]);

        $this->from(route('dashboard', ['month' => '2026-05']))
            ->post(route('transactions.store', ['month' => '2026-05']), [
                'description' => 'ab',
                'amount' => '10.00',
                'type' => 'expense',
                'date' => '2026-05-14',
                'category_id' => $category->id,
            ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'description' => 'A descrição deve ter pelo menos 3 caracteres.',
            ]);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_rejects_transaction_descriptions_containing_only_spaces(): void
    {
        $category = Category::factory()->create([
            'type' => 'expense',
        ]);

        $this->from(route('dashboard', ['month' => '2026-05']))
            ->post(route('transactions.store', ['month' => '2026-05']), [
                'description' => '   ',
                'amount' => '10.00',
                'type' => 'expense',
                'date' => '2026-05-14',
                'category_id' => $category->id,
            ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'description' => 'Informe a descrição do lançamento.',
            ]);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_rejects_transaction_descriptions_longer_than_one_hundred_characters_on_update(): void
    {
        $category = Category::factory()->create([
            'type' => 'expense',
        ]);
        $transaction = Transaction::factory()->for($category)->create();

        $this->from(route('dashboard', ['month' => '2026-05']))
            ->put(route('transactions.update', [
                'transaction' => $transaction,
                'month' => '2026-05',
            ]), [
                'description' => str_repeat('a', 101),
                'amount' => '10.00',
                'type' => 'expense',
                'date' => '2026-05-14',
                'category_id' => $category->id,
            ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'description' => 'A descrição pode ter no máximo 100 caracteres.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => $transaction->description,
        ]);
    }

    public function test_rejects_category_names_outside_the_three_to_one_hundred_character_range(): void
    {
        foreach ([
            'ab' => 'O nome da categoria deve ter pelo menos 3 caracteres.',
            str_repeat('a', 101) => 'O nome da categoria pode ter no máximo 100 caracteres.',
        ] as $name => $message) {
            $this->from(route('dashboard', ['month' => '2026-05']))
                ->post(route('categories.store', ['month' => '2026-05']), [
                    'name' => $name,
                    'type' => 'expense',
                    'color' => '#66804f',
                ])
                ->assertRedirectBack()
                ->assertSessionHasErrors([
                    'name' => $message,
                ]);
        }

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_rejects_category_names_containing_only_spaces(): void
    {
        $this->from(route('dashboard', ['month' => '2026-05']))
            ->post(route('categories.store', ['month' => '2026-05']), [
                'name' => '   ',
                'type' => 'expense',
                'color' => '#66804f',
            ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'name' => 'Informe o nome da categoria.',
            ]);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_rejects_invalid_transaction_amounts_without_storing_them(): void
    {
        $category = Category::factory()->create([
            'type' => 'expense',
        ]);

        foreach ([
            '-1.00' => 'O valor deve ser maior que zero.',
            '0' => 'O valor deve ser maior que zero.',
            '10.001' => 'O valor pode ter até duas casas decimais.',
            'abc' => 'O valor precisa ser numérico.',
            '10000000000.00' => 'O valor excede o limite permitido.',
        ] as $amount => $message) {
            $this->from(route('dashboard', ['month' => '2026-05']))
                ->post(route('transactions.store', ['month' => '2026-05']), [
                    'description' => 'Compra de mercado',
                    'amount' => $amount,
                    'type' => 'expense',
                    'date' => '2026-05-14',
                    'category_id' => $category->id,
                ])
                ->assertRedirectBack()
                ->assertSessionHasErrors([
                    'amount' => $message,
                ]);
        }

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_rejects_nonexistent_transaction_dates_without_storing_them(): void
    {
        $category = Category::factory()->create([
            'type' => 'expense',
        ]);

        foreach (['2026-02-31', '2026-13-01', '14/05/2026'] as $date) {
            $this->from(route('dashboard', ['month' => '2026-05']))
                ->post(route('transactions.store', ['month' => '2026-05']), [
                    'description' => 'Compra de mercado',
                    'amount' => '10.00',
                    'type' => 'expense',
                    'date' => $date,
                    'category_id' => $category->id,
                ])
                ->assertRedirectBack()
                ->assertSessionHasErrors([
                    'date' => 'Informe uma data válida.',
                ]);
        }

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_rejects_invalid_amount_and_date_on_update_without_changing_the_transaction(): void
    {
        $category = Category::factory()->create([
            'type' => 'expense',
        ]);
        $transaction = Transaction::factory()->for($category)->create([
            'description' => 'Compra original',
            'amount' => '25.00',
            'date' => '2026-05-10',
        ]);

        $this->from(route('dashboard', ['month' => '2026-05']))
            ->put(route('transactions.update', [
                'transaction' => $transaction,
                'month' => '2026-05',
            ]), [
                'description' => 'Compra atualizada',
                'amount' => '25.001',
                'type' => 'expense',
                'date' => '2026-02-31',
                'category_id' => $category->id,
            ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'amount' => 'O valor pode ter até duas casas decimais.',
                'date' => 'Informe uma data válida.',
            ]);

        $transaction->refresh();

        $this->assertSame('Compra original', $transaction->description);
        $this->assertSame('25.00', $transaction->amount);
        $this->assertSame('2026-05-10', $transaction->date->format('Y-m-d'));
    }
}
