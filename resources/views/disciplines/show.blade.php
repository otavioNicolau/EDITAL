@extends('layouts.app')

@section('title', 'Disciplina: ' . $discipline->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="width: 4px; height: 40px; background-color: {{ $discipline->color ?? '#007bff' }}; border-radius: 2px;"></div>
                    <div>
                        <h1 class="h3 mb-0">{{ $discipline->name }}</h1>
                        @if($discipline->code)
                            <small class="text-muted">{{ $discipline->code }}</small>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('disciplines.edit', $discipline) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <a href="{{ route('disciplines.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                </div>
            </div>

            <!-- Informações da Disciplina -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informações da Disciplina</h5>
                        </div>
                        <div class="card-body">
                            @if($discipline->description)
                                <div class="mb-3">
                                    <strong>Descrição:</strong>
                                    <p class="mt-1">{{ $discipline->description }}</p>
                                </div>
                            @endif
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Status:</strong>
                                    <span class="badge bg-{{ $discipline->status === 'active' ? 'success' : 'secondary' }} ms-2">
                                        {{ $discipline->status === 'active' ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Criada em:</strong>
                                    <span class="ms-2">{{ $discipline->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Estatísticas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-0" id="totalTopics">-</h4>
                                        <small class="text-muted">Tópicos</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-0" id="totalStudyItems">-</h4>
                                    <small class="text-muted">Itens de Estudo</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tópicos da Disciplina -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tópicos da Disciplina</h5>
                    <a href="{{ route('topics.create') }}?discipline_id={{ $discipline->id }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Novo Tópico
                    </a>
                </div>
                <div class="card-body">
                    <div id="loadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                    
                    <div id="topicsContainer" class="d-none">
                        <!-- Tópicos serão carregados aqui via JavaScript -->
                    </div>
                    
                    <div id="emptyState" class="text-center py-5 d-none">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum tópico encontrado</h5>
                        <p class="text-muted">Esta disciplina ainda não possui tópicos cadastrados.</p>
                        <a href="{{ route('topics.create') }}?discipline_id={{ $discipline->id }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Criar Primeiro Tópico
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var disciplineId = {{ $discipline->id }};
    var loadingSpinner = document.getElementById('loadingSpinner');
    var topicsContainer = document.getElementById('topicsContainer');
    var emptyState = document.getElementById('emptyState');
    var totalTopicsElement = document.getElementById('totalTopics');
    var totalStudyItemsElement = document.getElementById('totalStudyItems');
    var initialTopics = @json($topicsSummary ?? []);
    var initialMetrics = @json($disciplineMetrics ?? []);

    if (initialMetrics.topics) {
        totalTopicsElement.textContent = initialMetrics.topics.total;
    }

    if (initialMetrics.study_items) {
        totalStudyItemsElement.textContent = initialMetrics.study_items.total;
    }

    if (initialTopics.length > 0) {
        renderTopics(initialTopics);
        loadingSpinner.classList.add('d-none');
    }

    loadDisciplineData();

    function loadDisciplineData() {
        Promise.all([
            fetch('/api/topics?discipline_id=' + disciplineId).then(function(response) { return response.json(); }),
            fetch('/api/study-items?discipline_id=' + disciplineId).then(function(response) { return response.json(); })
        ]).then(function(results) {
            var topicsData = results[0];
            var studyItemsData = results[1];
            var topics = Array.isArray(topicsData) ? topicsData : (topicsData.data || []);
            var studyItems = Array.isArray(studyItemsData) ? studyItemsData : (studyItemsData.data || []);

            totalTopicsElement.textContent = topics.length;
            totalStudyItemsElement.textContent = studyItems.length;

            renderTopics(topics);
        }).catch(function(error) {
            console.error('Erro ao carregar dados da disciplina:', error);
        }).finally(function() {
            loadingSpinner.classList.add('d-none');
        });
    }

    function renderTopics(topics) {
        if (topics.length === 0) {
            emptyState.classList.remove('d-none');
            return;
        }

        var topicsHtml = topics.map(function(topic) {
            return '<div class="card mb-3">' +
                '<div class="card-body">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                        '<div class="flex-grow-1">' +
                            '<h6 class="card-title mb-2">' +
                                '<a href="/topics/' + topic.id + '" class="text-decoration-none">' + topic.name + '</a>' +
                            '</h6>' +
                            (topic.description ? '<p class="card-text text-muted small mb-2">' + topic.description + '</p>' : '') +
                            '<div class="d-flex align-items-center gap-3">' +
                                '<span class="badge bg-' + getStatusColor(topic.status) + '">' + getStatusText(topic.status) + '</span>' +
                                (topic.block ? '<small class="text-muted"><i class="fas fa-cube me-1"></i>' + topic.block.name + '</small>' : '') +
                                '<small class="text-muted"><i class="fas fa-calendar me-1"></i>' + formatDate(topic.created_at) + '</small>' +
                            '</div>' +
                        '</div>' +
                        '<div class="dropdown">' +
                            '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">' +
                                '<i class="fas fa-ellipsis-v"></i>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                                '<li><a class="dropdown-item" href="/topics/' + topic.id + '"><i class="fas fa-eye me-2"></i>Visualizar</a></li>' +
                                '<li><a class="dropdown-item" href="/topics/' + topic.id + '/edit"><i class="fas fa-edit me-2"></i>Editar</a></li>' +
                            '</ul>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');

        topicsContainer.innerHTML = topicsHtml;
        topicsContainer.classList.remove('d-none');
    }

    function getStatusColor(status) {
        var colors = {
            'PLANNED': 'secondary',
            'STUDYING': 'warning',
            'REVIEW': 'info',
            'COMPLETED': 'success'
        };
        return colors[status] || 'secondary';
    }

    function getStatusText(status) {
        var texts = {
            'PLANNED': 'Planejado',
            'STUDYING': 'Estudando',
            'REVIEW': 'Revisão',
            'COMPLETED': 'Concluído'
        };
        return texts[status] || status;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR');
    }
});
</script>
@endsection
