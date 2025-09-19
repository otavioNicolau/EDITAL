<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_item_id',
        'grade',
        'ease_before',
        'ease_after',
        'interval_before',
        'interval_after',
        'due_before',
        'due_after',
    ];

    protected $casts = [
        'study_item_id' => 'integer',
        'grade' => 'integer',
        'ease_before' => 'decimal:2',
        'ease_after' => 'decimal:2',
        'interval_before' => 'integer',
        'interval_after' => 'integer',
        'due_before' => 'datetime',
        'due_after' => 'datetime',
    ];

    public function studyItem(): BelongsTo
    {
        return $this->belongsTo(StudyItem::class);
    }

    public function scopeByGrade($query, int $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('grade', '>=', 3);
    }

    public function scopeFailed($query)
    {
        return $query->where('grade', '<', 3);
    }
}
