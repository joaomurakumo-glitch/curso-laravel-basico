<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMovementController extends Controller
{
    public function store(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        DB::transaction(function () use ($inventoryItem, $validated): void {
            $lockedItem = InventoryItem::query()
                ->lockForUpdate()
                ->findOrFail($inventoryItem->id);

            if ($validated['quantity'] > $lockedItem->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Não há estoque suficiente para essa saída.',
                ]);
            }

            $totalCents = $this->calculateTotalCents(
                (string) $lockedItem->unit_price,
                $validated['quantity'],
                $validated['discount_percent'],
            );

            $lockedItem->decrement('stock_quantity', $validated['quantity']);

            $lockedItem->movements()->create([
                'quantity' => $validated['quantity'],
                'unit_price' => $lockedItem->unit_price,
                'discount_percent' => number_format((float) $validated['discount_percent'], 2, '.', ''),
                'total' => $this->formatCents($totalCents),
            ]);
        }, attempts: 3);

        return to_route('inventory.items.index')
            ->with('status', 'Saída registrada e estoque atualizado.');
    }

    public function update(
        Request $request,
        InventoryItem $inventoryItem,
        int $movement,
    ): RedirectResponse {
        $validated = $request->validate($this->rules(), $this->messages());

        DB::transaction(function () use ($inventoryItem, $movement, $validated): void {
            $lockedItem = InventoryItem::query()
                ->lockForUpdate()
                ->findOrFail($inventoryItem->id);
            $lockedMovement = $lockedItem->movements()
                ->lockForUpdate()
                ->findOrFail($movement);
            $availableStock = $lockedItem->stock_quantity + $lockedMovement->quantity;

            if ($validated['quantity'] > $availableStock) {
                throw ValidationException::withMessages([
                    'quantity' => 'Não há estoque suficiente para essa saída.',
                ]);
            }

            $totalCents = $this->calculateTotalCents(
                (string) $lockedMovement->unit_price,
                $validated['quantity'],
                $validated['discount_percent'],
            );

            $lockedItem->stock_quantity = $availableStock - $validated['quantity'];
            $lockedItem->save();
            $lockedMovement->update([
                'quantity' => $validated['quantity'],
                'discount_percent' => number_format((float) $validated['discount_percent'], 2, '.', ''),
                'total' => $this->formatCents($totalCents),
            ]);
        }, attempts: 3);

        return to_route('inventory.items.index')
            ->with('status', 'Saída atualizada e estoque recalculado.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'discount_percent' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'quantity.required' => 'Informe a quantidade de saída.',
            'quantity.integer' => 'A quantidade de saída deve ser um número inteiro.',
            'quantity.min' => 'A quantidade de saída deve ser maior que zero.',
            'quantity.max' => 'A quantidade de saída excede o limite permitido.',
            'discount_percent.required' => 'Informe o percentual de desconto.',
            'discount_percent.numeric' => 'O desconto deve ser numérico.',
            'discount_percent.decimal' => 'O desconto pode ter até duas casas decimais.',
            'discount_percent.min' => 'O desconto não pode ser negativo.',
            'discount_percent.max' => 'O desconto máximo permitido é 30%.',
        ];
    }

    private function calculateTotalCents(string $unitPrice, int $quantity, string|int|float $discountPercent): int
    {
        $unitPriceCents = (int) str_replace('.', '', $unitPrice);
        $maximumCents = 999999999999999999;

        if ($unitPriceCents > 0 && $quantity > intdiv($maximumCents, $unitPriceCents)) {
            throw ValidationException::withMessages([
                'quantity' => 'O valor total da saída excede o limite permitido.',
            ]);
        }

        $subtotalCents = $unitPriceCents * $quantity;
        $discountHundredths = (int) round((float) $discountPercent * 100);
        $discountFactor = 10000 - $discountHundredths;
        $subtotalWhole = intdiv($subtotalCents, 10000);
        $subtotalRemainder = $subtotalCents % 10000;

        return ($subtotalWhole * $discountFactor)
            + intdiv(($subtotalRemainder * $discountFactor) + 5000, 10000);
    }

    private function formatCents(int $amountCents): string
    {
        return intdiv($amountCents, 100).'.'.str_pad((string) ($amountCents % 100), 2, '0', STR_PAD_LEFT);
    }
}
