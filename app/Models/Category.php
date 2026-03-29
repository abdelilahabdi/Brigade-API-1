<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'restaurant_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function plates(): HasMany
    {
        return $this->hasMany(Plate::class);
    }

    /**
     * @deprecated Legacy V1 relation kept for incremental migration.
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * @deprecated Legacy V1 relation kept for incremental migration.
     */
    public function plats(): HasMany
    {
        return $this->hasMany(Plat::class);
    }
}
