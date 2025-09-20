<?php

namespace App\Http\Controllers;

use App\Exceptions\EstrategiaApiException;
use App\Services\EstrategiaApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ConcursosController extends Controller
{
    public function index(EstrategiaApiService $estrategiaApiService): View
    {
        try {
            $concursos = $estrategiaApiService->listarConcursos();
            $erro = null;
        } catch (EstrategiaApiException $exception) {
            $concursos = [];
            $erro = $exception->getMessage();
        }

        return view('concursos.index', [
            'concursos' => $concursos,
            'erro' => $erro,
        ]);
    }

    public function show(string $slug, EstrategiaApiService $estrategiaApiService): View|RedirectResponse
    {
        try {
            $concursos = $estrategiaApiService->listarConcursos();
        } catch (EstrategiaApiException $exception) {
            return redirect()
                ->route('concursos.index')
                ->with('error', $exception->getMessage());
        }

        $concurso = collect($concursos)->first(function ($item) use ($slug) {
            return data_get($item, 'slug') === $slug;
        });

        if (!$concurso && preg_match('/-(\d+)$/', $slug, $matches)) {
            $id = (int) $matches[1];
            $concurso = collect($concursos)->firstWhere('id', $id);
        }

        if (!$concurso) {
            return redirect()
                ->route('concursos.index')
                ->with('error', 'Concurso não encontrado.');
        }

        $titulo = data_get($concurso, 'titulo')
            ?? data_get($concurso, 'nome')
            ?? data_get($concurso, 'name')
            ?? 'Concurso';

        $descricao = data_get($concurso, 'descricao') ?? data_get($concurso, 'description');
        $cursos = data_get($concurso, 'cursos', data_get($concurso, 'courses', []));
        $cursosCollection = collect($cursos);

        $modalidadesResumo = $cursosCollection
            ->map(function ($curso) {
                $label = trim((string) ($curso['modalidade'] ?? 'Sem modalidade'));
                $label = $label !== '' ? $label : 'Sem modalidade';

                return [
                    'label' => $label,
                    'slug' => Str::slug($label) ?: 'sem-modalidade',
                ];
            })
            ->groupBy('slug')
            ->map(function ($items, $slug) {
                $label = $items->first()['label'] ?? 'Sem modalidade';

                return [
                    'slug' => $slug,
                    'label' => $label,
                    'count' => $items->count(),
                ];
            })
            ->values();

        $stats = [
            'total' => $cursosCollection->count(),
            'arquivados' => $cursosCollection->where('arquivado', true)->count(),
            'favoritos' => $cursosCollection->where('favorito', true)->count(),
        ];

        return view('concursos.show', [
            'concurso' => $concurso,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'cursos' => $cursos,
            'modalidadesResumo' => $modalidadesResumo,
            'stats' => $stats,
        ]);
    }
}
