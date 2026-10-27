<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    //
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:40',
                Rule::unique('categories', 'name')->where(
                    fn (Builder $query) => $query->where('type', $request->input('type')),
                ),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'color' => ['required', 'hex_color'],
        ], [
            'name.required' => 'Informe o nome da categoria.',
            'name.unique' => 'Já existe uma categoria com esse nome para esse tipo.',
            'type.required' => 'Selecione se a categoria é receita ou despesa.',
            'type.in' => 'Selecione um tipo de categoria válido.',
            'color.required' => 'Escolha uma cor para a categoria.',
            'color.hex_color' => 'Informe uma cor válida.',
        ]);

        Category::query()->create($validated);

        return to_route('dashboard', array_filter([
            'month' => $request->query('month'),
            'type' => $request->query('type'),
            'category' => $request->query('category'),
        ], fn (?string $value): bool => $value !== null && $value !== ''))
            ->with('status', 'Categoria criada.');
    }
}
