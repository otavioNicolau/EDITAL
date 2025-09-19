<?php

namespace App\Http\Controllers;

use App\Models\StudyItem;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StudyItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Se for uma requisição da API (prefixo /api/), retorna JSON
        if ($request->is('api/*')) {
            $query = StudyItem::with(['topic:id,name,block_id', 'topic.block:id,name'])
                              ->withCount('reviews');

            // Filter by topic if provided
            if ($request->has('topic_id')) {
                $query->where('topic_id', $request->topic_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by kind if provided
            if ($request->has('kind')) {
                $query->where('kind', $request->kind);
            }

            // Filter due items
            if ($request->has('due') && $request->due === 'true') {
                $query->due();
            }

            $studyItems = $query->orderBy('due_at', 'asc')
                               ->orderBy('created_at', 'desc')
                               ->get();

            return response()->json($studyItems);
        }

        // Se for uma requisição AJAX explícita (com X-Requested-With), retorna JSON
        if ($request->ajax()) {
            $query = StudyItem::with(['topic:id,name,block_id', 'topic.block:id,name'])
                              ->withCount('reviews');

            // Filter by topic if provided
            if ($request->has('topic_id')) {
                $query->where('topic_id', $request->topic_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by kind if provided
            if ($request->has('kind')) {
                $query->where('kind', $request->kind);
            }

            // Filter due items
            if ($request->has('due') && $request->due === 'true') {
                $query->due();
            }

            $studyItems = $query->orderBy('due_at', 'asc')
                               ->orderBy('created_at', 'desc')
                               ->get();

            return response()->json($studyItems);
        }
        
        // Para todas as outras requisições web, retorna a view
        return view('study-items.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|min:1',
                'notes' => 'nullable|string',
                'kind' => ['required', Rule::in(['QUESTION', 'CONCEPT', 'EXERCISE', 'VIDEO', 'ARTICLE'])],
                'topic_id' => 'required|exists:topics,id',
                'status' => ['nullable', Rule::in(['NEW', 'LEARNING', 'REVIEW', 'MASTERED'])],
                'url' => 'nullable|url',
                'metadata' => 'nullable|json',
                'ease' => 'nullable|numeric|min:1.3|max:5.0',
                'interval' => 'nullable|integer|min:0',
                'due_at' => 'nullable|date',
            ]);

            if (isset($validated['metadata']) && is_string($validated['metadata'])) {
                $validated['metadata'] = json_decode($validated['metadata'], true) ?: null;
            }

            $studyItem = StudyItem::create($validated);
            $studyItem->load(['topic:id,name,block_id', 'topic.block:id,name']);

            return response()->json($studyItem, 201);
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
    public function show(StudyItem $studyItem): JsonResponse
    {
        $studyItem->load([
            'topic:id,name,block_id',
            'topic.block:id,name',
            'reviews' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return response()->json($studyItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StudyItem $studyItem): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|min:1',
                'notes' => 'nullable|string',
                'kind' => ['sometimes', Rule::in(['QUESTION', 'CONCEPT', 'EXERCISE', 'VIDEO', 'ARTICLE'])],
                'topic_id' => 'sometimes|required|exists:topics,id',
                'status' => ['nullable', Rule::in(['NEW', 'LEARNING', 'REVIEW', 'MASTERED'])],
                'url' => 'nullable|url',
                'metadata' => 'nullable|json',
                'ease' => 'nullable|numeric|min:1.3|max:5.0',
                'interval' => 'nullable|integer|min:0',
                'due_at' => 'nullable|date',
            ]);

            if (isset($validated['metadata']) && is_string($validated['metadata'])) {
                $validated['metadata'] = json_decode($validated['metadata'], true) ?: null;
            }

            $studyItem->update($validated);
            $studyItem->load(['topic:id,name,block_id', 'topic.block:id,name']);

            return response()->json($studyItem);
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
    public function destroy(StudyItem $studyItem): JsonResponse
    {
        try {
            $studyItem->delete();
            
            return response()->json([
                'message' => 'Item de estudo excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir item de estudo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get due study items for review
     */
    public function getDueItems(): JsonResponse
    {
        $dueItems = StudyItem::due()
                            ->with(['topic:id,name,block_id', 'topic.block:id,name'])
                            ->orderBy('due_at', 'asc')
                            ->get();

        return response()->json($dueItems);
    }

    /**
     * Update study item status
     */
    public function updateStatus(Request $request, StudyItem $studyItem): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => ['required', Rule::in(['NEW', 'LEARNING', 'REVIEW', 'MASTERED'])],
            ]);

            $studyItem->update(['status' => $validated['status']]);
            
            return response()->json($studyItem);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Status inválido',
                'details' => $e->errors()
            ], 422);
        }
    }
}
