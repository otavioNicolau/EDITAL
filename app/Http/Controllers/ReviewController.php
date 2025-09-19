<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\StudyItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Se for uma requisição da API (prefixo /api/), retorna JSON
        if ($request->is('api/*')) {
            $query = Review::with(['studyItem:id,title,topic_id', 'studyItem.topic:id,name']);

            // Filter by study item if provided
            if ($request->has('study_item_id')) {
                $query->where('study_item_id', $request->study_item_id);
            }

            // Filter by grade if provided
            if ($request->has('grade')) {
                $query->where('grade', $request->grade);
            }

            // Filter successful reviews
             if ($request->has('successful') && $request->successful === 'true') {
                 $query->successful();
             }

             // Filter failed reviews
             if ($request->has('failed') && $request->failed === 'true') {
                 $query->failed();
             }

             $reviews = $query->orderBy('created_at', 'desc')->get();

             return response()->json($reviews);
        }

        // Se for uma requisição AJAX explícita (com X-Requested-With), retorna JSON
        if ($request->ajax()) {
            $query = Review::with(['studyItem:id,title,topic_id', 'studyItem.topic:id,name']);

            // Filter by study item if provided
            if ($request->has('study_item_id')) {
                $query->where('study_item_id', $request->study_item_id);
            }

            // Filter by grade if provided
            if ($request->has('grade')) {
                $query->where('grade', $request->grade);
            }

            // Filter successful reviews
             if ($request->has('successful') && $request->successful === 'true') {
                 $query->successful();
             }

             // Filter failed reviews
             if ($request->has('failed') && $request->failed === 'true') {
                 $query->failed();
             }

             $reviews = $query->orderBy('created_at', 'desc')->get();

             return response()->json($reviews);
        }
        
        // Para todas as outras requisições web, retorna a view
        return view('reviews.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'study_item_id' => 'required|exists:study_items,id',
                'grade' => 'required|integer|min:0|max:5',
            ]);

            $studyItem = StudyItem::findOrFail($validated['study_item_id']);

            // Create review with before values
            $review = Review::create([
                'study_item_id' => $studyItem->id,
                'grade' => $validated['grade'],
                'ease_before' => $studyItem->ease,
                'ease_after' => $studyItem->ease, // Will be updated by applyReview
                'interval_before' => $studyItem->interval,
                'interval_after' => $studyItem->interval, // Will be updated by applyReview
                'due_before' => $studyItem->due_at,
                'due_after' => $studyItem->due_at, // Will be updated by applyReview
            ]);

            // Apply spaced repetition algorithm
            $studyItem->applyReview($validated['grade']);

            // Update review with after values
            $review->update([
                'ease_after' => $studyItem->ease,
                'interval_after' => $studyItem->interval,
                'due_after' => $studyItem->due_at,
            ]);

            $review->load(['studyItem:id,title,topic_id', 'studyItem.topic:id,name']);
            
            return response()->json($review, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'details' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review): JsonResponse
    {
        $review->load(['studyItem:id,title,topic_id', 'studyItem.topic:id,name']);

        return response()->json($review);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grade' => 'sometimes|required|integer|min:0|max:5',
            ]);

            $review->update($validated);
            
            return response()->json($review);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'details' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review): JsonResponse
    {
        try {
            $review->delete();
            
            return response()->json([
                'message' => 'Revisão excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir revisão',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get review statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        // Se for uma requisição da API (prefixo /api/), retorna JSON
        if ($request->is('api/*')) {
            $query = Review::query();

            // Filter by date range if provided
            if ($request->has('start_date')) {
                $query->where('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('created_at', '<=', $request->end_date);
            }

            $stats = [
                'total_reviews' => $query->count(),
                'successful_reviews' => $query->successful()->count(),
                'failed_reviews' => $query->failed()->count(),
                'average_grade' => round($query->avg('grade'), 2),
                'reviews_by_grade' => $query->selectRaw('grade, COUNT(*) as count')
                                           ->groupBy('grade')
                                           ->orderBy('grade')
                                           ->get()
                                           ->pluck('count', 'grade'),
            ];

            return response()->json($stats);
        }

        // Se for uma requisição AJAX explícita (com X-Requested-With), retorna JSON
        if ($request->ajax()) {
            $query = Review::query();

            // Filter by date range if provided
            if ($request->has('start_date')) {
                $query->where('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('created_at', '<=', $request->end_date);
            }

            $stats = [
                'total_reviews' => $query->count(),
                'successful_reviews' => $query->successful()->count(),
                'failed_reviews' => $query->failed()->count(),
                'average_grade' => round($query->avg('grade'), 2),
                'reviews_by_grade' => $query->selectRaw('grade, COUNT(*) as count')
                                           ->groupBy('grade')
                                           ->orderBy('grade')
                                           ->get()
                                           ->pluck('count', 'grade'),
            ];

            return response()->json($stats);
        }

        // Para requisições web normais, também retorna JSON (para compatibilidade)
        $query = Review::query();

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $stats = [
            'total_reviews' => $query->count(),
            'successful_reviews' => $query->successful()->count(),
            'failed_reviews' => $query->failed()->count(),
            'average_grade' => round($query->avg('grade'), 2),
            'reviews_by_grade' => $query->selectRaw('grade, COUNT(*) as count')
                                       ->groupBy('grade')
                                       ->orderBy('grade')
                                       ->get()
                                       ->pluck('count', 'grade'),
        ];

        return response()->json($stats);
    }

    /**
     * Get recent reviews
     */
    public function getRecent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        // Se for uma requisição da API (prefixo /api/), retorna JSON
        if ($request->is('api/*')) {
            $reviews = Review::with(['studyItem:id,title,topic_id', 'studyItem.topic:id,name'])
                            ->orderBy('created_at', 'desc')
                            ->limit($limit)
                            ->get();

            return response()->json($reviews);
        }

        // Se for uma requisição AJAX explícita (com X-Requested-With), retorna JSON
        if ($request->ajax()) {
            $reviews = Review::with(['studyItem:id,title,topic_id', 'studyItem.topic:id,name'])
                            ->orderBy('created_at', 'desc')
                            ->limit($limit)
                            ->get();

            return response()->json($reviews);
        }

        // Para requisições web normais, também retorna JSON (para compatibilidade)
        $reviews = Review::with(['studyItem:id,title,topic_id', 'studyItem.topic:id,name'])
                        ->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();

        return response()->json($reviews);
    }
}
