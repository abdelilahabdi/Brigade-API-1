<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttachPlatsToCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Plat;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $restaurant = $this->resolveRestaurant($request);

        $categories = Category::query()
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Categories recuperees avec succes.',
            'data' => $categories,
        ], 200);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $restaurant = $this->resolveRestaurant($request);

        $category = Category::create([
            ...$request->validated(),
            'restaurant_id' => $restaurant->id,
        ]);

        return response()->json([
            'message' => 'Categorie creee avec succes.',
            'data' => $category,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $this->authorize('view', $category);

        return response()->json([
            'message' => 'Categorie recuperee avec succes.',
            'data' => $category,
        ], 200);
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);

        $category->update($request->validated());

        return response()->json([
            'message' => 'Categorie mise a jour avec succes.',
            'data' => $category->fresh(),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Categorie supprimee avec succes.',
        ], 200);
    }

    public function attachPlats(AttachPlatsToCategoryRequest $request, int $id): JsonResponse
    {
        $restaurant = $this->resolveRestaurant($request);
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'message' => 'Categorie introuvable.',
            ], 404);
        }

        $this->authorize('update', $category);

        $platIds = $request->validated('plat_ids');

        $plats = Plat::query()
            ->whereIn('id', $platIds)
            ->get();

        $externalPlatIds = $plats
            ->where('restaurant_id', '!=', $restaurant->id)
            ->pluck('id')
            ->values();

        if ($externalPlatIds->isNotEmpty()) {
            return response()->json([
                'message' => 'Un ou plusieurs plats ne sont pas autorises pour ce restaurant.',
                'invalid_plat_ids' => $externalPlatIds->all(),
            ], 403);
        }

        Plat::query()
            ->whereIn('id', $platIds)
            ->update(['category_id' => $category->id]);

        $updatedPlats = Plat::query()
            ->whereIn('id', $platIds)
            ->with('category')
            ->get();

        return response()->json([
            'message' => 'Plats associes a la categorie avec succes.',
            'data' => [
                'category' => $category->fresh(),
                'plats' => $updatedPlats,
            ],
        ], 200);
    }

    private function resolveRestaurant(Request $request): Restaurant
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant) {
            abort(response()->json([
                'message' => 'Restaurant introuvable pour cet utilisateur.',
            ], 404));
        }

        return $restaurant;
    }
}
