<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'status',
        'goals',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected $appends = [
        'progress_percentage',
    ];

    public function disciplines(): HasMany
    {
        return $this->hasMany(Discipline::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function studyItems(): HasManyThrough
    {
        return $this->hasManyThrough(StudyItem::class, Topic::class);
    }

    public function getTopicsCountAttribute()
    {
        return $this->topics()->count();
    }

    public function getCompletedTopicsCountAttribute()
    {
        return $this->topics()->where('status', 'COMPLETED')->count();
    }

    public function getProgressPercentageAttribute()
    {
        $total = $this->topics_count;
        if ($total === 0) return 0;
        
        $completed = $this->completed_topics_count;
        return round(($completed / $total) * 100, 2);
    }
}
