<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(): View
    {
        return view('inventory.index', [
            'items' => InventoryItem::query()
                ->with(['movements' => fn ($query) => $query->latest()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'unit_price' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
        ], [
            'name.required' => 'Informe o nome do produto.',
            'name.min' => 'O nome do produto deve ter pelo menos 3 caracteres.',
            'name.max' => 'O nome do produto pode ter no máximo 100 caracteres.',
            'stock_quantity.required' => 'Informe a quantidade inicial em estoque.',
            'stock_quantity.integer' => 'A quantidade inicial deve ser um número inteiro.',
            'stock_quantity.min' => 'A quantidade inicial não pode ser negativa.',
            'stock_quantity.max' => 'A quantidade inicial excede o limite permitido.',
            'unit_price.required' => 'Informe o preço unitário.',
            'unit_price.numeric' => 'O preço unitário deve ser numérico.',
            'unit_price.decimal' => 'O preço unitário pode ter até duas casas decimais.',
            'unit_price.gt' => 'O preço unitário deve ser maior que zero.',
            'unit_price.max' => 'O preço unitário excede o limite permitido.',
        ]);

        InventoryItem::query()->create($validated);

        return to_route('inventory.items.index')
            ->with('status', 'Produto cadastrado no estoque.');
    }
}
