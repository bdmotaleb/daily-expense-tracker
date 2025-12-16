<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();
        $month = $request->query('month') ?: now()->format('Y-m');

        $startOfMonth = $month . '-01';
        $endOfMonth = date('Y-m-t', strtotime($startOfMonth));

        // Today
        $todayIncome = (float) Income::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->sum('amount');

        $todayExpense = (float) Expense::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->sum('amount');

        // Monthly
        $monthlyIncome = (float) Income::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyExpense = (float) Expense::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Expense by category (for month)
        $categoryTotals = Expense::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $categories = Category::whereIn('id', $categoryTotals->keys())
            ->get()
            ->keyBy('id');

        $expenseByCategory = $categoryTotals->map(function ($row, $categoryId) use ($categories) {
            $category = $categories->get($categoryId);

            return [
                'category_id' => (int) $categoryId,
                'name' => $category?->name ?? 'Uncategorized',
                'color' => $category?->color,
                'amount' => (float) $row->total,
            ];
        })->values();

        // Daily expense (for month)
        $dailyExpense = Expense::select(DB::raw('DATE(date) as day'), DB::raw('SUM(amount) as total'))
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('day')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->day,
                    'total' => (float) $row->total,
                ];
            });

        return response()->json([
            'month' => $month,
            'today' => [
                'income' => $todayIncome,
                'expense' => $todayExpense,
                'balance' => $todayIncome - $todayExpense,
            ],
            'monthly' => [
                'income' => $monthlyIncome,
                'expense' => $monthlyExpense,
                'balance' => $monthlyIncome - $monthlyExpense,
            ],
            'expense_by_category' => $expenseByCategory,
            'daily_expense' => $dailyExpense,
        ]);
    }
}


