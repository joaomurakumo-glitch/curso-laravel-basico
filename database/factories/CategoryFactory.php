<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Valores padrão usados pelos testes quando a categoria não exige um cenário especial.
        return [
            'name' => fake()->unique()->word(),
            'type' => 'expense',
            'color' => '#687B68',
        ];
    }
}
