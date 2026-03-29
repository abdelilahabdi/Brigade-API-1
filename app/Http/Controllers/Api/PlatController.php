<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlatRequest;
use App\Http\Requests\UpdatePlatRequest;
use App\Models\Plat;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $restaurant = $this->resolveRestaurant($request);

        $plats = Plat::query()
            ->where('restaurant_id', $restaurant->id)
            ->with('category')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Plats recuperes avec succes.',
            'data' => $plats,
        ], 200);
    }

    public function store(StorePlatRequest $request): JsonResponse
    {
        $restaurant = $this->resolveRestaurant($request);

        $plat = Plat::create([
            ...$request->validated(),
            'restaurant_id' => $restaurant->id,
        ]);

        return response()->json([
            'message' => 'Plat cree avec succes.',
            'data' => $plat->load('category'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $plat = Plat::with('category')->findOrFail($id);
        $this->authorize('view', $plat);

        return response()->json([
            'message' => 'Plat recupere avec succes.',
            'data' => $plat,
        ], 200);
    }

    public function update(UpdatePlatRequest $request, int $id): JsonResponse
    {
        $plat = Plat::findOrFail($id);
        $this->authorize('update', $plat);

        $plat->update($request->validated());

        return response()->json([
            'message' => 'Plat mis a jour avec succes.',
            'data' => $plat->fresh()->load('category'),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $plat = Plat::findOrFail($id);
        $this->authorize('delete', $plat);

        $plat->delete();

        return response()->json([
            'message' => 'Plat supprime avec succes.',
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
