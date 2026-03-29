<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\Recommendation;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends Controller
{
    public function index(): JsonResponse
    {
        $mostRecommendedPlate = $this->resolvePlateRecommendationStat('desc');
        $leastRecommendedPlate = $this->resolvePlateRecommendationStat('asc');

        $categoryWithMostPlates = Category::query()
            ->withCount('plates')
            ->orderByDesc('plates_count')
            ->orderBy('id')
            ->first();

        return response()->json([
            'message' => 'Admin statistics retrieved successfully.',
            'data' => [
                'totals' => [
                    'plates' => Plate::query()->count(),
                    'categories' => Category::query()->count(),
                    'ingredients' => Ingredient::query()->count(),
                    'generated_recommendations' => Recommendation::query()->count(),
                ],
                'recommendations' => [
                    'most_recommended_plate' => $mostRecommendedPlate,
                    'least_recommended_plate' => $leastRecommendedPlate,
                ],
                'categories' => [
                    'category_with_most_plates' => $categoryWithMostPlates
                        ? [
                            'id' => $categoryWithMostPlates->id,
                            'name' => $categoryWithMostPlates->name,
                            'plates_count' => (int) $categoryWithMostPlates->plates_count,
                        ]
                        : null,
                ],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolvePlateRecommendationStat(string $direction): ?array
    {
        $aggregate = Recommendation::query()
            ->selectRaw('plate_id, AVG(score) as average_score, COUNT(*) as recommendations_count')
            ->where('status', Recommendation::STATUS_READY)
            ->whereNotNull('score')
            ->groupBy('plate_id')
            ->orderBy('average_score', $direction)
            ->orderByDesc('recommendations_count')
            ->orderBy('plate_id')
            ->first();

        if (! $aggregate) {
            return null;
        }

        $plate = Plate::query()
            ->with('category:id,name')
            ->find($aggregate->plate_id);

        if (! $plate) {
            return null;
        }

        return [
            'plate' => [
                'id' => $plate->id,
                'name' => $plate->name,
                'category' => $plate->category
                    ? [
                        'id' => $plate->category->id,
                        'name' => $plate->category->name,
                    ]
                    : null,
            ],
            'average_score' => round((float) $aggregate->average_score, 2),
            'recommendations_count' => (int) $aggregate->recommendations_count,
        ];
    }
}
