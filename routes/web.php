<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\StudyItemController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MetricsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Blocks
Route::get('/blocks', [BlockController::class, 'index'])->name('blocks.index');
Route::get('/blocks/create', [BlockController::class, 'create'])->name('blocks.create');
Route::get('/blocks/{block}', [BlockController::class, 'show'])->name('blocks.show');
Route::get('/blocks/{block}/edit', [BlockController::class, 'edit'])->name('blocks.edit');

// Disciplines
Route::get('/disciplines', [DisciplineController::class, 'index'])->name('disciplines.index');
Route::get('/disciplines/create', function () { return view('disciplines.create'); })->name('disciplines.create');
Route::get('/disciplines/{discipline}', [DisciplineController::class, 'show'])->name('disciplines.show');
Route::get('/disciplines/{discipline}/edit', function ($discipline) { return view('disciplines.edit', compact('discipline')); })->name('disciplines.edit');

// Topics
Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
Route::get('/topics/create', function () { return view('topics.create'); })->name('topics.create');
Route::get('/topics/{topic}', [TopicController::class, 'show'])->name('topics.show');
Route::get('/topics/{topic}/edit', function ($topic) { return view('topics.edit', compact('topic')); })->name('topics.edit');

// Study Items
Route::get('/study-items', [StudyItemController::class, 'index'])->name('study-items.index');
Route::get('/study-items/create', function () { return view('study-items.create'); })->name('study-items.create');
Route::get('/study-items/{studyItem}', [StudyItemController::class, 'show'])->name('study-items.show');
Route::get('/study-items/{studyItem}/edit', function ($studyItem) { return view('study-items.edit', compact('studyItem')); })->name('study-items.edit');
Route::get('/study-items/due/review', function () { return view('study-items.review'); })->name('study-items.review');

// Reviews
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::get('/reviews/stats', [ReviewController::class, 'getStats'])->name('reviews.stats');
Route::get('/reviews/recent', [ReviewController::class, 'getRecent'])->name('reviews.recent');

// Metrics
Route::get('/metrics', [MetricsController::class, 'index'])->name('metrics.index');
