<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Topic;
use App\Models\StudyItem;
use App\Models\Review;
use App\Models\StudySession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    /**
     * Get comprehensive metrics
     */
    public function index(Request $request)
    {
        $data = [
            'performance' => $this->getPerformanceMetrics(),
            'progress' => $this->getProgressMetrics(),
            'time_analysis' => $this->getTimeAnalysis(),
            'difficulty_analysis' => $this->getDifficultyAnalysis(),
        ];

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($data);
        }

        // Return view for browser requests
        return view('metrics.index', compact('data'));
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $totalReviews = Review::count();
        $successfulReviews = Review::successful()->count();
        $averageGrade = Review::avg('grade');

        return [
            'total_reviews' => $totalReviews,
            'successful_reviews' => $successfulReviews,
            'success_rate' => $totalReviews > 0 ? round(($successfulReviews / $totalReviews) * 100, 1) : 0,
            'average_grade' => round($averageGrade, 2),
            'grade_distribution' => Review::selectRaw('grade, COUNT(*) as count')
                                         ->groupBy('grade')
                                         ->orderBy('grade')
                                         ->get()
                                         ->pluck('count', 'grade'),
        ];
    }

    /**
     * Get progress metrics
     */
    private function getProgressMetrics(): array
    {
        $blocks = Block::withCount([
            'topics',
            'topics as completed_topics_count' => fn ($query) => $query->where('status', 'COMPLETED'),
            'disciplines',
        ])->get();

        $blockProgress = $blocks->map(function ($block) {
            $totalTopics = $block->topics_count;
            $completedTopics = $block->completed_topics_count;

            return [
                'id' => $block->id,
                'name' => $block->name,
                'total_topics' => $totalTopics,
                'completed_topics' => $completedTopics,
                'disciplines_count' => $block->disciplines_count,
                'progress_percentage' => $totalTopics > 0
                    ? round(($completedTopics / $totalTopics) * 100, 1)
                    : 0,
            ];
        });

        return [
            'blocks_progress' => $blockProgress,
            'overall_completion' => [
                'topics' => [
                    'total' => Topic::count(),
                    'completed' => Topic::where('status', 'COMPLETED')->count(),
                    'studying' => Topic::where('status', 'STUDYING')->count(),
                    'planned' => Topic::where('status', 'PLANNED')->count(),
                ],
                'study_items' => [
                    'total' => StudyItem::count(),
                    'mastered' => StudyItem::where('status', 'MASTERED')->count(),
                    'in_review' => StudyItem::where('status', 'REVIEW')->count(),
                    'learning' => StudyItem::where('status', 'LEARNING')->count(),
                    'new' => StudyItem::where('status', 'NEW')->count(),
                ],
            ],
        ];
    }

    /**
     * Get time analysis metrics
     */
    private function getTimeAnalysis(): array
    {
        $last30Days = now()->subDays(30);
        
        $dailyActivity = Review::where('created_at', '>=', $last30Days)
                              ->selectRaw('DATE(created_at) as date, COUNT(*) as reviews')
                              ->groupBy('date')
                              ->orderBy('date')
                              ->get();

        $weeklyActivity = Review::where('created_at', '>=', now()->subWeeks(12))
                               ->selectRaw('strftime("%Y-%W", created_at) as week, COUNT(*) as reviews')
                               ->groupBy('week')
                               ->orderBy('week')
                               ->get();

        return [
            'daily_activity_last_30_days' => $dailyActivity,
            'weekly_activity_last_12_weeks' => $weeklyActivity,
            'best_study_days' => Review::selectRaw("
                CASE strftime('%w', created_at)
                    WHEN '0' THEN 'Sunday'
                    WHEN '1' THEN 'Monday'
                    WHEN '2' THEN 'Tuesday'
                    WHEN '3' THEN 'Wednesday'
                    WHEN '4' THEN 'Thursday'
                    WHEN '5' THEN 'Friday'
                    WHEN '6' THEN 'Saturday'
                END as day, COUNT(*) as reviews")
                                      ->groupBy('day')
                                      ->orderByDesc('reviews')
                                      ->get(),
            'study_sessions' => [
                'total' => StudySession::count(),
                'active' => StudySession::active()->count(),
                'completed' => StudySession::completed()->count(),
                'average_duration' => StudySession::completed()->avg('duration_minutes'),
            ],
        ];
    }

    /**
     * Get difficulty analysis
     */
    private function getDifficultyAnalysis(): array
    {
        $itemsByKind = StudyItem::selectRaw('kind, COUNT(*) as count')
                               ->groupBy('kind')
                               ->orderByDesc('count')
                               ->get();

        $topicDifficulty = Topic::withCount(['studyItems', 'reviews'])
                               ->with(['studyItems:id,topic_id,ease'])
                               ->get()
                               ->map(function ($topic) {
                                   $averageEase = $topic->studyItems->avg('ease') ?? 0;

                                   return [
                                       'id' => $topic->id,
                                       'name' => $topic->name,
                                       'study_items_count' => $topic->study_items_count,
                                       'reviews_count' => $topic->reviews_count,
                                       'average_ease' => round($averageEase, 2),
                                       'difficulty_level' => $this->getDifficultyLevel($averageEase ?: 2.5),
                                   ];
                               });

        $easeDistribution = StudyItem::selectRaw('
                CASE 
                    WHEN COALESCE(ease, 2.5) < 1.8 THEN "Muito Difícil"
                    WHEN COALESCE(ease, 2.5) < 2.2 THEN "Difícil"
                    WHEN COALESCE(ease, 2.5) < 2.6 THEN "Médio"
                    WHEN COALESCE(ease, 2.5) < 3.0 THEN "Fácil"
                    ELSE "Muito Fácil"
                END as difficulty_level,
                COUNT(*) as count
            ')
            ->groupBy('difficulty_level')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'items_by_kind' => $itemsByKind,
            'topic_difficulty' => $topicDifficulty,
            'ease_distribution' => $easeDistribution,
        ];
    }

    /**
     * Get difficulty level based on ease factor
     */
    private function getDifficultyLevel(float $ease): string
    {
        if ($ease < 2.0) return 'Very Hard';
        if ($ease < 2.5) return 'Hard';
        if ($ease < 3.0) return 'Medium';
        if ($ease < 3.5) return 'Easy';
        return 'Very Easy';
    }

    /**
     * Get retention analysis
     */
    public function getRetentionAnalysis(): JsonResponse
    {
        $retentionData = StudyItem::selectRaw('
            status,
            AVG(ease) as avg_ease,
            AVG(interval) as avg_interval,
            COUNT(*) as count
        ')
        ->groupBy('status')
        ->get();

        $intervalDistribution = StudyItem::selectRaw('
            CASE 
                WHEN interval = 0 THEN "New"
                WHEN interval <= 1 THEN "1 day"
                WHEN interval <= 7 THEN "1 week"
                WHEN interval <= 30 THEN "1 month"
                WHEN interval <= 90 THEN "3 months"
                ELSE "Long term"
            END as interval_range,
            COUNT(*) as count
        ')
        ->groupBy('interval_range')
        ->get();

        return response()->json([
            'retention_by_status' => $retentionData,
            'interval_distribution' => $intervalDistribution,
            'items_due_soon' => StudyItem::whereBetween('due_at', [now(), now()->addDays(7)])
                                        ->count(),
            'overdue_items' => StudyItem::where('due_at', '<', now())
                                       ->where('due_at', '!=', null)
                                       ->count(),
        ]);
    }

    /**
     * Get learning velocity metrics
     */
    public function getLearningVelocity(): JsonResponse
    {
        $last30Days = now()->subDays(30);
        
        $velocityData = [
            'items_created_last_30_days' => StudyItem::where('created_at', '>=', $last30Days)->count(),
            'items_mastered_last_30_days' => StudyItem::where('status', 'MASTERED')
                                                     ->where('updated_at', '>=', $last30Days)
                                                     ->count(),
            'topics_completed_last_30_days' => Topic::where('status', 'COMPLETED')
                                                   ->where('updated_at', '>=', $last30Days)
                                                   ->count(),
            'average_reviews_per_day' => Review::where('created_at', '>=', $last30Days)->count() / 30,
            'learning_efficiency' => $this->calculateLearningEfficiency(),
        ];

        return response()->json($velocityData);
    }

    /**
     * Calculate learning efficiency
     */
    private function calculateLearningEfficiency(): float
    {
        $totalReviews = Review::count();
        $successfulReviews = Review::successful()->count();
        $masteredItems = StudyItem::where('status', 'MASTERED')->count();
        $totalItems = StudyItem::count();

        if ($totalReviews === 0 || $totalItems === 0) {
            return 0;
        }

        $successRate = $successfulReviews / $totalReviews;
        $masteryRate = $masteredItems / $totalItems;
        
        return round(($successRate + $masteryRate) / 2 * 100, 1);
    }
}
