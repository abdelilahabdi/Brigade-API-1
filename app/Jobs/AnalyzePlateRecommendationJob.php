<?php

namespace App\Jobs;

use App\Models\Recommendation;
use App\Services\RecommendationScoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyzePlateRecommendationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $recommendationId)
    {
    }

    public function handle(RecommendationScoringService $scoringService): void
    {
        $recommendation = Recommendation::query()
            ->with([
                'user:id,dietary_tags',
                'plate:id,name,is_available',
                'plate.ingredients:id,tags',
            ])
            ->find($this->recommendationId);

        if (! $recommendation || ! $recommendation->user || ! $recommendation->plate) {
            return;
        }

        try {
            $result = $scoringService->analyze($recommendation->user, $recommendation->plate);

            $recommendation->forceFill([
                'score' => $result['score'],
                'label' => $result['label'],
                'warning_message' => $result['warning_message'],
                'conflicting_tags' => $result['conflicting_tags'],
                'status' => Recommendation::STATUS_READY,
            ])->save();
        } catch (Throwable $exception) {
            Log::error('Recommendation analysis failed.', [
                'recommendation_id' => $recommendation->id,
                'error' => $exception->getMessage(),
            ]);

            $recommendation->forceFill([
                'score' => null,
                'label' => null,
                'conflicting_tags' => [],
                'status' => Recommendation::STATUS_FAILED,
                'warning_message' => 'Recommendation analysis failed. Please retry.',
            ])->save();

            throw $exception;
        }
    }
}
