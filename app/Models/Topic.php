<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'block_id',
        'discipline_id',
        'status',
        'tags',
    ];

    protected $casts = [
        'block_id' => 'integer',
        'discipline_id' => 'integer',
    ];

    protected $appends = [
        'progress_percentage',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function studyItems(): HasMany
    {
        return $this->hasMany(StudyItem::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, StudyItem::class);
    }

    public function getStudyItemsCountAttribute()
    {
        return $this->studyItems()->count();
    }

    public function getCompletedStudyItemsCountAttribute()
    {
        return $this->studyItems()->where('status', 'MASTERED')->count();
    }

    public function getProgressPercentageAttribute()
    {
        $total = $this->study_items_count;
        if ($total === 0) return 0;
        
        $completed = $this->completed_study_items_count;
        return round(($completed / $total) * 100, 2);
    }
}
