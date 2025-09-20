@extends('layouts.app')

@section('title', data_get($curso, 'titulo', data_get($curso, 'nome', 'Curso')))

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $tituloCurso = data_get($curso, 'titulo') ?? data_get($curso, 'nome') ?? data_get($curso, 'name') ?? 'Curso';
    $descricaoCurso = data_get($curso, 'descricao') ?? data_get($curso, 'description');
    $cargaHoraria = data_get($curso, 'carga_horaria') ?? data_get($curso, 'cargaHoraria');
    $professores = collect(data_get($curso, 'professores', data_get($curso, 'teachers', [])))
        ->map(fn ($professor) => is_array($professor) ? ($professor['nome'] ?? $professor['name'] ?? null) : $professor)
        ->filter()
        ->implode(', ');
    $tags = collect(data_get($curso, 'tags', []))->filter()->values();

    $ultimaAtualizacao = data_get($curso, 'atualizado_em') ?? data_get($curso, 'updated_at');
    try {
        $ultimaAtualizacaoLabel = $ultimaAtualizacao ? Carbon::parse($ultimaAtualizacao)->format('d/m/Y') : null;
    } catch (Exception $exception) {
        $ultimaAtualizacaoLabel = $ultimaAtualizacao;
    }
@endphp

@section('content')
<div class="mx-auto" style="max-width: 1000px;">
    <div class="bg-white rounded shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="h3 fw-semibold mb-2">{{ $tituloCurso }}</h1>
                @if($descricaoCurso)
                    <p class="text-muted mb-2">{!! nl2br(e(strip_tags($descricaoCurso))) !!}</p>
                @endif

                <div class="d-flex flex-wrap gap-2 align-items-center text-muted small">
                    @if($professores)
                        <span><i class="fas fa-user-graduate me-1"></i>{{ $professores }}</span>
                    @endif
                    @if($cargaHoraria)
                        <span><i class="fas fa-clock me-1"></i>{{ $cargaHoraria }}</span>
                    @endif
                    @if($ultimaAtualizacaoLabel)
                        <span><i class="fas fa-calendar-check me-1"></i>Atualizado em {{ $ultimaAtualizacaoLabel }}</span>
                    @endif
                </div>
            </div>

            <div class="text-end">
                <a href="{{ route('concursos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar aos concursos
                </a>
                @if($tags->isNotEmpty())
                    <div class="mt-2 d-flex flex-wrap gap-2 justify-content-end">
                        @foreach($tags as $tag)
                            <span class="badge bg-primary-subtle text-primary-emphasis">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(empty($modulos))
        <div class="alert alert-info">Nenhum conteúdo disponível.</div>
    @else
        <div class="row g-3">
            @foreach($modulos as $modulo)
                @foreach($modulo['aulas'] as $aula)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <p class="text-muted small mb-1">{{ $modulo['titulo'] }}</p>
                                <h5 class="fw-semibold mb-2">Aula {{ $aula['numero'] }} &mdash; {{ $aula['titulo'] }}</h5>
                                @if($aula['descricao'])
                                    <p class="text-muted">{{ Str::limit(strip_tags($aula['descricao']), 120) }}</p>
                                @endif
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">{{ $aula['duracao'] ?? 'Conteúdo disponível' }}</span>
                                    <a href="{{ route('cursos.aula', ['curso' => $aula['curso_id'], 'slug' => $aula['slug']]) }}" class="btn btn-primary btn-sm">
                                        Ver conteúdo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif
</div>
@endsection
