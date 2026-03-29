<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlateRequest;
use App\Http\Requests\UpdatePlateRequest;
use App\Models\Plate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $available = $this->parseBooleanFilter($request, 'available') ?? true;
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $plates = Plate::query()
            ->with([
                'category:id,name,description,color,is_active',
                'ingredients:id,name,tags',
            ])
            ->where('is_available', $available)
            ->orderBy('id')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'message' => 'Plates retrieved successfully.',
            'data' => $plates,
            'filters' => [
                'available' => $available,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(StorePlateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $ingredientIds = $validated['ingredient_ids'] ?? [];
        unset($validated['ingredient_ids']);

        $plate = DB::transaction(function () use ($validated, $ingredientIds): Plate {
            $plate = Plate::query()->create([
                ...$validated,
                'is_available' => $validated['is_available'] ?? true,
            ]);

            if ($ingredientIds !== []) {
                $plate->ingredients()->sync($ingredientIds);
            }

            return $plate;
        });

        return response()->json([
            'message' => 'Plate created successfully.',
            'data' => $plate->load([
                'category:id,name,description,color,is_active',
                'ingredients:id,name,tags',
            ]),
        ], 201);
    }

    public function show(Plate $plate): JsonResponse
    {
        return response()->json([
            'message' => 'Plate retrieved successfully.',
            'data' => $plate->load([
                'category:id,name,description,color,is_active',
                'ingredients:id,name,tags',
            ]),
        ]);
    }

    public function update(UpdatePlateRequest $request, Plate $plate): JsonResponse
    {
        $validated = $request->validated();
        $hasIngredientIds = array_key_exists('ingredient_ids', $validated);
        $ingredientIds = $validated['ingredient_ids'] ?? [];
        unset($validated['ingredient_ids']);

        DB::transaction(function () use ($plate, $validated, $hasIngredientIds, $ingredientIds): void {
            if ($validated !== []) {
                $plate->update($validated);
            }

            if ($hasIngredientIds) {
                $plate->ingredients()->sync($ingredientIds);
            }
        });

        return response()->json([
            'message' => 'Plate updated successfully.',
            'data' => $plate->fresh()->load([
                'category:id,name,description,color,is_active',
                'ingredients:id,name,tags',
            ]),
        ]);
    }

    public function destroy(Plate $plate): JsonResponse
    {
        DB::transaction(function () use ($plate): void {
            $plate->ingredients()->detach();
            $plate->delete();
        });

        return response()->json([
            'message' => 'Plate deleted successfully.',
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
