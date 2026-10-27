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
        ]);

        $month = $filters['month'] ?? now()->format('Y-m');
        $monthStart = CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $monthTransactions = Transaction::query()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

        $transactionsQuery = (clone $monthTransactions)->with('category');

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

        $incomeTotal = (clone $monthTransactions)->where('type', 'income')->sum('amount');
        $expenseTotal = (clone $monthTransactions)->where('type', 'expense')->sum('amount');
        $monthTransactionCount = (clone $monthTransactions)->count();

        $topExpenseCategories = (clone $monthTransactions)
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
            'monthLabel' => $monthStart->locale('pt_BR')->translatedFormat('F Y'),
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
        ]);
    }
}
