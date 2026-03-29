<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    use HasFactory;

    public const TAG_CONTAINS_MEAT = 'contains_meat';

    public const TAG_CONTAINS_SUGAR = 'contains_sugar';

    public const TAG_CONTAINS_CHOLESTEROL = 'contains_cholesterol';

    public const TAG_CONTAINS_GLUTEN = 'contains_gluten';

    public const TAG_CONTAINS_LACTOSE = 'contains_lactose';

    protected $fillable = [
        'name',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedTags(): array
    {
        return [
            self::TAG_CONTAINS_MEAT,
            self::TAG_CONTAINS_SUGAR,
            self::TAG_CONTAINS_CHOLESTEROL,
            self::TAG_CONTAINS_GLUTEN,
            self::TAG_CONTAINS_LACTOSE,
        ];
    }

    public function plates(): BelongsToMany
    {
        return $this->belongsToMany(Plate::class, 'plate_ingredient');
    }
}
