<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    //
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request), $this->messages());

        Transaction::query()->create($validated);

        return to_route('dashboard', $this->dashboardFilters($request))
            ->with('status', 'Lançamento adicionado.');
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate($this->rules($request), $this->messages());

        $transaction->update($validated);

        return to_route('dashboard', $this->dashboardFilters($request))
            ->with('status', 'Lançamento atualizado.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return to_route('dashboard', $this->dashboardFilters($request))
            ->with('status', 'Lançamento removido.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(Request $request): array
    {
        return [
            'description' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'date' => ['required', 'date_format:Y-m-d'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn (Builder $query) => $query->where('type', $request->input('type')),
                ),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'description.required' => 'Informe a descrição do lançamento.',
            'description.max' => 'A descrição pode ter no máximo 120 caracteres.',
            'amount.required' => 'Informe o valor.',
            'amount.numeric' => 'O valor precisa ser numérico.',
            'amount.decimal' => 'O valor pode ter até duas casas decimais.',
            'amount.gt' => 'O valor deve ser maior que zero.',
            'amount.max' => 'O valor excede o limite permitido.',
            'type.required' => 'Selecione se o lançamento é receita ou despesa.',
            'type.in' => 'Selecione um tipo de lançamento válido.',
            'date.required' => 'Informe a data do lançamento.',
            'date.date_format' => 'Informe uma data válida.',
            'category_id.required' => 'Selecione uma categoria.',
            'category_id.integer' => 'Selecione uma categoria válida.',
            'category_id.exists' => 'Escolha uma categoria compatível com o tipo do lançamento.',
            'notes.max' => 'A observação pode ter no máximo 500 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function dashboardFilters(Request $request): array
    {
        return array_filter([
            'month' => $request->query('month'),
            'type' => $request->query('type'),
            'category' => $request->query('category'),
        ], fn (?string $value): bool => $value !== null && $value !== '');
    }
}
