@extends('layouts.app')

@section('title', 'Itens de Estudo')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">📚 Itens de Estudo</h1>
            <p class="text-muted">Gerencie seus materiais de estudo e acompanhe o progresso</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('study-items.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Novo Item
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">🔍 Buscar</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Buscar por título ou conteúdo...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">📋 Tópico</label>
                    <select class="form-select" id="topicFilter">
                        <option value="">Todos os tópicos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">📝 Tipo</label>
                    <select class="form-select" id="kindFilter">
                        <option value="">Todos os tipos</option>
                        <option value="QUESTION">❓ Questão</option>
                        <option value="CONCEPT">💡 Conceito</option>
                        <option value="EXERCISE">✏️ Exercício</option>
                        <option value="VIDEO">🎥 Vídeo</option>
                        <option value="ARTICLE">📄 Artigo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">📊 Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">Todos os status</option>
                        <option value="NEW">🆕 Novo</option>
                        <option value="LEARNING">📖 Aprendendo</option>
                        <option value="REVIEW">🔄 Revisão</option>
                        <option value="MASTERED">✅ Dominado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">⚙️ Ações</label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary" id="clearFilters">
                            <i class="fas fa-times me-1"></i>Limpar
                        </button>
                        <button class="btn btn-outline-info" id="refreshBtn">
                            <i class="fas fa-sync-alt me-1"></i>Atualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4" id="statsCards">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h2 text-primary mb-1" id="totalItems">0</div>
                    <div class="text-muted">Total de Itens</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h2 text-warning mb-1" id="newItems">0</div>
                    <div class="text-muted">Novos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h2 text-info mb-1" id="reviewItems">0</div>
                    <div class="text-muted">Em Revisão</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="h2 text-success mb-1" id="masteredItems">0</div>
                    <div class="text-muted">Dominados</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div class="text-center py-5" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando itens de estudo...</p>
    </div>

    <!-- Lista de Itens -->
    <div id="studyItemsList" class="d-none">
        <div class="row" id="itemsContainer">
            <!-- Itens serão inseridos aqui via JavaScript -->
        </div>
    </div>

    <!-- Empty State -->
    <div class="text-center py-5 d-none" id="emptyState">
        <div class="mb-4">
            <i class="fas fa-book-open fa-4x text-muted"></i>
        </div>
        <h4 class="text-muted">Nenhum item de estudo encontrado</h4>
        <p class="text-muted mb-4">Crie seu primeiro item de estudo para começar a organizar seus materiais.</p>
        <a href="{{ route('study-items.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Criar Primeiro Item
        </a>
    </div>
</div>

<!-- Modal de Visualização -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalTitle">Detalhes do Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Conteúdo será inserido via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="editFromViewBtn">
                    <i class="fas fa-edit me-2"></i>Editar
                </button>
            </div>
        </div>
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
                <p>Tem certeza que deseja excluir este item de estudo?</p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Excluir</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let studyItems = [];
    let topics = [];
    let filteredItems = [];
    let currentViewingItem = null;
    let currentDeletingItem = null;

    // Elementos DOM
    const loadingSpinner = document.getElementById('loadingSpinner');
    const studyItemsList = document.getElementById('studyItemsList');
    const emptyState = document.getElementById('emptyState');
    const itemsContainer = document.getElementById('itemsContainer');
    
    // Filtros
    const searchInput = document.getElementById('searchInput');
    const topicFilter = document.getElementById('topicFilter');
    const kindFilter = document.getElementById('kindFilter');
    const statusFilter = document.getElementById('statusFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const refreshBtn = document.getElementById('refreshBtn');
    
    // Modals
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const editFromViewBtn = document.getElementById('editFromViewBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    // Event listeners
    searchInput.addEventListener('input', debounce(applyFilters, 300));
    topicFilter.addEventListener('change', applyFilters);
    kindFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    clearFiltersBtn.addEventListener('click', clearFilters);
    refreshBtn.addEventListener('click', loadData);
    editFromViewBtn.addEventListener('click', editFromView);
    confirmDeleteBtn.addEventListener('click', deleteItem);

    // Carregar dados iniciais
    loadData();

    async function loadData() {
        try {
            showLoading();
            
            // Carregar itens e tópicos em paralelo
            const [itemsResponse, topicsResponse] = await Promise.all([
                fetch('/api/study-items'),
                fetch('/api/topics')
            ]);

            studyItems = await itemsResponse.json();
            topics = await topicsResponse.json();

            populateTopicFilter();
            applyFilters();
            updateStats();
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            showError('Erro ao carregar dados');
        }
    }

    function populateTopicFilter() {
        topicFilter.innerHTML = '<option value="">Todos os tópicos</option>';
        topics.forEach(topic => {
            const option = document.createElement('option');
            option.value = topic.id;
            option.textContent = `${topic.block.name} • ${topic.name}`;
            topicFilter.appendChild(option);
        });
    }

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedTopic = topicFilter.value;
        const selectedKind = kindFilter.value;
        const selectedStatus = statusFilter.value;

        filteredItems = studyItems.filter(item => {
            const searchMatch = !searchTerm || 
                item.title.toLowerCase().includes(searchTerm) ||
                (item.notes && item.notes.toLowerCase().includes(searchTerm)) ||
                item.topic.name.toLowerCase().includes(searchTerm) ||
                item.topic.block.name.toLowerCase().includes(searchTerm);
            
            const topicMatch = !selectedTopic || item.topic_id == selectedTopic;
            const kindMatch = !selectedKind || item.kind === selectedKind;
            const statusMatch = !selectedStatus || item.status === selectedStatus;
            
            return searchMatch && topicMatch && kindMatch && statusMatch;
        });

        renderItems();
    }

    function renderItems() {
        hideLoading();
        
        if (filteredItems.length === 0) {
            showEmptyState();
            return;
        }

        showItemsList();
        
        itemsContainer.innerHTML = filteredItems.map(item => `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge ${getKindBadgeClass(item.kind)}">${getKindLabel(item.kind)}</span>
                            <span class="badge ${getStatusBadgeClass(item.status)}">${getStatusLabel(item.status)}</span>
                        </div>
                        <h6 class="card-title">${item.title}</h6>
                        <p class="card-text small text-muted mb-2">
                            <i class="fas fa-folder me-1"></i>${item.topic.block.name} • ${item.topic.name}
                        </p>
                        ${item.notes ? `<p class="card-text small">${truncateText(item.notes, 100)}</p>` : ''}
                        ${item.url ? `<p class="card-text"><small><a href="${item.url}" target="_blank" class="text-decoration-none"><i class="fas fa-external-link-alt me-1"></i>Link</a></small></p>` : ''}
                        ${item.due_at ? `<p class="card-text"><small class="text-muted"><i class="fas fa-clock me-1"></i>Vence: ${formatDate(item.due_at)}</small></p>` : ''}
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary flex-fill" onclick="viewItem('${item.id}')">
                                <i class="fas fa-eye me-1"></i>Ver
                            </button>
                            <a href="/study-items/${item.id}/edit" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger" onclick="showDeleteModal('${item.id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function updateStats() {
        document.getElementById('totalItems').textContent = studyItems.length;
        document.getElementById('newItems').textContent = studyItems.filter(item => item.status === 'NEW').length;
        document.getElementById('reviewItems').textContent = studyItems.filter(item => item.status === 'REVIEW').length;
        document.getElementById('masteredItems').textContent = studyItems.filter(item => item.status === 'MASTERED').length;
    }

    function viewItem(itemId) {
        const item = studyItems.find(i => i.id == itemId);
        if (!item) return;

        currentViewingItem = item;
        
        document.getElementById('viewModalTitle').innerHTML = `
            ${getKindIcon(item.kind)} ${item.title}
        `;
        
        document.getElementById('viewModalBody').innerHTML = `
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <h6>Conteúdo:</h6>
                        <p class="text-muted">${item.notes || 'Sem conteúdo'}</p>
                    </div>
                    ${item.url ? `
                        <div class="mb-3">
                            <h6>Link:</h6>
                            <a href="${item.url}" target="_blank" class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-1"></i>${item.url}
                            </a>
                        </div>
                    ` : ''}
                    ${item.metadata ? `
                        <div class="mb-3">
                            <h6>Metadados:</h6>
                            <pre class="bg-light p-2 rounded small">${item.metadata}</pre>
                        </div>
                    ` : ''}
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <h6>Informações:</h6>
                        <p><strong>Tópico:</strong><br>${item.topic.block.name} • ${item.topic.name}</p>
                        <p><strong>Tipo:</strong><br>${getKindLabel(item.kind)}</p>
                        <p><strong>Status:</strong><br>${getStatusLabel(item.status)}</p>
                        <p><strong>Criado:</strong><br>${formatDate(item.created_at)}</p>
                        ${item.due_at ? `<p><strong>Vencimento:</strong><br>${formatDate(item.due_at)}</p>` : ''}
                        ${item.ease ? `<p><strong>Facilidade:</strong><br>${item.ease}</p>` : ''}
                        ${item.interval ? `<p><strong>Intervalo:</strong><br>${item.interval} dias</p>` : ''}
                    </div>
                </div>
            </div>
        `;
        
        viewModal.show();
    }

    function editFromView() {
        if (currentViewingItem) {
            window.location.href = `/study-items/${currentViewingItem.id}/edit`;
        }
    }

    function showDeleteModal(itemId) {
        const item = studyItems.find(i => i.id == itemId);
        if (!item) return;

        currentDeletingItem = item;
        deleteModal.show();
    }

    async function deleteItem() {
        if (!currentDeletingItem) return;

        try {
            const response = await fetch(`/api/study-items/${currentDeletingItem.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                deleteModal.hide();
                showSuccess('Item excluído com sucesso!');
                loadData(); // Recarregar dados
            } else {
                throw new Error('Erro ao excluir item');
            }
        } catch (error) {
            console.error('Erro ao excluir item:', error);
            showError('Erro ao excluir item');
        }
    }

    function clearFilters() {
        searchInput.value = '';
        topicFilter.value = '';
        kindFilter.value = '';
        statusFilter.value = '';
        applyFilters();
    }

    // Utility functions
    function getKindBadgeClass(kind) {
        switch (kind) {
            case 'QUESTION': return 'bg-warning';
            case 'CONCEPT': return 'bg-info';
            case 'EXERCISE': return 'bg-primary';
            case 'VIDEO': return 'bg-success';
            case 'ARTICLE': return 'bg-secondary';
            default: return 'bg-light text-dark';
        }
    }

    function getKindLabel(kind) {
        switch (kind) {
            case 'QUESTION': return '❓ Questão';
            case 'CONCEPT': return '💡 Conceito';
            case 'EXERCISE': return '✏️ Exercício';
            case 'VIDEO': return '🎥 Vídeo';
            case 'ARTICLE': return '📄 Artigo';
            default: return '📄 Outro';
        }
    }

    function getKindIcon(kind) {
        switch (kind) {
            case 'QUESTION': return '❓';
            case 'CONCEPT': return '💡';
            case 'EXERCISE': return '✏️';
            case 'VIDEO': return '🎥';
            case 'ARTICLE': return '📄';
            default: return '📄';
        }
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'NEW': return 'bg-light text-dark';
            case 'LEARNING': return 'bg-warning';
            case 'REVIEW': return 'bg-info';
            case 'MASTERED': return 'bg-success';
            default: return 'bg-secondary';
        }
    }

    function getStatusLabel(status) {
        switch (status) {
            case 'NEW': return '🆕 Novo';
            case 'LEARNING': return '📖 Aprendendo';
            case 'REVIEW': return '🔄 Revisão';
            case 'MASTERED': return '✅ Dominado';
            default: return '❓ Indefinido';
        }
    }

    function truncateText(text, maxLength) {
        return text && text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR');
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function showLoading() {
        loadingSpinner.classList.remove('d-none');
        studyItemsList.classList.add('d-none');
        emptyState.classList.add('d-none');
    }

    function hideLoading() {
        loadingSpinner.classList.add('d-none');
    }

    function showItemsList() {
        studyItemsList.classList.remove('d-none');
        emptyState.classList.add('d-none');
    }

    function showEmptyState() {
        studyItemsList.classList.add('d-none');
        emptyState.classList.remove('d-none');
    }

    function showSuccess(message) {
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            document.body.removeChild(toast);
        });
    }

    function showError(message) {
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-exclamation-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            document.body.removeChild(toast);
        });
    }

    // Tornar funções globais para uso nos elementos HTML
    window.viewItem = viewItem;
    window.showDeleteModal = showDeleteModal;
});
</script>
@endpush