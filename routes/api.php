<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\StudyItemController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MetricsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');
Route::get('/dashboard/weekly-stats', [DashboardController::class, 'getWeeklyStats'])->name('api.dashboard.weekly-stats');
Route::get('/dashboard/monthly-stats', [DashboardController::class, 'getMonthlyStats'])->name('api.dashboard.monthly-stats');

// Metrics routes
Route::get('/metrics', [MetricsController::class, 'index'])->name('api.metrics.index');
Route::get('/metrics/retention', [MetricsController::class, 'getRetentionAnalysis'])->name('api.metrics.retention');
Route::get('/metrics/velocity', [MetricsController::class, 'getLearningVelocity'])->name('api.metrics.velocity');

// Block routes
Route::apiResource('blocks', BlockController::class)->names([
    'index' => 'api.blocks.index',
    'store' => 'api.blocks.store',
    'show' => 'api.blocks.show',
    'update' => 'api.blocks.update',
    'destroy' => 'api.blocks.destroy'
]);

// Discipline routes
Route::apiResource('disciplines', DisciplineController::class)->names([
    'index' => 'api.disciplines.index',
    'store' => 'api.disciplines.store',
    'show' => 'api.disciplines.show',
    'update' => 'api.disciplines.update',
    'destroy' => 'api.disciplines.destroy'
]);

// Topic routes
Route::apiResource('topics', TopicController::class)->names([
    'index' => 'api.topics.index',
    'store' => 'api.topics.store',
    'show' => 'api.topics.show',
    'update' => 'api.topics.update',
    'destroy' => 'api.topics.destroy'
]);
Route::patch('/topics/{topic}/status', [TopicController::class, 'updateStatus'])->name('api.topics.update-status');

// Study Item routes
Route::apiResource('study-items', StudyItemController::class)->names([
    'index' => 'api.study-items.index',
    'store' => 'api.study-items.store',
    'show' => 'api.study-items.show',
    'update' => 'api.study-items.update',
    'destroy' => 'api.study-items.destroy'
]);
Route::get('/study-items/due/items', [StudyItemController::class, 'getDueItems'])->name('api.study-items.due');
Route::patch('/study-items/{studyItem}/status', [StudyItemController::class, 'updateStatus'])->name('api.study-items.update-status');

// Review routes
Route::apiResource('reviews', ReviewController::class)->names([
    'index' => 'api.reviews.index',
    'store' => 'api.reviews.store',
    'show' => 'api.reviews.show',
    'update' => 'api.reviews.update',
    'destroy' => 'api.reviews.destroy'
]);
Route::get('/reviews/stats', [ReviewController::class, 'getStats'])->name('api.reviews.stats');
Route::get('/reviews/recent', [ReviewController::class, 'getRecent'])->name('api.reviews.recent');