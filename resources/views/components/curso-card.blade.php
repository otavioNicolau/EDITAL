@props(['curso' => []])

@php
    $id = data_get($curso, 'id');
    $titulo = trim(data_get($curso, 'titulo')
        ?? data_get($curso, 'nome')
        ?? data_get($curso, 'name')
        ?? 'Curso disponível');
    $descricao = data_get($curso, 'descricao') ?? data_get($curso, 'description');
    $tipo = data_get($curso, 'tipo') ?? data_get($curso, 'type');
    $modalidade = data_get($curso, 'modalidade') ?? data_get($curso, 'modality');
    $cargaHoraria = data_get($curso, 'carga_horaria') ?? data_get($curso, 'cargaHoraria');
    $professores = collect(data_get($curso, 'professores', data_get($curso, 'teachers', [])))->map(function ($professor) {
        return is_array($professor) ? ($professor['nome'] ?? $professor['name'] ?? null) : $professor;
    })->filter()->implode(', ');
    $atualizadoEm = data_get($curso, 'atualizado_em')
        ?? data_get($curso, 'atualizacao')
        ?? data_get($curso, 'updated_at');
    $dataInicio = data_get($curso, 'data_inicio') ?? data_get($curso, 'inicio');
    $dataRetirada = data_get($curso, 'data_retirada') ?? data_get($curso, 'fim') ?? data_get($curso, 'data_fim');
    $totalAulas = data_get($curso, 'total_aulas');
    $totalAulasAssistidas = data_get($curso, 'total_aulas_visualizadas');
    $arquivado = filter_var(data_get($curso, 'arquivado', false), FILTER_VALIDATE_BOOLEAN);
    $favorito = filter_var(data_get($curso, 'favorito', false), FILTER_VALIDATE_BOOLEAN);
    $redirectUrl = data_get($curso, 'redirect_area_aluno');

    $formatDate = function ($value) {
        if (empty($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
        } catch (\Exception $exception) {
            return $value;
        }
    };

    $atualizadoLabel = $formatDate($atualizadoEm);
    $inicioLabel = $formatDate($dataInicio);
    $retiradaLabel = $formatDate($dataRetirada);
    $aulasAssistidas = is_numeric($totalAulasAssistidas) ? (int) $totalAulasAssistidas : null;
    $aulasTotais = is_numeric($totalAulas) ? max(0, (int) $totalAulas) : null;
    $progresso = $aulasTotais && $aulasAssistidas !== null
        ? (int) round(($aulasAssistidas / max(1, $aulasTotais)) * 100)
        : null;
    $accentClass = match (true) {
        $favorito => 'bg-warning',
        $arquivado => 'bg-secondary',
        default => 'bg-primary',
    };
@endphp

<div class="card h-100 shadow-sm border-0 rounded-3 bg-white position-relative overflow-hidden">
    <span class="position-absolute top-0 start-0 w-100 {{ $accentClass }}" style="height: 4px; opacity: .6;"></span>
    <div class="card-body d-flex flex-column">
        <div class="d-flex align-items-start justify-content-between gap-3">
            <div>
                <h5 class="card-title fw-semibold mb-1">{{ $titulo }}</h5>
                @if($descricao)
                    <p class="mb-0 text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($descricao), 180) }}</p>
                @endif
            </div>
            <div class="d-flex flex-column align-items-end gap-2">
                @if($favorito)
                    <span class="badge text-bg-warning text-dark"><i class="fas fa-star me-1"></i>Favorito</span>
                @endif
                @if($arquivado)
                    <span class="badge text-bg-secondary"><i class="fas fa-box-archive me-1"></i>Arquivado</span>
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
            @if($tipo)
                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                    <i class="fas fa-graduation-cap me-1"></i>{{ $tipo }}
                </span>
            @endif
            @if($modalidade)
                <span class="badge rounded-pill text-bg-light border border-light text-secondary">
                    <i class="fas fa-layer-group me-1"></i>{{ $modalidade }}
                </span>
            @endif
            @if($cargaHoraria)
                <span class="badge rounded-pill text-bg-light border border-light text-secondary">
                    <i class="fas fa-clock me-1"></i>{{ $cargaHoraria }}
                </span>
            @endif
        </div>

        <dl class="row small text-muted mb-3 mt-3">
            @if($professores)
                <dt class="col-5">Professores</dt>
                <dd class="col-7">{{ $professores }}</dd>
            @endif

            @if($inicioLabel)
                <dt class="col-5">Início</dt>
                <dd class="col-7">{{ $inicioLabel }}</dd>
            @endif

            @if($retiradaLabel)
                <dt class="col-5">Disponível até</dt>
                <dd class="col-7">{{ $retiradaLabel }}</dd>
            @endif

            @if($atualizadoLabel)
                <dt class="col-5">Atualizado</dt>
                <dd class="col-7">{{ $atualizadoLabel }}</dd>
            @endif
        </dl>

        @if($aulasTotais)
            <div class="mb-3">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span><i class="fas fa-list-check me-1"></i>{{ $aulasAssistidas ?? 0 }} / {{ $aulasTotais }} aulas</span>
                    @if($progresso !== null)
                        <span>{{ $progresso }}%</span>
                    @endif
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ max(0, min(100, $progresso ?? 0)) }}%;"></div>
                </div>
            </div>
        @endif

        <div class="mt-auto d-flex flex-column gap-2">
            @if($id)
                <a href="{{ route('cursos.show', ['id' => $id]) }}" class="btn btn-primary w-100">
                    Ver detalhes do curso
                </a>
            @else
                <span class="btn btn-secondary disabled w-100">Detalhes indisponíveis</span>
            @endif

            @if($redirectUrl)
                <a href="{{ $redirectUrl }}" class="btn btn-outline-secondary w-100" target="_blank" rel="noopener">
                    Abrir na área do aluno
                </a>
            @endif
        </div>
    </div>
</div>
