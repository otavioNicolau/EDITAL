<?php

namespace App\Http\Controllers;

use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $blocksQuery = Block::withCount(['topics', 'studyItems', 'disciplines'])
            ->with(['disciplines' => function ($query) {
                $query->select('id', 'name', 'block_id', 'order')->orderBy('order');
            }]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $blocksQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $blocksQuery->where('status', $request->status);
        }

        $sortBy = $request->get('sort', 'name');
        $sortOrder = $request->get('order', 'asc');

        if (in_array($sortBy, ['name', 'created_at', 'updated_at'])) {
            $blocksQuery->orderBy($sortBy, $sortOrder);
        }

        $blocks = $blocksQuery->get();

        if ($request->is('api/*') || $request->ajax()) {
            return response()->json($blocks);
        }

        return view('blocks.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('blocks.create');
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
                'color' => 'nullable|string|in:blue,green,purple,red,yellow,indigo',
                'status' => 'nullable|string|in:not_started,in_progress,completed',
                'goals' => 'nullable|string',
                'order' => 'nullable|integer|min:0',
            ]);

            $block = Block::create($validated);
            
            return response()->json($block, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Block $block)
    {
        // Se for uma requisição da API (prefixo /api/), retorna JSON
        if ($request->is('api/*')) {
            $block->load(['topics' => function($query) {
                $query->withCount(['studyItems', 'reviews']);
            }]);
            
            return response()->json($block);
        }
        
        // Se for uma requisição AJAX explícita (com X-Requested-With), retorna JSON
        if ($request->ajax()) {
            $block->load(['topics' => function($query) {
                $query->withCount(['studyItems', 'reviews']);
            }]);
            
            return response()->json($block);
        }
        
        // Para todas as outras requisições web, retorna a view
        return view('blocks.show', compact('block'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Block $block): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|min:1',
                'description' => 'nullable|string',
                'order' => 'nullable|integer|min:0',
            ]);

            $block->update($validated);
            
            return response()->json($block);
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
    public function edit(Block $block)
    {
        return view('blocks.edit', compact('block'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Block $block): JsonResponse
    {
        try {
            $block->delete();
            
            return response()->json([
                'message' => 'Bloco excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao excluir bloco',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
