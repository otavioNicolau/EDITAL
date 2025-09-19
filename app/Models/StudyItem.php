<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class StudyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'notes',
        'kind',
        'topic_id',
        'status',
        'url',
        'tags',
        'metadata',
        'ease',
        'interval',
        'due_at',
    ];

    protected $casts = [
        'topic_id' => 'integer',
        'ease' => 'decimal:2',
        'interval' => 'integer',
        'due_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isDue(): bool
    {
        return $this->due_at && $this->due_at <= now();
    }

    public function applyReview(int $grade): array
    {
        $ease = $this->ease ?? 2.5;
        $interval = $this->interval ?? 0;

        // Algoritmo SRS baseado no original
        $newEase = max(1.3, $ease + (-0.8 + 0.28 * $grade - 0.02 * $grade * $grade));

        if ($grade < 2) {
            $newInterval = 1;
        } elseif ($interval === 0) {
            $newInterval = 1;
        } else {
            $newInterval = (int) round($interval * $newEase);
        }

        $dueAt = Carbon::now()->addDays($newInterval);

        $updates = [
            'ease' => $newEase,
            'interval' => $newInterval,
            'due_at' => $dueAt,
        ];

        $this->forceFill($updates)->save();

        return $updates;
    }

    public function scopeDue($query)
    {
        return $query->where('due_at', '<=', now());
    }

    public function scopeByKind($query, string $kind)
    {
        return $query->where('kind', $kind);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
