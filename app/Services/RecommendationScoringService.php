<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\User;

class RecommendationScoringService
{
    /**
     * @var array<string, string>
     */
    private const CONFLICT_MAPPING = [
        User::DIETARY_TAG_VEGAN => Ingredient::TAG_CONTAINS_MEAT,
        User::DIETARY_TAG_NO_SUGAR => Ingredient::TAG_CONTAINS_SUGAR,
        User::DIETARY_TAG_NO_CHOLESTEROL => Ingredient::TAG_CONTAINS_CHOLESTEROL,
        User::DIETARY_TAG_GLUTEN_FREE => Ingredient::TAG_CONTAINS_GLUTEN,
        User::DIETARY_TAG_NO_LACTOSE => Ingredient::TAG_CONTAINS_LACTOSE,
    ];

    /**
     * @return array{
     *     score:int,
     *     label:string,
     *     warning_message:?string,
     *     conflicting_tags:array<int, array{dietary_tag:string, ingredient_tag:string}>
     * }
     */
    public function analyze(User $user, Plate $plate): array
    {
        $plate->loadMissing('ingredients:id,tags');

        $dietaryTags = collect($user->dietary_tags ?? [])
            ->filter(static fn ($tag): bool => is_string($tag))
            ->values();

        $restrictedDietaryTags = $dietaryTags
            ->filter(static fn (string $tag): bool => array_key_exists($tag, self::CONFLICT_MAPPING))
            ->values();

        if ($restrictedDietaryTags->isEmpty()) {
            return [
                'score' => 100,
                'label' => 'Highly Recommended',
                'warning_message' => null,
                'conflicting_tags' => [],
            ];
        }

        $plateIngredientTags = $plate->ingredients
            ->flatMap(static function ($ingredient): array {
                return is_array($ingredient->tags) ? $ingredient->tags : [];
            })
            ->filter(static fn ($tag): bool => is_string($tag))
            ->unique()
            ->values();

        $conflictingTags = $restrictedDietaryTags
            ->map(static fn (string $dietaryTag): array => [
                'dietary_tag' => $dietaryTag,
                'ingredient_tag' => self::CONFLICT_MAPPING[$dietaryTag],
            ])
            ->filter(static fn (array $mapping) => $plateIngredientTags->contains($mapping['ingredient_tag']))
            ->values();

        $restrictionCount = $restrictedDietaryTags->count();
        $conflictCount = $conflictingTags->count();
        $score = (int) round((($restrictionCount - $conflictCount) / $restrictionCount) * 100);

        return [
            'score' => $score,
            'label' => $this->resolveLabel($score),
            'warning_message' => $this->resolveWarningMessage($score, $conflictingTags->all()),
            'conflicting_tags' => $conflictingTags->all(),
        ];
    }

    private function resolveLabel(int $score): string
    {
        if ($score >= 80) {
            return 'Highly Recommended';
        }

        if ($score >= 50) {
            return 'Recommended with notes';
        }

        return 'Not Recommended';
    }

    /**
     * @param array<int, array{dietary_tag:string, ingredient_tag:string}> $conflictingTags
     */
    private function resolveWarningMessage(int $score, array $conflictingTags): ?string
    {
        if ($score >= 50) {
            return null;
        }

        $dietaryTags = collect($conflictingTags)
            ->pluck('dietary_tag')
            ->unique()
            ->values()
            ->implode(', ');

        return $dietaryTags !== ''
            ? "This plate conflicts with your dietary restrictions: {$dietaryTags}."
            : 'This plate has major compatibility issues with your dietary profile.';
    }
}
