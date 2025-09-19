@extends('layouts.app')

@section('title', 'Tópicos')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">📚 Tópicos</h1>
            <p class="mb-0 text-muted">Gerencie seus tópicos de estudo</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('topics.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Novo Tópico
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Nome do tópico...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bloco</label>
                    <select class="form-select" id="blockFilter">
                        <option value="">Todos os blocos</option>
                        <!-- Será preenchido via JavaScript -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Disciplina</label>
                    <select class="form-select" id="disciplineFilter">
                        <option value="">Todas as disciplinas</option>
                        <!-- Será preenchido via JavaScript -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">Todos os status</option>
                        <option value="PLANNED">Planejado</option>
                        <option value="STUDYING">Estudando</option>
                        <option value="COMPLETED">Concluído</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="fas fa-times me-2"></i>Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Tópicos -->
    <div class="row" id="topicsContainer">
        <!-- Será preenchido via JavaScript -->
    </div>

    <!-- Loading -->
    <div class="text-center py-5" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando tópicos...</p>
    </div>

    <!-- Empty State -->
    <div class="text-center py-5 d-none" id="emptyState">
        <div class="mb-4">
            <i class="fas fa-book-open fa-4x text-muted"></i>
        </div>
        <h4 class="text-muted">Nenhum tópico encontrado</h4>
        <p class="text-muted mb-4">Crie seu primeiro tópico para começar a organizar seus estudos.</p>
        <a href="{{ route('topics.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Criar Primeiro Tópico
        </a>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este tópico?</p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Esta ação não pode ser desfeita e todos os itens de estudo relacionados também serão excluídos.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Excluir</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let topics = [];
    let blocks = [];
    let disciplines = [];
    let filteredTopics = [];
    let deleteTopicId = null;

    // Elementos DOM
    const searchInput = document.getElementById('searchInput');
    const blockFilter = document.getElementById('blockFilter');
    const disciplineFilter = document.getElementById('disciplineFilter');
    const statusFilter = document.getElementById('statusFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const topicsContainer = document.getElementById('topicsContainer');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const emptyState = document.getElementById('emptyState');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const confirmDeleteBtn = document.getElementById('confirmDelete');

    // Carregar dados iniciais
    loadTopics();
    loadBlocks();
    loadDisciplines();

    // Event listeners
    searchInput.addEventListener('input', filterTopics);
    blockFilter.addEventListener('change', filterTopics);
    disciplineFilter.addEventListener('change', filterTopics);
    statusFilter.addEventListener('change', filterTopics);
    clearFiltersBtn.addEventListener('click', clearFilters);
    confirmDeleteBtn.addEventListener('click', deleteTopic);

    async function loadTopics() {
        try {
            showLoading();
            const response = await fetch('/api/topics');
            topics = await response.json();
            filteredTopics = [...topics];
            renderTopics();
        } catch (error) {
            console.error('Erro ao carregar tópicos:', error);
            showError('Erro ao carregar tópicos');
        }
    }

    async function loadBlocks() {
        try {
            const response = await fetch('/api/blocks');
            const data = await response.json();
            // O BlockController retorna { data: blocks, total: count }
            blocks = data.data || [];
            renderBlockFilter();
        } catch (error) {
            console.error('Erro ao carregar blocos:', error);
        }
    }

    async function loadDisciplines() {
        try {
            const response = await fetch('/api/disciplines');
            const data = await response.json();
            disciplines = data.data || [];
            renderDisciplineFilter();
        } catch (error) {
            console.error('Erro ao carregar disciplinas:', error);
        }
    }

    function renderBlockFilter() {
        const options = blocks.map(block => 
            `<option value="${block.id}">${block.name}</option>`
        ).join('');
        blockFilter.innerHTML = '<option value="">Todos os blocos</option>' + options;
    }

    function renderDisciplineFilter() {
        const options = disciplines.map(discipline => 
            `<option value="${discipline.id}">${discipline.name}</option>`
        ).join('');
        disciplineFilter.innerHTML = '<option value="">Todas as disciplinas</option>' + options;
    }

    function filterTopics() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedBlock = blockFilter.value;
        const selectedDiscipline = disciplineFilter.value;
        const selectedStatus = statusFilter.value;

        filteredTopics = topics.filter(topic => {
            const matchesSearch = topic.name.toLowerCase().includes(searchTerm);
            const matchesBlock = !selectedBlock || topic.block_id === selectedBlock;
            const matchesDiscipline = !selectedDiscipline || topic.discipline_id == selectedDiscipline;
            const matchesStatus = !selectedStatus || topic.status === selectedStatus;
            
            return matchesSearch && matchesBlock && matchesDiscipline && matchesStatus;
        });

        renderTopics();
    }

    function clearFilters() {
        searchInput.value = '';
        blockFilter.value = '';
        disciplineFilter.value = '';
        statusFilter.value = '';
        filteredTopics = [...topics];
        renderTopics();
    }

    function renderTopics() {
        hideLoading();
        
        if (filteredTopics.length === 0) {
            showEmptyState();
            return;
        }

        hideEmptyState();
        
        const topicsHtml = filteredTopics.map(topic => `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">${topic.name}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/topics/${topic.id}">
                                        <i class="fas fa-eye me-2"></i>Visualizar
                                    </a></li>
                                    <li><a class="dropdown-item" href="/topics/${topic.id}/edit">
                                        <i class="fas fa-edit me-2"></i>Editar
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="showDeleteModal('${topic.id}')">
                                        <i class="fas fa-trash me-2"></i>Excluir
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        ${topic.description ? `<p class="card-text text-muted small">${topic.description}</p>` : ''}
                        
                        <div class="mb-3">
                            <span class="badge bg-light text-dark">${topic.block?.name || 'Sem bloco'}</span>
                            <span class="badge ${getStatusBadgeClass(topic.status)}">${getStatusLabel(topic.status)}</span>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h6 mb-0 text-primary">${topic.study_items_count || 0}</div>
                                <small class="text-muted">Itens</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0 text-success">${topic.reviews_count || 0}</div>
                                <small class="text-muted">Revisões</small>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0 text-info">${calculateProgress(topic)}%</div>
                                <small class="text-muted">Progresso</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="/topics/${topic.id}" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i>Ver Detalhes
                            </a>
                            <a href="/topics/${topic.id}/edit" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        topicsContainer.innerHTML = topicsHtml;
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'PLANNED': return 'bg-secondary';
            case 'STUDYING': return 'bg-warning';
            case 'COMPLETED': return 'bg-success';
            default: return 'bg-secondary';
        }
    }

    function getStatusLabel(status) {
        switch (status) {
            case 'PLANNED': return 'Planejado';
            case 'STUDYING': return 'Estudando';
            case 'COMPLETED': return 'Concluído';
            default: return 'Indefinido';
        }
    }

    function calculateProgress(topic) {
        if (!topic.study_items_count || topic.study_items_count === 0) return 0;
        // Aqui você pode implementar uma lógica mais complexa baseada no status dos itens
        return topic.status === 'COMPLETED' ? 100 : 
               topic.status === 'STUDYING' ? 50 : 0;
    }

    function showDeleteModal(topicId) {
        deleteTopicId = topicId;
        deleteModal.show();
    }

    async function deleteTopic() {
        if (!deleteTopicId) return;

        try {
            const response = await fetch(`/api/topics/${deleteTopicId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                deleteModal.hide();
                showSuccess('Tópico excluído com sucesso');
                loadTopics(); // Recarregar lista
            } else {
                throw new Error('Erro ao excluir tópico');
            }
        } catch (error) {
            console.error('Erro ao excluir tópico:', error);
            showError('Erro ao excluir tópico');
        }
    }

    function showLoading() {
        loadingSpinner.classList.remove('d-none');
        topicsContainer.classList.add('d-none');
        emptyState.classList.add('d-none');
    }

    function hideLoading() {
        loadingSpinner.classList.add('d-none');
        topicsContainer.classList.remove('d-none');
    }

    function showEmptyState() {
        emptyState.classList.remove('d-none');
        topicsContainer.classList.add('d-none');
    }

    function hideEmptyState() {
        emptyState.classList.add('d-none');
    }

    function showSuccess(message) {
        // Implementar toast de sucesso
        console.log('Success:', message);
    }

    function showError(message) {
        // Implementar toast de erro
        console.error('Error:', message);
    }

    // Tornar função global para uso nos dropdowns
    window.showDeleteModal = showDeleteModal;
});
</script>
@endpush