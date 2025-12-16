<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseStoreRequest;
use App\Http\Requests\ExpenseUpdateRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the authenticated user's expenses with filters.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Expense::with('category')
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->input('to_date'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $expenses = $query->get();

        return ExpenseResource::collection($expenses);
    }

    /**
     * Store a newly created expense.
     */
    public function store(ExpenseStoreRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $expense = Expense::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'date' => $data['date'],
            'note' => $data['note'] ?? null,
        ]);

        $expense->load('category');

        return new ExpenseResource($expense);
    }

    /**
     * Update the specified expense.
     */
    public function update(ExpenseUpdateRequest $request, Expense $expense)
    {
        $user = $request->user();

        if ($expense->user_id !== $user->id) {
            abort(403, 'You are not allowed to modify this expense record.');
        }

        $data = $request->validated();

        $expense->update([
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'date' => $data['date'],
            'note' => $data['note'] ?? null,
        ]);

        $expense->load('category');

        return new ExpenseResource($expense);
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Request $request, Expense $expense)
    {
        $user = $request->user();

        if ($expense->user_id !== $user->id) {
            abort(403, 'You are not allowed to delete this expense record.');
        }

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully.',
        ]);
    }
}


