@extends('layouts.app')

@section('title', 'Detalhes do Tópico')

@section('content')
<div class="container-fluid py-4" id="topicContainer">
    <!-- Loading -->
    <div class="text-center py-5" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando tópico...</p>
    </div>

    <!-- Conteúdo do Tópico (será preenchido via JavaScript) -->
    <div id="topicContent" class="d-none">
        <!-- Header será inserido aqui -->
        <div id="topicHeader"></div>
        
        <!-- Stats será inserido aqui -->
        <div id="topicStats"></div>
        
        <!-- Itens de Estudo será inserido aqui -->
        <div id="studyItems"></div>
    </div>

    <!-- Error State -->
    <div class="text-center py-5 d-none" id="errorState">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
        </div>
        <h4 class="text-danger">Erro ao carregar tópico</h4>
        <p class="text-muted mb-4" id="errorMessage">Tópico não encontrado ou erro de conexão.</p>
        <a href="{{ route('topics.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left me-2"></i>Voltar para Tópicos
        </a>
    </div>
</div>

<!-- Modal de Edição Rápida de Status -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Novo Status</label>
                    <select class="form-select" id="newStatus">
                        <option value="PLANNED">📋 Planejado</option>
                        <option value="STUDYING">📖 Estudando</option>
                        <option value="COMPLETED">✅ Concluído</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="updateStatusBtn">Atualizar</button>
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
                <p>Tem certeza que deseja excluir este tópico?</p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Esta ação não pode ser desfeita e todos os itens de estudo relacionados também serão excluídos.
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
    const topicId = window.location.pathname.split('/').pop();
    let currentTopic = null;

    // Elementos DOM
    const loadingSpinner = document.getElementById('loadingSpinner');
    const topicContent = document.getElementById('topicContent');
    const errorState = document.getElementById('errorState');
    const errorMessage = document.getElementById('errorMessage');
    
    // Modals
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const newStatusSelect = document.getElementById('newStatus');
    const updateStatusBtn = document.getElementById('updateStatusBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    // Event listeners
    updateStatusBtn.addEventListener('click', updateStatus);
    confirmDeleteBtn.addEventListener('click', deleteTopic);

    // Carregar dados do tópico
    loadTopic();

    async function loadTopic() {
        try {
            showLoading();
            const response = await fetch(`/api/topics/${topicId}`);
            
            if (!response.ok) {
                throw new Error('Tópico não encontrado');
            }
            
            currentTopic = await response.json();
            renderTopic();
        } catch (error) {
            console.error('Erro ao carregar tópico:', error);
            showError(error.message);
        }
    }

    function renderTopic() {
        hideLoading();
        showContent();
        
        // Renderizar header
        document.getElementById('topicHeader').innerHTML = `
            <div class="row align-items-center mb-4">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/topics">Tópicos</a></li>
                            <li class="breadcrumb-item"><a href="/blocks/${currentTopic.block.id}">${currentTopic.block.name}</a></li>
                            <li class="breadcrumb-item active">${currentTopic.name}</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h1 class="h3 mb-0 text-gray-800">${currentTopic.name}</h1>
                        <span class="badge ${getStatusBadgeClass(currentTopic.status)} fs-6">${getStatusLabel(currentTopic.status)}</span>
                    </div>
                    ${currentTopic.description ? `<p class="text-muted mb-0">${currentTopic.description}</p>` : ''}
                    ${currentTopic.tags ? `<div class="mt-2">${renderTags(currentTopic.tags)}</div>` : ''}
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="showStatusModal()">
                            <i class="fas fa-edit me-2"></i>Alterar Status
                        </button>
                        <a href="/topics/${currentTopic.id}/edit" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/study-items/create?topic_id=${currentTopic.id}">
                                    <i class="fas fa-plus me-2"></i>Adicionar Item
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="showDeleteModal()">
                                    <i class="fas fa-trash me-2"></i>Excluir Tópico
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Renderizar estatísticas
        document.getElementById('topicStats').innerHTML = `
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="h2 text-primary mb-1">${currentTopic.study_items_count || 0}</div>
                            <div class="text-muted">Itens de Estudo</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="h2 text-success mb-1">${currentTopic.reviews_count || 0}</div>
                            <div class="text-muted">Revisões</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="h2 text-info mb-1">${calculateProgress()}%</div>
                            <div class="text-muted">Progresso</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="h2 text-warning mb-1">${formatDate(currentTopic.created_at)}</div>
                            <div class="text-muted">Criado em</div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Renderizar itens de estudo
        renderStudyItems();
    }

    function renderStudyItems() {
        const studyItems = currentTopic.study_items || [];
        
        let itemsHtml = `
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">📚 Itens de Estudo</h5>
                    <a href="/study-items/create?topic_id=${currentTopic.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Novo Item
                    </a>
                </div>
                <div class="card-body">
        `;

        if (studyItems.length === 0) {
            itemsHtml += `
                <div class="text-center py-4">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum item de estudo</h5>
                    <p class="text-muted">Adicione itens de estudo para começar a organizar seu conteúdo.</p>
                    <a href="/study-items/create?topic_id=${currentTopic.id}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Adicionar Primeiro Item
                    </a>
                </div>
            `;
        } else {
            itemsHtml += `
                <div class="row">
                    ${studyItems.map(item => `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge ${getItemKindBadgeClass(item.kind)}">${getItemKindLabel(item.kind)}</span>
                                        <span class="badge ${getItemStatusBadgeClass(item.status)}">${getItemStatusLabel(item.status)}</span>
                                    </div>
                                    <h6 class="card-title">${item.title}</h6>
                                    ${item.content ? `<p class="card-text small text-muted">${truncateText(item.content, 100)}</p>` : ''}
                                    ${item.url ? `<p class="card-text"><small><a href="${item.url}" target="_blank" class="text-decoration-none"><i class="fas fa-external-link-alt me-1"></i>Link</a></small></p>` : ''}
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-1">
                                        <a href="/study-items/${item.id}" class="btn btn-sm btn-outline-primary flex-fill">Ver</a>
                                        <a href="/study-items/${item.id}/edit" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        itemsHtml += `
                </div>
            </div>
        `;

        document.getElementById('studyItems').innerHTML = itemsHtml;
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

    function getItemKindBadgeClass(kind) {
        switch (kind) {
            case 'SUMMARY': return 'bg-info';
            case 'QUESTION': return 'bg-warning';
            case 'LAW': return 'bg-danger';
            case 'VIDEO': return 'bg-success';
            default: return 'bg-secondary';
        }
    }

    function getItemKindLabel(kind) {
        switch (kind) {
            case 'SUMMARY': return '📖 Resumo';
            case 'QUESTION': return '❓ Questão';
            case 'LAW': return '⚖️ Lei';
            case 'VIDEO': return '🎥 Vídeo';
            default: return '📄 Outro';
        }
    }

    function getItemStatusBadgeClass(status) {
        switch (status) {
            case 'TO_STUDY': return 'bg-light text-dark';
            case 'IN_PROGRESS': return 'bg-warning';
            case 'DONE': return 'bg-success';
            default: return 'bg-secondary';
        }
    }

    function getItemStatusLabel(status) {
        switch (status) {
            case 'TO_STUDY': return 'A Estudar';
            case 'IN_PROGRESS': return 'Em Progresso';
            case 'DONE': return 'Concluído';
            default: return 'Indefinido';
        }
    }

    function renderTags(tags) {
        return tags.split(',').map(tag => 
            `<span class="badge bg-light text-dark me-1">${tag.trim()}</span>`
        ).join('');
    }

    function calculateProgress() {
        if (!currentTopic.study_items_count || currentTopic.study_items_count === 0) return 0;
        return currentTopic.status === 'COMPLETED' ? 100 : 
               currentTopic.status === 'STUDYING' ? 50 : 0;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR');
    }

    function truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    function showStatusModal() {
        newStatusSelect.value = currentTopic.status;
        statusModal.show();
    }

    function showDeleteModal() {
        deleteModal.show();
    }

    async function updateStatus() {
        try {
            const newStatus = newStatusSelect.value;
            
            const response = await fetch(`/api/topics/${topicId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: newStatus })
            });

            if (response.ok) {
                currentTopic.status = newStatus;
                statusModal.hide();
                renderTopic();
                showSuccess('Status atualizado com sucesso!');
            } else {
                throw new Error('Erro ao atualizar status');
            }
        } catch (error) {
            console.error('Erro ao atualizar status:', error);
            showError('Erro ao atualizar status');
        }
    }

    async function deleteTopic() {
        try {
            const response = await fetch(`/api/topics/${topicId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                deleteModal.hide();
                showSuccess('Tópico excluído com sucesso!');
                setTimeout(() => {
                    window.location.href = '/topics';
                }, 1500);
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
        topicContent.classList.add('d-none');
        errorState.classList.add('d-none');
    }

    function hideLoading() {
        loadingSpinner.classList.add('d-none');
    }

    function showContent() {
        topicContent.classList.remove('d-none');
        errorState.classList.add('d-none');
    }

    function showError(message) {
        hideLoading();
        errorMessage.textContent = message;
        errorState.classList.remove('d-none');
        topicContent.classList.add('d-none');
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

    // Tornar funções globais para uso nos elementos HTML
    window.showStatusModal = showStatusModal;
    window.showDeleteModal = showDeleteModal;
});
</script>
@endpush