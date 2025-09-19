<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Block;
use App\Models\Discipline;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Se for uma requisição da API (prefixo /api/), retorna JSON
        if ($request->is('api/*')) {
            $query = Topic::with(['block:id,name', 'discipline:id,name,block_id'])
                         ->withCount(['studyItems', 'reviews']);

            // Filter by block if provided
            if ($request->has('block_id')) {
                $query->where('block_id', $request->block_id);
            }

            // Filter by discipline if provided
            if ($request->filled('discipline_id')) {
                $query->where('discipline_id', $request->discipline_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $topics = $query->orderBy('created_at', 'desc')->get();

            return response()->json($topics);
        }

        // Se for uma requisição AJAX explícita (com X-Requested-With), retorna JSON
        if ($request->ajax()) {
            $query = Topic::with(['block:id,name', 'discipline:id,name,block_id'])
                         ->withCount(['studyItems', 'reviews']);

            // Filter by block if provided
            if ($request->has('block_id')) {
                $query->where('block_id', $request->block_id);
            }

            // Filter by discipline if provided
            if ($request->filled('discipline_id')) {
                $query->where('discipline_id', $request->discipline_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $topics = $query->orderBy('created_at', 'desc')->get();

            return response()->json($topics);
        }

        // Para todas as outras requisições web, retorna a view
        return view('topics.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:1',
                'description' => 'nullable|string',
                'discipline_id' => 'required|exists:disciplines,id',
                'status' => ['nullable', Rule::in(['PLANNED', 'STUDYING', 'REVIEW', 'COMPLETED'])],
                'tags' => 'nullable|string',
            ]);

            $discipline = Discipline::find($validated['discipline_id']);

            if ($request->filled('block_id') && (int) $request->block_id !== $discipline->block_id) {
                return response()->json([
                    'error' => 'Dados inválidos',
                    'details' => [
                        'block_id' => ['O bloco selecionado não corresponde à disciplina escolhida.'],
                    ],
                ], 422);
            }

            $topic = Topic::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'PLANNED',
                'tags' => $validated['tags'] ?? null,
                'discipline_id' => $discipline->id,
                'block_id' => $discipline->block_id,
            ]);

            $topic->load(['block:id,name', 'discipline:id,name,block_id']);
            
            return response()->json($topic, 201);
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
    public function show(Request $request, Topic $topic)
    {
        // Se for uma requisição da API (prefixo /api/), retorna JSON
        if ($request->is('api/*')) {
            $topic->load([
                'block:id,name',
                'discipline:id,name,block_id',
                'studyItems' => function ($query) {
                    $query->withCount('reviews');
                }
            ]);

            return response()->json($topic);
        }

        // Se for uma requisição AJAX explícita (com X-Requested-With), retorna JSON
        if ($request->ajax()) {
            $topic->load([
                'block:id,name',
                'discipline:id,name,block_id',
                'studyItems' => function ($query) {
                    $query->withCount('reviews');
                }
            ]);

            return response()->json($topic);
        }
        
        // Para todas as outras requisições web, retorna a view
        return view('topics.show', compact('topic'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Topic $topic): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|min:1',
                'description' => 'nullable|string',
                'discipline_id' => 'sometimes|required|exists:disciplines,id',
                'status' => ['nullable', Rule::in(['PLANNED', 'STUDYING', 'REVIEW', 'COMPLETED'])],
                'tags' => 'nullable|string',
            ]);

            if (array_key_exists('discipline_id', $validated)) {
                $discipline = Discipline::find($validated['discipline_id']);

                if ($request->filled('block_id') && (int) $request->block_id !== $discipline->block_id) {
                    return response()->json([
                        'error' => 'Dados inválidos',
                        'details' => [
                            'block_id' => ['O bloco selecionado não corresponde à disciplina escolhida.'],
                        ],
                    ], 422);
                }

                $validated['block_id'] = $discipline->block_id;
            }

            $topic->update($validated);
            $topic->load(['block:id,name', 'discipline:id,name,block_id']);
            
            return response()->json($topic);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'details' => $e->errors()
            ], 422);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Topic $topic)
    {
        $blocks = Block::orderBy('name')->get();
        return view('topics.edit', compact('topic', 'blocks'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Topic $topic): JsonResponse
    {
        try {
            $topic->delete();
            
            return response()->json([
                'message' => 'Tópico excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir tópico',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update topic status
     */
    public function updateStatus(Request $request, Topic $topic): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => ['required', Rule::in(['PLANNED', 'STUDYING', 'REVIEW', 'COMPLETED'])],
            ]);

            $topic->update(['status' => $validated['status']]);
            
            return response()->json($topic);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Status inválido',
                'details' => $e->errors()
            ], 422);
        }
    }
}
