<?php

namespace App\Http\Controllers;

use App\Exceptions\EstrategiaApiException;
use App\Services\EstrategiaApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CursosController extends Controller
{
    public function __construct(private readonly EstrategiaApiService $estrategiaApiService)
    {
    }

    public function show(int $id): View|RedirectResponse
    {
        try {
            $curso = $this->estrategiaApiService->obterCurso($id);
        } catch (EstrategiaApiException $exception) {
            return redirect()
                ->route('concursos.index')
                ->with('error', $exception->getMessage());
        }

        $modulos = $this->prepareModules($curso, detailed: false);

        return view('cursos.show', [
            'curso' => $curso,
            'modulos' => $modulos,
        ]);
    }

    public function aula(int $cursoId, string $slug): View|RedirectResponse
    {
        try {
            $curso = $this->estrategiaApiService->obterCurso($cursoId);
        } catch (EstrategiaApiException $exception) {
            return redirect()
                ->route('concursos.index')
                ->with('error', $exception->getMessage());
        }

        $modulos = $this->prepareModules($curso, detailed: true);

        $aula = collect($modulos)
            ->flatMap(fn ($modulo) => $modulo['aulas'])
            ->firstWhere('slug', $slug);

        if (!$aula) {
            return redirect()
                ->route('cursos.show', ['id' => $cursoId])
                ->with('error', 'Aula não encontrada.');
        }

        return view('cursos.aula', [
            'curso' => $curso,
            'aula' => $aula,
        ]);
    }

    private function prepareModules(array $curso, bool $detailed = false): array
    {
        $modulosBrutos = data_get($curso, 'modulos', data_get($curso, 'modules', []));

        if (!is_array($modulosBrutos) || empty($modulosBrutos)) {
            $aulasSoltas = data_get($curso, 'aulas', data_get($curso, 'lessons', []));
            if (!is_array($aulasSoltas)) {
                $aulasSoltas = [];
            }

            $modulosBrutos = [[
                'titulo' => 'Conteúdo do curso',
                'aulas' => $aulasSoltas,
            ]];
        }

        return collect($modulosBrutos)
            ->filter(fn ($modulo) => is_array($modulo))
            ->values()
            ->map(function ($modulo, int $moduloIndex) use ($curso, $detailed) {
                $titulo = data_get($modulo, 'titulo')
                    ?? data_get($modulo, 'nome')
                    ?? data_get($modulo, 'name')
                    ?? 'Módulo ' . ($moduloIndex + 1);

                $aulas = collect(data_get($modulo, 'aulas', data_get($modulo, 'lessons', [])))
                    ->filter(fn ($aula) => is_array($aula))
                    ->values()
                    ->map(function ($aula, int $index) use ($curso, $detailed) {
                        $enriched = $detailed ? $this->enrichAula($aula) : $aula;

                        $numero = data_get($enriched, 'numero')
                            ?? data_get($enriched, 'ordem')
                            ?? data_get($enriched, 'order')
                            ?? ($index + 1);

                        $nome = data_get($enriched, 'titulo')
                            ?? data_get($enriched, 'nome')
                            ?? 'Aula ' . $numero;
                        $nome = trim($nome) !== '' ? trim($nome) : 'Aula ' . $numero;

                        $baseSlug = Str::slug($nome . '-' . $numero);
                        $aulaId = data_get($enriched, 'id');
                        if ($baseSlug === '') {
                            $baseSlug = 'aula-' . $numero;
                        }
                        $slug = $baseSlug . ($aulaId ? '-' . $aulaId : '-' . ($index + 1));

                        $lessonData = [
                            'numero' => $numero,
                            'titulo' => $nome,
                            'descricao' => data_get($enriched, 'descricao')
                                ?? data_get($enriched, 'conteudo'),
                            'duracao' => data_get($enriched, 'duracao')
                                ?? data_get($enriched, 'duration'),
                            'slug' => $slug,
                            'aula_id' => $aulaId,
                            'curso_id' => data_get($curso, 'id'),
                        ];

                        if ($detailed) {
                            $videos = $this->normalizeVideos($enriched);
                            $materials = $this->extractMaterials($enriched, $videos);

                            $lessonData['videos'] = $videos;
                            $lessonData['materials'] = $materials;
                            $lessonData['atualizado_em'] = data_get($enriched, 'atualizado_em')
                                ?? data_get($enriched, 'updated_at');
                        }

                        return $lessonData;
                    })
                    ->values()
                    ->all();

                return [
                    'titulo' => $titulo,
                    'slug' => Str::slug($titulo) ?: 'modulo-' . ($moduloIndex + 1),
                    'aulas' => $aulas,
                ];
            })
            ->filter(fn ($modulo) => !empty($modulo['aulas']))
            ->values()
            ->all();
    }

    private function enrichAula(array $aula): array
    {
        if ($this->aulaPossuiVideo($aula) || empty($aula['id'])) {
            return $aula;
        }

        try {
            $detalhes = $this->estrategiaApiService->obterAula((int) $aula['id']);

            if (is_array($detalhes)) {
                return array_replace_recursive($aula, $detalhes);
            }
        } catch (EstrategiaApiException $exception) {
            Log::warning('Não foi possível carregar detalhes da aula', [
                'aula_id' => $aula['id'],
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable $throwable) {
            Log::warning('Erro inesperado ao carregar aula do Estratégia', [
                'aula_id' => $aula['id'],
                'message' => $throwable->getMessage(),
            ]);
        }

        return $aula;
    }

    private function aulaPossuiVideo(array $aula): bool
    {
        $videos = data_get($aula, 'videos');

        if (is_array($videos) && !empty($videos)) {
            foreach ($videos as $video) {
                if (is_array($video) && $this->buildVideoSources($video)) {
                    return true;
                }
            }
        }

        $singleVideo = data_get($aula, 'video');
        if (is_array($singleVideo) && $this->buildVideoSources($singleVideo)) {
            return true;
        }

        return $this->buildVideoSources($aula) !== null;
    }

    private function normalizeVideos(array $aula): array
    {
        $videosCollection = collect();

        $videos = data_get($aula, 'videos');
        if (is_array($videos)) {
            $videosCollection = $videosCollection->merge(collect($videos)->filter(fn ($video) => is_array($video)));
        }

        $singleVideo = data_get($aula, 'video');
        if (is_array($singleVideo)) {
            $videosCollection = $videosCollection->push($singleVideo);
        }

        $normalized = $videosCollection
            ->map(fn ($video) => $this->buildVideoSources($video))
            ->filter()
            ->values()
            ->all();

        if (empty($normalized)) {
            $fallback = $this->buildVideoSources($aula);

            if ($fallback !== null) {
                $normalized[] = $fallback;
            }
        }

        return $normalized;
    }

    private function buildVideoSources(array $data): ?array
    {
        $resolutions = [];

        $push = function (?string $quality, $value) use (&$resolutions) {
            if (!is_string($value)) {
                return;
            }

            $value = trim($value);

            if ($value === '' || !Str::startsWith($value, ['http://', 'https://'])) {
                return;
            }

            $qualityKey = strtoupper((string) ($quality ?: 'LINK'));

            if (!isset($resolutions[$qualityKey])) {
                $resolutions[$qualityKey] = $value;
            }
        };

        foreach (data_get($data, 'resolucoes', []) as $quality => $url) {
            $push($quality, $url);
        }

        $qualityFields = [
            'url_1080p' => '1080p',
            'url_720p' => '720p',
            'url_540p' => '540p',
            'url_480p' => '480p',
            'url_360p' => '360p',
            'url_240p' => '240p',
        ];

        foreach ($qualityFields as $field => $quality) {
            $push($quality, data_get($data, $field));
            $push($quality, data_get($data, 'video.' . $field));
        }

        $genericFields = [
            'url',
            'link',
            'download',
            'video_url',
            'url_video',
            'video.url',
            'links.video',
        ];

        foreach ($genericFields as $field) {
            $push(null, data_get($data, $field));
        }

        if (empty($resolutions)) {
            return null;
        }

        $order = ['1080P', '720P', '540P', '480P', '360P', '240P', 'LINK'];

        $sorted = collect($resolutions)
            ->map(fn ($url, $label) => ['label' => $label, 'url' => $url])
            ->sortBy(function ($item) use ($order) {
                $index = array_search($item['label'], $order, true);

                return $index === false ? PHP_INT_MAX : $index;
            })
            ->values()
            ->all();

        return [
            'titulo' => data_get($data, 'titulo') ?? data_get($data, 'nome') ?? null,
            'resolucoes' => $sorted,
        ];
    }

    private function extractMaterials(array $aula, array $videos): array
    {
        $materials = collect([
            ['label' => 'PDF', 'url' => data_get($aula, 'pdf')],
            ['label' => 'PDF (Grifado)', 'url' => data_get($aula, 'pdf_grifado')],
            ['label' => 'PDF (Simplificado)', 'url' => data_get($aula, 'pdf_simplificado')],
            ['label' => 'Resumo', 'url' => data_get($aula, 'resumo')],
            ['label' => 'Slides', 'url' => data_get($aula, 'slides.url') ?? data_get($aula, 'slide')],
            ['label' => 'Mapa Mental', 'url' => data_get($aula, 'mapa_mental')],
            ['label' => 'Técnico Concursos', 'url' => data_get($aula, 'tec_concursos')],
        ]);

        $videoMaterials = collect(data_get($aula, 'videos', []))
            ->filter(fn ($video) => is_array($video))
            ->flatMap(function ($video) {
                return [
                    ['label' => 'Áudio', 'url' => data_get($video, 'audio')],
                    ['label' => 'Resumo', 'url' => data_get($video, 'resumo')],
                    ['label' => 'Slides', 'url' => data_get($video, 'slide')],
                    ['label' => 'Mapa Mental', 'url' => data_get($video, 'mapa_mental')],
                ];
            });

        $materials = $materials->merge($videoMaterials)
            ->filter(fn ($item) => is_string($item['url']) && trim($item['url']) !== '')
            ->unique(fn ($item) => ($item['label'] ?? '') . '|' . $item['url'])
            ->values()
            ->all();

        return $materials;
    }
}
