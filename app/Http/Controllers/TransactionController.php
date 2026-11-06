<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Valida e cria um lançamento financeiro.
     */
    public function store(Request $request): RedirectResponse
    {
        // Usa apenas os dados que passaram pelas regras definidas abaixo.
        $validated = $request->validate($this->rules($request), $this->messages());

        Transaction::query()->create($validated);

        // Mantém a pessoa no mesmo período e nos mesmos filtros depois de salvar.
        return to_route('dashboard', $this->dashboardFilters($request))
            ->with('status', 'Lançamento adicionado.');
    }

    /**
     * Atualiza um lançamento existente com os mesmos critérios de validação da criação.
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        // Aplica as mesmas regras da criação para não aceitar dados diferentes na edição.
        $validated = $request->validate($this->rules($request), $this->messages());

        $transaction->update($validated);

        return to_route('dashboard', $this->dashboardFilters($request))
            ->with('status', 'Lançamento atualizado.');
    }

    /**
     * Move o lançamento para a lixeira e guarda o identificador para permitir desfazer.
     */
    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        // O modelo usa exclusão lógica: o registro some das consultas comuns, mas pode ser recuperado.
        $transaction->delete();

        // Guarda qual registro foi removido para o botão "Desfazer" funcionar nesta sessão.
        $request->session()->put('undo_transaction_id', $transaction->id);

        return to_route('dashboard', $this->dashboardFilters($request))
            ->with('status', 'Lançamento removido.')
            ->with('undo_transaction_available', true);
    }

    /**
     * Restaura apenas o lançamento cuja remoção foi registrada na sessão atual.
     */
    public function restore(Request $request, int $transaction): RedirectResponse
    {
        // Impede que a ação de desfazer restaure um registro diferente do removido nesta sessão.
        if ((int) $request->session()->get('undo_transaction_id') !== $transaction) {
            abort(404);
        }

        // SoftDeletes esconde o registro normalmente; withTrashed permite encontrá-lo para restauração.
        $deletedTransaction = Transaction::withTrashed()->findOrFail($transaction);
        $deletedTransaction->restore();

        // Limpa a permissão de desfazer antiga para não restaurar o mesmo item outra vez.
        $request->session()->forget('undo_transaction_id');

        return to_route('dashboard', $this->dashboardFilters($request))
            ->with('status', 'Lançamento restaurado.')
            ->with('redo_transaction_id', $transaction);
    }

    /**
     * Regras compartilhadas por criação e edição para manter os mesmos limites de entrada.
     *
     * @return array<string, array<int, mixed>>
     */
    private function rules(Request $request): array
    {
        return [
            // Descrição curta, valor positivo com até duas casas e tipo conhecido.
            'description' => ['required', 'string', 'min:3', 'max:100'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'date' => ['required', 'date_format:Y-m-d'],
            // A categoria precisa existir e pertencer ao mesmo tipo do lançamento.
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
     * Mensagens em português para erros de validação dos campos do lançamento.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'description.required' => 'Informe a descrição do lançamento.',
            'description.min' => 'A descrição deve ter pelo menos 3 caracteres.',
            'description.max' => 'A descrição pode ter no máximo 100 caracteres.',
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
     * Conserva os filtros do painel nos redirecionamentos após as operações.
     *
     * @return array<string, string>
     */
    private function dashboardFilters(Request $request): array
    {
        // Remove parâmetros vazios para não criar URLs com filtros sem valor.
        return array_filter([
            'month' => $request->query('month'),
            'type' => $request->query('type'),
            'category' => $request->query('category'),
        ], fn (?string $value): bool => $value !== null && $value !== '');
    }
}
