<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
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

        // Overall totals (all time)
        $totalIncome = (float) Income::where('user_id', $user->id)->sum('amount');
        $totalExpense = (float) Expense::where('user_id', $user->id)->sum('amount');

        // Month-wise income & expense trend for the last 12 months
        $startPeriod = Carbon::now()->subMonths(11)->startOfMonth();
        $endPeriod = Carbon::now()->endOfMonth();

        $incomeByMonth = Income::select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as ym"),
            DB::raw('SUM(amount) as total')
        )
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startPeriod->toDateString(), $endPeriod->toDateString()])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $expenseByMonth = Expense::select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as ym"),
            DB::raw('SUM(amount) as total')
        )
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startPeriod->toDateString(), $endPeriod->toDateString()])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $monthlyTrend = [];

        for ($date = $startPeriod->copy(); $date <= $endPeriod; $date->addMonth()) {
            $key = $date->format('Y-m');

            $monthlyTrend[] = [
                'month' => $key,
                'income' => (float) ($incomeByMonth[$key] ?? 0),
                'expense' => (float) ($expenseByMonth[$key] ?? 0),
            ];
        }

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
            'overall' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'balance' => $totalIncome - $totalExpense,
            ],
            'expense_by_category' => $expenseByCategory,
            'daily_expense' => $dailyExpense,
            'monthly_trend' => $monthlyTrend,
        ]);
    }
}


