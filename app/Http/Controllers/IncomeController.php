<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeStoreRequest;
use App\Http\Requests\IncomeUpdateRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    /**
     * Display a listing of the authenticated user's incomes.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $incomes = Income::where('user_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return IncomeResource::collection($incomes);
    }

    /**
     * Store a newly created income.
     */
    public function store(IncomeStoreRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $income = Income::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'source' => $data['source'] ?? null,
            'date' => $data['date'],
            'note' => $data['note'] ?? null,
        ]);

        return new IncomeResource($income);
    }

    /**
     * Update the specified income.
     */
    public function update(IncomeUpdateRequest $request, Income $income)
    {
        $user = $request->user();

        // Ensure the income belongs to the authenticated user
        if ($income->user_id !== $user->id) {
            abort(403, 'You are not allowed to modify this income record.');
        }

        $data = $request->validated();

        $income->update([
            'amount' => $data['amount'],
            'source' => $data['source'] ?? null,
            'date' => $data['date'],
            'note' => $data['note'] ?? null,
        ]);

        return new IncomeResource($income);
    }

    /**
     * Remove the specified income.
     */
    public function destroy(Request $request, Income $income)
    {
        $user = $request->user();

        if ($income->user_id !== $user->id) {
            abort(403, 'You are not allowed to delete this income record.');
        }

        $income->delete();

        return response()->json([
            'message' => 'Income deleted successfully.',
        ]);
    }
}


