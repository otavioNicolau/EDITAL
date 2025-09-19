<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DisciplineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Se for uma requisição AJAX ou API, retorna JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            $query = Discipline::with('block')
                ->withCount([
                    'topics',
                    'topics as completed_topics_count' => fn ($query) => $query->where('status', 'COMPLETED'),
                    'studyItems',
                    'reviews',
                ]);
            
            // Filtros
            if ($request->filled('search')) {
                $search = $request->string('search')->toString();
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('block_id')) {
                $query->where('block_id', $request->block_id);
            }
            
            // Ordenação
            $sortBy = $request->get('sort', 'order');
            $sortOrder = $request->get('order', 'asc');
            
            if (in_array($sortBy, ['name', 'order', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            $disciplines = $query->get();
            
            return response()->json([
                'data' => $disciplines,
                'total' => $disciplines->count()
            ]);
        }
        
        // Para requisições web, retorna a view
        return view('disciplines.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'block_id' => 'required|exists:blocks,id',
                'order' => 'nullable|integer|min:0',
            ]);

            $discipline = Discipline::create($validated);
            $discipline->load('block');

            return response()->json([
                'message' => 'Disciplina criada com sucesso!',
                'discipline' => $discipline
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Discipline $discipline): JsonResponse
    {
        $discipline->load(['block', 'topics']);
        return response()->json($discipline);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discipline $discipline): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'block_id' => 'required|exists:blocks,id',
                'order' => 'nullable|integer|min:0',
            ]);

            $discipline->update($validated);
            $discipline->load('block');

            return response()->json([
                'message' => 'Disciplina atualizada com sucesso!',
                'discipline' => $discipline
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discipline $discipline): JsonResponse
    {
        try {
            $discipline->delete();

            return response()->json([
                'message' => 'Disciplina excluída com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir disciplina',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
