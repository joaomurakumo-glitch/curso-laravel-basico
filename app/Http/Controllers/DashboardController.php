<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Aplica os filtros do painel e prepara os totais e lançamentos do período.
     */
    public function index(Request $request): View
    {
        // A validação evita datas fora do formato esperado e filtros que não são permitidos.
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'type' => ['nullable', Rule::in(['income', 'expense'])],
            'category' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'start_date' => ['nullable', 'required_with:end_date', 'date_format:Y-m-d'],
            'end_date' => [
                'nullable',
                'required_with:start_date',
                'date_format:Y-m-d',
                Rule::when($request->filled('start_date'), ['after_or_equal:start_date']),
            ],
        ], [
            'start_date.required_with' => 'Informe a data inicial para filtrar o período.',
            'start_date.date_format' => 'Informe uma data inicial válida.',
            'end_date.required_with' => 'Informe a data final para filtrar o período.',
            'end_date.date_format' => 'Informe uma data final válida.',
            'end_date.after_or_equal' => 'A data final não pode ser anterior à data inicial.',
        ]);

        // Se nenhum mês vier na URL, usa o mês atual como padrão.
        $month = $filters['month'] ?? now()->format('Y-m');
        $monthStart = CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        // Sem um intervalo completo, o painel considera o mês inteiro, do primeiro ao último dia.
        $periodStart = $filters['start_date'] ?? $monthStart->toDateString();
        $periodEnd = $filters['end_date'] ?? $monthEnd->toDateString();
        $isDateRange = isset($filters['start_date'], $filters['end_date']);

        // O texto exibido no painel acompanha o modo escolhido: mês ou intervalo de datas.
        $periodLabel = $isDateRange
            ? CarbonImmutable::createFromFormat('Y-m-d', $periodStart)->format('d/m/Y')
                .' até '.CarbonImmutable::createFromFormat('Y-m-d', $periodEnd)->format('d/m/Y')
            : $monthStart->locale('pt_BR')->translatedFormat('F Y');

        // Esta consulta-base permite calcular listagem e indicadores sobre o mesmo período.
        $periodTransactions = Transaction::query()
            ->whereDate('date', '>=', $periodStart)
            ->whereDate('date', '<=', $periodEnd);

        // A cópia permite filtrar a tabela sem mudar a consulta-base usada nos totais gerais.
        $transactionsQuery = (clone $periodTransactions)->with('category');

        // Estes filtros refinam somente a lista de lançamentos.
        if (isset($filters['type'])) {
            $transactionsQuery->where('type', $filters['type']);
        }

        if (isset($filters['category'])) {
            $transactionsQuery->where('category_id', $filters['category']);
        }

        // Mostra primeiro os registros mais recentes e limita a tela a 8 por página.
        $transactions = $transactionsQuery
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        // O banco soma os valores diretamente, sem precisar carregar cada lançamento para o PHP.
        $incomeTotal = (clone $periodTransactions)->where('type', 'income')->sum('amount');
        $expenseTotal = (clone $periodTransactions)->where('type', 'expense')->sum('amount');
        $monthTransactionCount = (clone $periodTransactions)->count();

        // Agrupa despesas por categoria, soma seus valores e seleciona as quatro mais altas.
        $topExpenseCategories = (clone $periodTransactions)
            ->where('type', 'expense')
            ->select('category_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->limit(4)
            ->get();

        // A view recebe valores prontos; assim, ela cuida da apresentação e não das consultas.
        return view('dashboard', [
            'month' => $month,
            'monthLabel' => $periodLabel,
            'startDate' => $filters['start_date'] ?? '',
            'endDate' => $filters['end_date'] ?? '',
            'categories' => Category::query()->orderBy('name')->get()->groupBy('type'),
            'allCategories' => Category::query()->orderBy('name')->get(),
            'transactions' => $transactions,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'balanceTotal' => $incomeTotal - $expenseTotal,
            'monthTransactionCount' => $monthTransactionCount,
            'topExpenseCategories' => $topExpenseCategories,
            'typeFilter' => $filters['type'] ?? '',
            'categoryFilter' => $filters['category'] ?? '',
            'dateRangeApplied' => $isDateRange,
        ]);
    }
}
