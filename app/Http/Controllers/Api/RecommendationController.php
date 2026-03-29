<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzePlateRecommendationJob;
use App\Models\Plate;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function analyze(Request $request, int $plateId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $plateQuery = Plate::query();

        if (! $user->isAdmin()) {
            $plateQuery->where('is_available', true);
        }

        $plate = $plateQuery->find($plateId);

        if (! $plate) {
            return response()->json([
                'message' => 'Plate not found.',
            ], 404);
        }

        $recommendation = Recommendation::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'plate_id' => $plate->id,
            ],
            [
                'status' => Recommendation::STATUS_PROCESSING,
                'score' => null,
                'label' => null,
                'warning_message' => null,
                'conflicting_tags' => null,
            ]
        );

        AnalyzePlateRecommendationJob::dispatch($recommendation->id);

        return response()->json([
            'message' => 'Recommendation analysis started.',
            'status' => Recommendation::STATUS_PROCESSING,
            'plate_id' => $plate->id,
        ], 202);
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $recommendations = Recommendation::query()
            ->where('user_id', $user->id)
            ->with([
                'plate:id,category_id,name,description,price,image,is_available',
                'plate.category:id,name',
            ])
            ->latest('updated_at')
            ->paginate($perPage)
            ->appends($request->query());

        $recommendations->setCollection(
            $recommendations->getCollection()->map(fn (Recommendation $recommendation): array => $this->recommendationPayload($recommendation))
        );

        return response()->json([
            'message' => 'Recommendations history retrieved successfully.',
            'data' => $recommendations,
            'filters' => [
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(Request $request, int $plateId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $recommendation = Recommendation::query()
            ->where('user_id', $user->id)
            ->where('plate_id', $plateId)
            ->with([
                'plate:id,category_id,name,description,price,image,is_available',
                'plate.category:id,name',
            ])
            ->first();

        if (! $recommendation) {
            return response()->json([
                'message' => 'Recommendation not found for this plate. Start analysis first.',
            ], 404);
        }

        $status = $this->normalizeStatus($recommendation->status);

        if ($status === Recommendation::STATUS_PROCESSING) {
            return response()->json([
                'message' => 'Recommendation is still processing.',
                'status' => Recommendation::STATUS_PROCESSING,
                'plate_id' => $plateId,
            ]);
        }

        return response()->json([
            'message' => 'Recommendation retrieved successfully.',
            'data' => $this->recommendationPayload($recommendation, $status),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function recommendationPayload(Recommendation $recommendation, ?string $normalizedStatus = null): array
    {
        $status = $normalizedStatus ?? $this->normalizeStatus($recommendation->status);

        return [
            'plate' => [
                'id' => $recommendation->plate?->id,
                'name' => $recommendation->plate?->name,
                'description' => $recommendation->plate?->description,
                'price' => $recommendation->plate?->price,
                'image' => $recommendation->plate?->image,
                'is_available' => $recommendation->plate?->is_available,
                'category' => $recommendation->plate?->category,
            ],
            'score' => $recommendation->score !== null ? (float) $recommendation->score : null,
            'label' => $recommendation->label,
            'warning_message' => $recommendation->warning_message,
            'conflicting_tags' => $recommendation->conflicting_tags ?? [],
            'status' => $status,
            'updated_at' => $recommendation->updated_at?->toISOString(),
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        if ($status === Recommendation::STATUS_PENDING || $status === Recommendation::STATUS_PROCESSING || $status === null) {
            return Recommendation::STATUS_PROCESSING;
        }

        return $status;
    }
}
