@extends('layouts.app')

@section('title', $aula['titulo'] ?? 'Aula')

@php
    use Illuminate\Support\Carbon;

    $descricao = $aula['descricao'] ?? null;
    $atualizadoEm = $aula['atualizado_em'] ?? null;
    try {
        $atualizadoLabel = $atualizadoEm ? Carbon::parse($atualizadoEm)->format('d/m/Y H:i') : null;
    } catch (Exception $exception) {
        $atualizadoLabel = $atualizadoEm;
    }
@endphp

@section('content')
<div class="mx-auto" style="max-width: 900px;">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between flex-wrap gap-3">
            <div>
                <p class="text-muted small mb-1">Aula {{ $aula['numero'] ?? '-' }}</p>
                <h1 class="h4 fw-semibold mb-2">{{ $aula['titulo'] ?? 'Aula' }}</h1>
                @if($descricao)
                    <p class="text-muted">{!! nl2br(e(strip_tags($descricao))) !!}</p>
                @endif
            </div>
            <div class="text-end">
                <a href="{{ route('cursos.show', ['id' => $aula['curso_id']]) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar para o curso
                </a>
                @if($atualizadoLabel)
                    <p class="text-muted small mt-2 mb-0">Atualizado em {{ $atualizadoLabel }}</p>
                @endif
            </div>
        </div>
    </div>

    <x-video-player :videos="$aula['videos'] ?? []" />

    @if(!empty($aula['materials']))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="h6 mb-0">Materiais</h2>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($aula['materials'] as $material)
                <a href="{{ $material['url'] }}" target="_blank" rel="noreferrer noopener" class="btn btn-outline-secondary btn-sm w-100 w-sm-auto">
                    {{ $material['label'] }}
                </a>
            @endforeach
        </div>
            </div>
        </div>
    @endif

    @if(empty($aula['videos']) && empty($aula['materials']))
        <div class="alert alert-info mt-3">Nenhum conteúdo disponível.</div>
    @endif
</div>
@endsection
