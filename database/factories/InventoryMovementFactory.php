<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'quantity' => 1,
            'unit_price' => '10.00',
            'discount_percent' => '0.00',
            'total' => '10.00',
        ];
    }
}
