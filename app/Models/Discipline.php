<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Discipline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'block_id',
        'order',
    ];

    protected $casts = [
        'block_id' => 'integer',
        'order' => 'integer',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function studyItems(): HasManyThrough
    {
        return $this->hasManyThrough(StudyItem::class, Topic::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, StudyItem::class, 'topic_id', 'study_item_id');
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
