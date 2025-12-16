<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Get budget summary per category for a given month.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->query('month') ?: now()->format('Y-m');

        // All expense categories visible to user
        $categories = Category::query()
            ->where('type', 'expense')
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->orderBy('name')
            ->get();

        // Budgets for month
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->get()
            ->keyBy('category_id');

        // Spent per category for month
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        $spent = Expense::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $summary = $categories->map(function (Category $category) use ($budgets, $spent) {
            $budget = $budgets->get($category->id);
            $budgetAmount = $budget ? (float) $budget->amount : 0.0;
            $spentAmount = (float) ($spent[$category->id] ?? 0.0);
            $remaining = max($budgetAmount - $spentAmount, 0);
            $over = $spentAmount > $budgetAmount && $budgetAmount > 0;
            $progress = $budgetAmount > 0
                ? min(100, round(($spentAmount / $budgetAmount) * 100))
                : 0;

            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'category_color' => $category->color,
                'budget_id' => $budget ? $budget->id : null,
                'budget_amount' => $budgetAmount,
                'spent' => $spentAmount,
                'remaining' => $remaining,
                'over_budget' => $over,
                'progress' => $progress,
            ];
        })->values();

        return response()->json([
            'month' => $month,
            'data' => $summary,
        ]);
    }

    /**
     * Set or update monthly budget per category.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $category = Category::findOrFail($validated['category_id']);

        // Ensure user can use this category (global or owned)
        if (! is_null($category->user_id) && $category->user_id !== $user->id) {
            abort(403, 'You may only budget for your own categories.');
        }

        $budget = Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $validated['category_id'],
                'month' => $validated['month'],
            ],
            [
                'amount' => $validated['amount'],
            ]
        );

        return response()->json($budget);
    }

    /**
     * Update an existing budget.
     */
    public function update(Request $request, Budget $budget)
    {
        $user = $request->user();

        if ($budget->user_id !== $user->id) {
            abort(403, 'You may only modify your own budgets.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $budget->update([
            'amount' => $validated['amount'],
        ]);

        return response()->json($budget);
    }
}


