<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Return categories available for the authenticated user:
     * - global (user_id = null)
     * - user-specific (user_id = current user)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $categories = Category::query()
            ->where('type', 'expense')
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'user_id']);

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Store a new user category.
     */
    public function store(CategoryStoreRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $category = Category::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'color' => $data['color'] ?? null,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Update a user-owned category (global categories are read-only).
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $user = $request->user();

        if ($category->user_id !== $user->id) {
            abort(403, 'You may only modify your own categories.');
        }

        $data = $request->validated();

        $category->update([
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
        ]);

        return response()->json($category);
    }

    /**
     * Delete a user-owned category that is not in use.
     */
    public function destroy(Request $request, Category $category)
    {
        $user = $request->user();

        if ($category->user_id !== $user->id) {
            abort(403, 'You may only delete your own categories.');
        }

        $inUse = Expense::where('category_id', $category->id)->exists()
            || Budget::where('category_id', $category->id)->exists();

        if ($inUse) {
            return response()->json([
                'message' => 'Category is in use and cannot be deleted.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}

