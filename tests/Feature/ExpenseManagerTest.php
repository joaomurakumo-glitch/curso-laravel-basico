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
            'description' => 'Projeto de identidade visual',
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
            'description' => 'Projeto atualizado',
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
            ->assertSessionHas('status', 'Lançamento removido.');

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_rejects_a_transaction_category_with_a_different_type(): void
    {
        $category = Category::factory()->create([
            'name' => 'Alimentação',
            'type' => 'expense',
        ]);

        $response = $this->from(route('dashboard', ['month' => '2026-05']))
            ->post(route('transactions.store', ['month' => '2026-05']), [
                'description' => 'Entrada incompatível',
                'amount' => '100.00',
                'type' => 'income',
                'date' => '2026-05-14',
                'category_id' => $category->id,
            ]);

        $response->assertRedirectBack()
            ->assertSessionHasErrors([
                'category_id' => 'Escolha uma categoria compatível com o tipo do lançamento.',
            ]);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_creates_categories_and_rejects_duplicate_names_for_the_same_type(): void
    {
        $response = $this->post(route('categories.store', ['month' => '2026-05']), [
            'name' => 'Pets',
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
}
