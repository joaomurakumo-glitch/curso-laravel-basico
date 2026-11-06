<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_page_lists_products_and_stock(): void
    {
        $item = InventoryItem::factory()->create([
            'name' => 'Café especial',
            'stock_quantity' => 12,
        ]);

        $this->get(route('inventory.items.index'))
            ->assertOk()
            ->assertSee('Café especial')
            ->assertSee('12')
            ->assertSee(route('inventory.movements.store', $item));
    }

    public function test_creates_an_inventory_item_with_a_nonnegative_integer_stock(): void
    {
        $this->post(route('inventory.items.store'), [
            'name' => '  Café especial  ',
            'stock_quantity' => '0',
            'unit_price' => '12.50',
        ])
            ->assertRedirectToRoute('inventory.items.index')
            ->assertSessionHas('status', 'Produto cadastrado no estoque.');

        $this->assertDatabaseHas('inventory_items', [
            'name' => 'Café especial',
            'stock_quantity' => 0,
            'unit_price' => '12.50',
        ]);
    }

    public function test_rejects_negative_or_noninteger_initial_stock(): void
    {
        foreach ([
            '-1' => 'A quantidade inicial não pode ser negativa.',
            '1.5' => 'A quantidade inicial deve ser um número inteiro.',
            'abc' => 'A quantidade inicial deve ser um número inteiro.',
        ] as $stock => $message) {
            $this->from(route('inventory.items.index'))
                ->post(route('inventory.items.store'), $this->validItemData([
                    'stock_quantity' => $stock,
                ]))
                ->assertRedirectBack()
                ->assertSessionHasErrors([
                    'stock_quantity' => $message,
                ]);
        }

        $this->assertDatabaseCount('inventory_items', 0);
    }

    public function test_registers_stock_outflow_and_calculates_discounted_total(): void
    {
        $item = InventoryItem::factory()->create([
            'stock_quantity' => 8,
            'unit_price' => '12.50',
        ]);

        $this->post(route('inventory.movements.store', $item), [
            'quantity' => '3',
            'discount_percent' => '10',
        ])
            ->assertRedirectToRoute('inventory.items.index')
            ->assertSessionHas('status', 'Saída registrada e estoque atualizado.');

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'stock_quantity' => 5,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'quantity' => 3,
            'unit_price' => '12.50',
            'discount_percent' => '10.00',
            'total' => '33.75',
        ]);
    }

    public function test_rejects_missing_invalid_and_excessive_outflow_or_discount_values(): void
    {
        $item = InventoryItem::factory()->create([
            'stock_quantity' => 5,
        ]);

        $this->from(route('inventory.items.index'))
            ->post(route('inventory.movements.store', $item), [])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'quantity' => 'Informe a quantidade de saída.',
                'discount_percent' => 'Informe o percentual de desconto.',
            ]);

        foreach ([
            'quantity' => [
                '0' => 'A quantidade de saída deve ser maior que zero.',
                '-1' => 'A quantidade de saída deve ser maior que zero.',
                '1.5' => 'A quantidade de saída deve ser um número inteiro.',
            ],
            'discount_percent' => [
                '-0.01' => 'O desconto não pode ser negativo.',
                '30.01' => 'O desconto máximo permitido é 30%.',
                'abc' => 'O desconto deve ser numérico.',
            ],
        ] as $field => $cases) {
            foreach ($cases as $value => $message) {
                $this->from(route('inventory.items.index'))
                    ->post(route('inventory.movements.store', $item), [
                        'quantity' => '1',
                        'discount_percent' => '0',
                        $field => $value,
                    ])
                    ->assertRedirectBack()
                    ->assertSessionHasErrors([
                        $field => $message,
                    ]);
            }
        }

        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'stock_quantity' => 5,
        ]);
    }

    public function test_rejects_an_outflow_larger_than_available_stock(): void
    {
        $item = InventoryItem::factory()->create([
            'stock_quantity' => 2,
        ]);

        $this->from(route('inventory.items.index'))
            ->post(route('inventory.movements.store', $item), [
                'quantity' => '3',
                'discount_percent' => '0',
            ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'quantity' => 'Não há estoque suficiente para essa saída.',
            ]);

        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'stock_quantity' => 2,
        ]);
    }

    public function test_updates_an_outflow_and_recalculates_stock_and_discount(): void
    {
        $item = InventoryItem::factory()->create([
            'stock_quantity' => 10,
            'unit_price' => '10.00',
        ]);

        $this->post(route('inventory.movements.store', $item), [
            'quantity' => '3',
            'discount_percent' => '10',
        ])->assertRedirectToRoute('inventory.items.index');

        $movement = InventoryMovement::query()->sole();

        $this->put(route('inventory.movements.update', [
            'inventoryItem' => $item,
            'movement' => $movement,
        ]), [
            'quantity' => '4',
            'discount_percent' => '30',
        ])
            ->assertRedirectToRoute('inventory.items.index')
            ->assertSessionHas('status', 'Saída atualizada e estoque recalculado.');

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'stock_quantity' => 6,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'id' => $movement->id,
            'quantity' => 4,
            'discount_percent' => '30.00',
            'total' => '28.00',
        ]);
    }

    public function test_rejects_an_invalid_outflow_update_without_changing_stock_or_movement(): void
    {
        $item = InventoryItem::factory()->create([
            'stock_quantity' => 5,
            'unit_price' => '10.00',
        ]);
        $this->post(route('inventory.movements.store', $item), [
            'quantity' => '4',
            'discount_percent' => '0',
        ])->assertRedirectToRoute('inventory.items.index');

        $movement = InventoryMovement::query()->sole();

        $this->from(route('inventory.items.index'))
            ->put(route('inventory.movements.update', [
                'inventoryItem' => $item,
                'movement' => $movement,
            ]), [
                'quantity' => '6',
                'discount_percent' => '0',
            ])
            ->assertRedirectBack()
            ->assertSessionHasErrors([
                'quantity' => 'Não há estoque suficiente para essa saída.',
            ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'stock_quantity' => 1,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'id' => $movement->id,
            'quantity' => 4,
            'total' => '40.00',
        ]);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validItemData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Café especial',
            'stock_quantity' => '20',
            'unit_price' => '12.50',
        ], $overrides);
    }
}
