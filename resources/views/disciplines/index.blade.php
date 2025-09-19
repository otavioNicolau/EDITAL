@extends('layouts.app')

@section('title', 'Disciplinas')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">🎓 Disciplinas</h1>
            <p class="mb-0 text-muted">Gerencie suas disciplinas de estudo</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('disciplines.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova Disciplina
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Nome da disciplina...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bloco</label>
                    <select class="form-select" id="blockFilter">
                        <option value="">Todos os blocos</option>
                        <!-- Será preenchido via JavaScript -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ordenar por</label>
                    <select class="form-select" id="sortFilter">
                        <option value="order">Ordem</option>
                        <option value="name">Nome</option>
                        <option value="created_at">Data de criação</option>
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

    <!-- Lista de Disciplinas -->
    <div class="row" id="disciplinesContainer">
        <!-- Será preenchido via JavaScript -->
    </div>

    <!-- Loading -->
    <div class="text-center py-5" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando disciplinas...</p>
    </div>

    <!-- Empty State -->
    <div class="text-center py-5 d-none" id="emptyState">
        <div class="mb-4">
            <i class="fas fa-graduation-cap fa-4x text-muted"></i>
        </div>
        <h4 class="text-muted">Nenhuma disciplina encontrada</h4>
        <p class="text-muted mb-4">Crie sua primeira disciplina para começar a organizar seus estudos.</p>
        <a href="{{ route('disciplines.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nova Disciplina
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
                <p>Tem certeza que deseja excluir esta disciplina?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita e todos os tópicos relacionados serão afetados.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let disciplines = [];
    let filteredDisciplines = [];
    let blocks = [];
    let currentDeletingDiscipline = null;

    // Elementos DOM
    const searchInput = document.getElementById('searchInput');
    const blockFilter = document.getElementById('blockFilter');
    const sortFilter = document.getElementById('sortFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const disciplinesContainer = document.getElementById('disciplinesContainer');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const emptyState = document.getElementById('emptyState');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const confirmDeleteBtn = document.getElementById('confirmDelete');

    // Carregar dados iniciais
    loadDisciplines();
    loadBlocks();

    // Event listeners
    searchInput.addEventListener('input', filterDisciplines);
    blockFilter.addEventListener('change', filterDisciplines);
    sortFilter.addEventListener('change', filterDisciplines);
    clearFiltersBtn.addEventListener('click', clearFilters);
    confirmDeleteBtn.addEventListener('click', deleteDiscipline);

    async function loadDisciplines() {
        try {
            showLoading();
            const response = await fetch('/api/disciplines');
            const data = await response.json();
            disciplines = Array.isArray(data) ? data : (data.data || []);
            filteredDisciplines = [...disciplines];
            renderDisciplines();
        } catch (error) {
            console.error('Erro ao carregar disciplinas:', error);
            showError('Erro ao carregar disciplinas');
        }
    }

    async function loadBlocks() {
        try {
            const response = await fetch('/api/blocks');
            const data = await response.json();
            blocks = Array.isArray(data) ? data : (data.data || []);
            renderBlockFilter();
        } catch (error) {
            console.error('Erro ao carregar blocos:', error);
        }
    }

    function renderBlockFilter() {
        const options = blocks.map(block => 
            `<option value="${block.id}">${block.name}</option>`
        ).join('');
        blockFilter.innerHTML = '<option value="">Todos os blocos</option>' + options;
    }

    function filterDisciplines() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedBlock = blockFilter.value;
        const sortBy = sortFilter.value;

        filteredDisciplines = disciplines.filter(discipline => {
            const matchesSearch = discipline.name.toLowerCase().includes(searchTerm) ||
                                (discipline.description && discipline.description.toLowerCase().includes(searchTerm));
            const matchesBlock = !selectedBlock || discipline.block_id == selectedBlock;
            
            return matchesSearch && matchesBlock;
        });

        // Ordenar
        filteredDisciplines.sort((a, b) => {
            switch (sortBy) {
                case 'name':
                    return a.name.localeCompare(b.name);
                case 'created_at':
                    return new Date(b.created_at) - new Date(a.created_at);
                case 'order':
                default:
                    return (a.order || 0) - (b.order || 0);
            }
        });

        renderDisciplines();
    }

    function clearFilters() {
        searchInput.value = '';
        blockFilter.value = '';
        sortFilter.value = 'order';
        filteredDisciplines = [...disciplines];
        renderDisciplines();
    }

    function renderDisciplines() {
        hideLoading();
        
        if (filteredDisciplines.length === 0) {
            showEmptyState();
            return;
        }

        hideEmptyState();
        
        const disciplinesHtml = filteredDisciplines.map(discipline => `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">${discipline.name}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/disciplines/${discipline.id}">
                                        <i class="fas fa-eye me-2"></i>Visualizar
                                    </a></li>
                                    <li><a class="dropdown-item" href="/disciplines/${discipline.id}/edit">
                                        <i class="fas fa-edit me-2"></i>Editar
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="showDeleteModal('${discipline.id}')">
                                        <i class="fas fa-trash me-2"></i>Excluir
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        
                        ${discipline.description ? `<p class="card-text text-muted small mb-3">${discipline.description}</p>` : ''}
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h6 mb-0">${discipline.topics_count || 0}</div>
                                    <small class="text-muted">Tópicos</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <div class="h6 mb-0">${discipline.completed_topics_count || 0}</div>
                                    <small class="text-muted">Concluídos</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="h6 mb-0">${discipline.progress_percentage || 0}%</div>
                                <small class="text-muted">Progresso</small>
                            </div>
                        </div>
                        
                        ${discipline.progress_percentage ? `
                        <div class="mt-3">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: ${discipline.progress_percentage}%" 
                                     aria-valuenow="${discipline.progress_percentage}" 
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <small class="text-muted">
                            <i class="fas fa-cube me-1"></i>
                            ${discipline.block ? discipline.block.name : 'Sem bloco'}
                        </small>
                    </div>
                </div>
            </div>
        `).join('');
        
        disciplinesContainer.innerHTML = disciplinesHtml;
    }

    function showDeleteModal(disciplineId) {
        const discipline = disciplines.find(d => d.id == disciplineId);
        if (!discipline) return;

        currentDeletingDiscipline = discipline;
        deleteModal.show();
    }

    async function deleteDiscipline() {
        if (!currentDeletingDiscipline) return;

        try {
            const response = await fetch(`/api/disciplines/${currentDeletingDiscipline.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                deleteModal.hide();
                showSuccess('Disciplina excluída com sucesso!');
                loadDisciplines(); // Recarregar dados
            } else {
                throw new Error('Erro ao excluir disciplina');
            }
        } catch (error) {
            console.error('Erro ao excluir disciplina:', error);
            showError('Erro ao excluir disciplina');
        }
    }

    function showLoading() {
        loadingSpinner.classList.remove('d-none');
        disciplinesContainer.classList.add('d-none');
        emptyState.classList.add('d-none');
    }

    function hideLoading() {
        loadingSpinner.classList.add('d-none');
        disciplinesContainer.classList.remove('d-none');
    }

    function showEmptyState() {
        emptyState.classList.remove('d-none');
        disciplinesContainer.classList.add('d-none');
    }

    function hideEmptyState() {
        emptyState.classList.add('d-none');
    }

    function showSuccess(message) {
        // Implementar notificação de sucesso
        console.log('Success:', message);
    }

    function showError(message) {
        // Implementar notificação de erro
        console.error('Error:', message);
    }

    // Tornar função global para uso nos dropdowns
    window.showDeleteModal = showDeleteModal;
});
</script>
@endsection
