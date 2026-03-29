<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $ingredients = Ingredient::query()
            ->withCount('plates')
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'message' => 'Ingredients retrieved successfully.',
            'data' => $ingredients,
            'filters' => [
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(StoreIngredientRequest $request): JsonResponse
    {
        $ingredient = Ingredient::query()->create($request->validated());

        return response()->json([
            'message' => 'Ingredient created successfully.',
            'data' => $ingredient->loadCount('plates'),
        ], 201);
    }

    public function update(UpdateIngredientRequest $request, Ingredient $ingredient): JsonResponse
    {
        $ingredient->update($request->validated());

        return response()->json([
            'message' => 'Ingredient updated successfully.',
            'data' => $ingredient->fresh()->loadCount('plates'),
        ]);
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        DB::transaction(function () use ($ingredient): void {
            $ingredient->plates()->detach();
            $ingredient->delete();
        });

        return response()->json([
            'message' => 'Ingredient deleted successfully.',
        ]);
    }
}
