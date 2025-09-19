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

class DashboardController extends Controller
{
    /**
     * Get dashboard overview data
     */
    public function index()
    {
        $overview = $this->getOverview();
        $dueItems = $this->getDueItems();
        $recentReviews = $this->getRecentReviews();
        $progress = $this->getProgress();
        $studyStreak = $this->getStudyStreak();

        return view('dashboard.index', compact(
            'overview', 
            'dueItems', 
            'recentReviews', 
            'progress', 
            'studyStreak'
        ));
    }

    /**
     * Get overview statistics
     */
    private function getOverview(): array
    {
        return [
            'total_blocks' => Block::count(),
            'total_topics' => Topic::count(),
            'total_study_items' => StudyItem::count(),
            'total_reviews' => Review::count(),
            'due_items_count' => StudyItem::due()->count(),
            'completed_topics' => Topic::where('status', 'COMPLETED')->count(),
            'studying_topics' => Topic::where('status', 'STUDYING')->count(),
            'planned_topics' => Topic::where('status', 'PLANNED')->count(),
        ];
    }

    /**
     * Get due items for today
     */
    private function getDueItems(): array
    {
        $dueItems = StudyItem::due()
                            ->with(['topic:id,name,block_id', 'topic.block:id,name'])
                            ->orderBy('due_at', 'asc')
                            ->limit(10)
                            ->get();

        return [
            'count' => StudyItem::due()->count(),
            'items' => $dueItems,
        ];
    }

    /**
     * Get recent reviews
     */
    private function getRecentReviews(): array
    {
        $reviews = Review::with(['studyItem:id,title,topic_id', 'studyItem.topic:id,name'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();

        return [
            'today_count' => Review::whereDate('created_at', today())->count(),
            'week_count' => Review::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'recent' => $reviews,
        ];
    }

    /**
     * Get progress statistics
     */
    private function getProgress(): array
    {
        $totalTopics = Topic::count();
        $completedTopics = Topic::where('status', 'COMPLETED')->count();
        $studyingTopics = Topic::where('status', 'STUDYING')->count();
        $plannedTopics = Topic::where('status', 'PLANNED')->count();

        $totalItems = StudyItem::count();
        $masteredItems = StudyItem::where('status', 'MASTERED')->count();
        $reviewItems = StudyItem::where('status', 'REVIEW')->count();

        // Calculate percentages for the chart
        $completedPercentage = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100, 1) : 0;
        $inProgressPercentage = $totalTopics > 0 ? round(($studyingTopics / $totalTopics) * 100, 1) : 0;
        $notStartedPercentage = $totalTopics > 0 ? round(($plannedTopics / $totalTopics) * 100, 1) : 0;

        return [
            'topics' => [
                'total' => $totalTopics,
                'completed' => $completedTopics,
                'studying' => $studyingTopics,
                'completion_rate' => $completedPercentage,
            ],
            'study_items' => [
                'total' => $totalItems,
                'mastered' => $masteredItems,
                'in_review' => $reviewItems,
                'mastery_rate' => $totalItems > 0 ? round(($masteredItems / $totalItems) * 100, 1) : 0,
            ],
            // Chart data
            'completed_percentage' => $completedPercentage,
            'in_progress_percentage' => $inProgressPercentage,
            'not_started_percentage' => $notStartedPercentage,
        ];
    }

    /**
     * Get study streak information
     */
    private function getStudyStreak(): array
    {
        $today = today();
        $streak = 0;
        $currentDate = $today->copy();

        // Count consecutive days with reviews
        while (Review::whereDate('created_at', $currentDate)->exists()) {
            $streak++;
            $currentDate->subDay();
        }

        // Calculate longest streak (simplified version)
        $longestStreak = Review::selectRaw('DATE(created_at) as review_date')
                              ->groupBy('review_date')
                              ->orderBy('review_date')
                              ->get()
                              ->count();

        // Calculate total study days
        $totalStudyDays = Review::selectRaw('DATE(created_at) as review_date')
                               ->groupBy('review_date')
                               ->count();

        return [
            'current_streak' => $streak,
            'longest_streak' => max($longestStreak, $streak),
            'total_study_days' => $totalStudyDays,
            'studied_today' => Review::whereDate('created_at', $today)->exists(),
            'reviews_today' => Review::whereDate('created_at', $today)->count(),
        ];
    }

    /**
     * Get weekly statistics
     */
    public function getWeeklyStats(): JsonResponse
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $dailyReviews = [];
        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $dailyReviews[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'reviews' => Review::whereDate('created_at', $date)->count(),
                'successful_reviews' => Review::whereDate('created_at', $date)->successful()->count(),
            ];
        }

        return response()->json([
            'daily_reviews' => $dailyReviews,
            'week_total' => Review::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'week_successful' => Review::whereBetween('created_at', [$startOfWeek, $endOfWeek])->successful()->count(),
        ]);
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStats(): JsonResponse
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthlyData = [
            'total_reviews' => Review::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'successful_reviews' => Review::whereBetween('created_at', [$startOfMonth, $endOfMonth])->successful()->count(),
            'topics_completed' => Topic::where('status', 'COMPLETED')
                                      ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                                      ->count(),
            'items_mastered' => StudyItem::where('status', 'MASTERED')
                                        ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                                        ->count(),
        ];

        return response()->json($monthlyData);
    }
}
