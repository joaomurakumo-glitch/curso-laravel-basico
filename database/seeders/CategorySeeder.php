<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorias iniciais para que o painel possa ser usado logo após a preparação do banco.
        $categories = [
            ['name' => 'Salário', 'type' => 'income', 'color' => '#477A62'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#5477A5'],
            ['name' => 'Investimentos', 'type' => 'income', 'color' => '#A77736'],
            ['name' => 'Outras receitas', 'type' => 'income', 'color' => '#78835D'],
            ['name' => 'Moradia', 'type' => 'expense', 'color' => '#816954'],
            ['name' => 'Alimentação', 'type' => 'expense', 'color' => '#66804F'],
            ['name' => 'Transporte', 'type' => 'expense', 'color' => '#5477A5'],
            ['name' => 'Saúde', 'type' => 'expense', 'color' => '#A65D58'],
            ['name' => 'Lazer', 'type' => 'expense', 'color' => '#A77736'],
            ['name' => 'Contas', 'type' => 'expense', 'color' => '#77769A'],
            ['name' => 'Outras despesas', 'type' => 'expense', 'color' => '#78838A'],
        ];

        // updateOrCreate evita duplicar uma categoria se o seeder for executado novamente.
        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                ['color' => $category['color']],
            );
        }
    }
}
