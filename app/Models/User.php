<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_CLIENT = 'client';

    public const ROLE_ADMIN = 'admin';

    public const DIETARY_TAG_VEGAN = 'vegan';

    public const DIETARY_TAG_NO_SUGAR = 'no_sugar';

    public const DIETARY_TAG_NO_CHOLESTEROL = 'no_cholesterol';

    public const DIETARY_TAG_GLUTEN_FREE = 'gluten_free';

    public const DIETARY_TAG_NO_LACTOSE = 'no_lactose';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'dietary_tags',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    protected $attributes = [
        'role' => self::ROLE_CLIENT,
        'dietary_tags' => '[]',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'dietary_tags' => 'array',
        ];
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * @return list<string>
     */
    public static function allowedRoles(): array
    {
        return [
            self::ROLE_CLIENT,
            self::ROLE_ADMIN,
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedDietaryTags(): array
    {
        return [
            self::DIETARY_TAG_VEGAN,
            self::DIETARY_TAG_NO_SUGAR,
            self::DIETARY_TAG_NO_CHOLESTEROL,
            self::DIETARY_TAG_GLUTEN_FREE,
            self::DIETARY_TAG_NO_LACTOSE,
        ];
    }

    /**
     * @deprecated Legacy V1 relation kept for incremental migration.
     */
    public function restaurant(): HasOne
    {
        return $this->hasOne(Restaurant::class);
    }
}
