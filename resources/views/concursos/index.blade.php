@extends('layouts.app')

@section('title', 'Concursos Estratégia')

@section('content')
<div class="mx-auto" style="max-width: 900px;">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center gap-3 mb-2">
            <span class="badge bg-primary rounded-circle p-3"><i class="fas fa-university"></i></span>
            <h1 class="h3 fw-semibold mb-0">Catálogo de Concursos</h1>
        </div>
        <p class="text-muted mb-0">Integração direta com a API do Estratégia Concursos</p>
    </div>

    <div id="concursos-loading" class="d-flex justify-content-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>

    <div id="concursos-content" class="d-none">
        @if($erro)
            <div class="alert alert-danger d-flex justify-content-between align-items-center" role="alert">
                <div>{{ $erro }}</div>
                <a href="{{ route('concursos.index') }}" class="btn btn-sm btn-danger">
                    Tentar novamente
                </a>
            </div>
        @endif

        <x-concurso-list :concursos="$concursos" />
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loader = document.getElementById('concursos-loading');
        const content = document.getElementById('concursos-content');

        if (loader) loader.classList.add('d-none');
        if (content) {
            content.classList.remove('d-none');
        }
    });
</script>
@endpush
