<?php

namespace App\Http\Controllers;

use App\Models\Block;
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
     * Show the form for creating a new discipline.
     */
    public function create(Request $request)
    {
        $blocks = Block::orderBy('name')->get();
        $selectedBlockId = $request->query('block_id', $blocks->first()?->id);

        return view('disciplines.create', compact('blocks', 'selectedBlockId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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

            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Disciplina criada com sucesso!',
                    'discipline' => $discipline
                ], 201);
            }

            return redirect()
                ->route('disciplines.show', $discipline)
                ->with('status', 'Disciplina criada com sucesso!');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Dados inválidos',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Discipline $discipline)
    {
        $discipline->load([
            'block:id,name,description,color,status,order,created_at,updated_at',
            'topics' => function ($query) {
                $query->withCount([
                    'studyItems',
                    'studyItems as mastered_study_items_count' => fn ($q) => $q->where('status', 'MASTERED'),
                    'reviews',
                ])->orderBy('name');
            },
        ]);

        $topics = $discipline->topics->map(function ($topic) {
            $base = $topic->only([
                'id',
                'block_id',
                'discipline_id',
                'name',
                'description',
                'tags',
                'weight',
                'status',
                'next_review_at',
                'ease_factor',
                'interval_days',
                'lapses',
                'created_at',
                'updated_at',
            ]);

            return array_merge($base, [
                'study_items_count' => $topic->study_items_count,
                'mastered_study_items_count' => $topic->mastered_study_items_count,
                'reviews_count' => $topic->reviews_count,
                'progress_percentage' => $topic->progress_percentage,
                'status_label' => $this->formatTopicStatus($topic->status),
            ]);
        })->values();

        $topicsCount = $topics->count();
        $completedTopics = $topics->where('status', 'COMPLETED')->count();
        $studyingTopics = $topics->where('status', 'STUDYING')->count();
        $plannedTopics = $topics->where('status', 'PLANNED')->count();
        $reviewTopics = $topics->where('status', 'REVIEW')->count();

        $studyItemsTotal = $topics->sum('study_items_count');
        $masteredItemsTotal = $topics->sum('mastered_study_items_count');
        $reviewsTotal = $topics->sum('reviews_count');

        $disciplineData = [
            'id' => $discipline->id,
            'block_id' => $discipline->block_id,
            'name' => $discipline->name,
            'description' => $discipline->description,
            'order' => $discipline->order,
            'created_at' => $discipline->created_at,
            'updated_at' => $discipline->updated_at,
            'block' => $discipline->block,
            'metrics' => [
                'topics' => [
                    'total' => $topicsCount,
                    'completed' => $completedTopics,
                    'studying' => $studyingTopics,
                    'planned' => $plannedTopics,
                    'review' => $reviewTopics,
                    'completion_percentage' => $topicsCount > 0 ? round(($completedTopics / $topicsCount) * 100, 1) : 0,
                ],
                'study_items' => [
                    'total' => $studyItemsTotal,
                    'mastered' => $masteredItemsTotal,
                ],
                'reviews' => [
                    'total' => $reviewsTotal,
                ],
            ],
            'topics' => $topics,
        ];

        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json($disciplineData);
        }

        $discipline->setAttribute('metrics', $disciplineData['metrics']);
        $discipline->setRelation('topics', $discipline->topics->map(function ($topic) {
            $topic->setAttribute('progress_percentage', $topic->progress_percentage);
            $topic->setAttribute('status_label', $this->formatTopicStatus($topic->status));
            return $topic;
        }));

        return view('disciplines.show', [
            'discipline' => $discipline,
            'topicsSummary' => $topics,
            'disciplineMetrics' => $disciplineData['metrics'],
        ]);
    }

    private function formatTopicStatus(?string $status): string
    {
        return match ($status) {
            'PLANNED' => 'Planejado',
            'STUDYING' => 'Em estudo',
            'REVIEW' => 'Em revisão',
            'COMPLETED' => 'Concluído',
            default => $status ?? 'Indefinido',
        };
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discipline $discipline)
    {
        $blocks = Block::orderBy('name')->get();

        return view('disciplines.edit', compact('discipline', 'blocks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discipline $discipline)
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

            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Disciplina atualizada com sucesso!',
                    'discipline' => $discipline
                ]);
            }

            return redirect()
                ->route('disciplines.show', $discipline)
                ->with('status', 'Disciplina atualizada com sucesso!');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Dados inválidos',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Discipline $discipline)
    {
        try {
            $discipline->delete();

            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Disciplina excluída com sucesso!'
                ]);
            }

            return redirect()
                ->route('disciplines.index')
                ->with('status', 'Disciplina excluída com sucesso!');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Erro ao excluir disciplina',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir disciplina: ' . $e->getMessage());
        }
    }
}
