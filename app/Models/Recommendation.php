<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_READY = 'ready';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'plate_id',
        'score',
        'label',
        'warning_message',
        'conflicting_tags',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'conflicting_tags' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plate(): BelongsTo
    {
        return $this->belongsTo(Plate::class);
    }
}
