<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $active = $this->parseBooleanFilter($request, 'active');

        $categories = Category::query()
            ->when($active !== null, static fn ($query) => $query->where('is_active', $active))
            ->withCount('plates')
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Categories retrieved successfully.',
            'data' => $categories,
            'filters' => [
                'active' => $active,
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $category = Category::query()->create([
            ...$validated,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'message' => 'Category retrieved successfully.',
            'data' => $category->loadCount('plates'),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category->fresh()->loadCount('plates'),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->plates()->where('is_available', true)->exists()) {
            return response()->json([
                'message' => 'Category cannot be deleted while it contains active plates.',
            ], 409);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    public function plates(Request $request, Category $category): JsonResponse
    {
        $includeUnavailable = $this->parseBooleanFilter($request, 'include_unavailable') ?? false;

        $plates = $category->plates()
            ->when(! $includeUnavailable, static fn ($query) => $query->where('is_available', true))
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Category plates retrieved successfully.',
            'data' => [
                'category' => $category,
                'plates' => $plates,
            ],
            'filters' => [
                'include_unavailable' => $includeUnavailable,
            ],
        ]);
    }

    private function parseBooleanFilter(Request $request, string $key): ?bool
    {
        if (! $request->has($key)) {
            return null;
        }

        $value = filter_var($request->query($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($value === null) {
            throw ValidationException::withMessages([
                $key => ["The {$key} query parameter must be true or false."],
            ]);
        }

        return $value;
    }
}
