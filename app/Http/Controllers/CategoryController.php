<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $query = Category::forUser(auth()->id())
            ->orderBy('type')
            ->orderBy('name');

        // Filter by type if provided
        if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        $categories = $query->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['type'])
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return Inertia::render('Categories/Create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('user_id', auth()->id())
                        ->where('type', $request->type);
                })
            ],
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/'
        ]);

        $validated['user_id'] = auth()->id();

        // Set default color if not provided
        if (!isset($validated['color'])) {
            $validated['color'] = $validated['type'] === 'income' ? '#87c38f' : '#da2c38';
        }

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        // Ensure the category belongs to the authenticated user
        if ($category->user_id !== auth()->id()) {
            abort(404);
        }

        return Inertia::render('Categories/Show', [
            'category' => $category
        ]);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        // Ensure the category belongs to the authenticated user
        if ($category->user_id !== auth()->id()) {
            abort(404);
        }

        return Inertia::render('Categories/Edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        // Ensure the category belongs to the authenticated user
        if ($category->user_id !== auth()->id()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request, $category) {
                    return $query->where('user_id', auth()->id())
                        ->where('type', $request->type ?? $category->type)
                        ->where('id', '!=', $category->id);
                })
            ],
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/'
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        // Ensure the category belongs to the authenticated user
        if ($category->user_id !== auth()->id()) {
            abort(404);
        }

        // Check if category has transactions
        if ($category->transactions()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category that has transactions');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }

    /**
     * Get categories as JSON (for AJAX requests)
     */
    public function api(Request $request)
    {
        $query = Category::forUser(auth()->id())
            ->orderBy('name');

        // Filter by type if provided
        if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        $categories = $query->get();

        return response()->json($categories);
    }

    /**
     * Get categories grouped by type (for forms)
     */
    public function grouped()
    {
        $categories = Category::forUser(auth()->id())
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return response()->json([
            'income' => $categories->get('income', collect()),
            'expense' => $categories->get('expense', collect())
        ]);
    }
}
