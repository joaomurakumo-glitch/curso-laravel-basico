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
    public function index(Request $request): View
    {
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

        $month = $filters['month'] ?? now()->format('Y-m');
        $monthStart = CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $periodStart = $filters['start_date'] ?? $monthStart->toDateString();
        $periodEnd = $filters['end_date'] ?? $monthEnd->toDateString();
        $isDateRange = isset($filters['start_date'], $filters['end_date']);
        $periodLabel = $isDateRange
            ? CarbonImmutable::createFromFormat('Y-m-d', $periodStart)->format('d/m/Y')
                .' até '.CarbonImmutable::createFromFormat('Y-m-d', $periodEnd)->format('d/m/Y')
            : $monthStart->locale('pt_BR')->translatedFormat('F Y');

        $periodTransactions = Transaction::query()
            ->whereDate('date', '>=', $periodStart)
            ->whereDate('date', '<=', $periodEnd);

        $transactionsQuery = (clone $periodTransactions)->with('category');

        if (isset($filters['type'])) {
            $transactionsQuery->where('type', $filters['type']);
        }

        if (isset($filters['category'])) {
            $transactionsQuery->where('category_id', $filters['category']);
        }

        $transactions = $transactionsQuery
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        $incomeTotal = (clone $periodTransactions)->where('type', 'income')->sum('amount');
        $expenseTotal = (clone $periodTransactions)->where('type', 'expense')->sum('amount');
        $monthTransactionCount = (clone $periodTransactions)->count();

        $topExpenseCategories = (clone $periodTransactions)
            ->where('type', 'expense')
            ->select('category_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->limit(4)
            ->get();

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
