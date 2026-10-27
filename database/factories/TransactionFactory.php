<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->words(3, true),
            'amount' => fake()->randomFloat(2, 1, 1000),
            'type' => 'expense',
            'date' => fake()->date(),
            'category_id' => Category::factory(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
