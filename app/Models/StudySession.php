<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'items_reviewed',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'topic_id' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'items_reviewed' => 'integer',
        'metadata' => 'array',
    ];

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    public function endSession(): void
    {
        $this->ended_at = now();
        $this->duration_minutes = $this->started_at->diffInMinutes($this->ended_at);
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('ended_at');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('started_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }
}
