@extends('layouts.app')

@section('title', $titulo)

@section('content')
<div class="mx-auto" style="max-width: 1000px;">
    <div class="mb-4">
        <a href="{{ route('concursos.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar aos pacotes
        </a>

        <div class="bg-white rounded shadow-sm p-4">
            <div class="d-flex flex-column flex-md-row gap-3 align-items-start">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-semibold mb-2">{{ $titulo }}</h1>
                    @if($descricao)
                        <p class="text-muted mb-0">{{ $descricao }}</p>
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2 small">
                    @php
                        $totalCursos = is_countable($cursos) ? count($cursos) : 0;
                    @endphp

                    <span class="badge rounded-pill text-bg-light border border-light text-secondary">
                        <i class="fas fa-layer-group me-1"></i>{{ $totalCursos }} {{ \Illuminate\Support\Str::plural('curso', $totalCursos) }}
                    </span>
                    @if(($stats['arquivados'] ?? 0) > 0)
                        <span class="badge rounded-pill text-bg-light border border-light text-secondary">
                            <i class="fas fa-box-archive me-1"></i>{{ $stats['arquivados'] }} arquivado{{ $stats['arquivados'] === 1 ? '' : 's' }}
                        </span>
                    @endif
                    @if(($stats['favoritos'] ?? 0) > 0)
                        <span class="badge rounded-pill text-bg-warning text-dark">
                            <i class="fas fa-star me-1"></i>{{ $stats['favoritos'] }} favorito{{ $stats['favoritos'] === 1 ? '' : 's' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(($modalidadesResumo ?? collect())->isNotEmpty())
        <div class="bg-white rounded shadow-sm p-3 mb-4">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small me-2">Filtrar por modalidade:</span>
                <button type="button" class="btn btn-sm btn-primary text-white" data-filter-modalidade="todos">
                    Todos ({{ $stats['total'] ?? $totalCursos }})
                </button>
                @foreach($modalidadesResumo as $modalidade)
                    <button type="button" class="btn btn-sm btn-outline-primary" data-filter-modalidade="{{ $modalidade['slug'] }}">
                        {{ $modalidade['label'] }} ({{ $modalidade['count'] }})
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if(empty($cursos))
        <div class="alert alert-info">Nenhum curso disponível para este pacote no momento.</div>
    @else
        <div class="row g-3">
            @foreach($cursos as $curso)
                @php
                    $modalidadeSlug = \Illuminate\Support\Str::slug($curso['modalidade'] ?? 'Sem modalidade') ?: 'sem-modalidade';
                @endphp
                <div class="col-12 col-lg-6" data-modalidade="{{ $modalidadeSlug }}">
                    <x-curso-card :curso="$curso" />
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterButtons = document.querySelectorAll('[data-filter-modalidade]');
        const courseCards = document.querySelectorAll('[data-modalidade]');

        if (!filterButtons.length) {
            return;
        }

        const setActive = (button) => {
            filterButtons.forEach((btn) => {
                btn.classList.remove('btn-primary', 'text-white');
                btn.classList.add('btn-outline-primary');
            });

            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-primary', 'text-white');
        };

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.dataset.filterModalidade;

                courseCards.forEach((card) => {
                    const matches = target === 'todos' || card.dataset.modalidade === target;
                    card.classList.toggle('d-none', !matches);
                });

                setActive(button);
            });
        });
    });
</script>
@endpush
