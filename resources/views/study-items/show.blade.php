@extends('layouts.app')

@section('title', 'Item de Estudo')

@section('content')
<div class="container-fluid py-4">
    <!-- Loading -->
    <div id="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando item de estudo...</p>
    </div>

    <!-- Conteúdo principal -->
    <div id="content" style="display: none;">
        <!-- Header com ações -->
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('study-items.index') }}">Itens de Estudo</a></li>
                                <li class="breadcrumb-item active" id="breadcrumbTitle">Item</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" id="editBtn">
                            <i class="fas fa-edit me-1"></i>Editar
                        </button>
                        <button type="button" class="btn btn-outline-success" id="reviewBtn">
                            <i class="fas fa-check me-1"></i>Marcar como Revisado
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" id="changeStatusBtn">
                                    <i class="fas fa-exchange-alt me-2"></i>Alterar Status
                                </a></li>
                                <li><a class="dropdown-item" href="#" id="duplicateBtn">
                                    <i class="fas fa-copy me-2"></i>Duplicar Item
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" id="deleteBtn">
                                    <i class="fas fa-trash me-2"></i>Excluir
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Conteúdo principal -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span id="kindBadge" class="badge"></span>
                                <span id="statusBadge" class="badge"></span>
                            </div>
                            <small class="text-muted" id="createdAt"></small>
                        </div>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title mb-3" id="itemTitle"></h4>
                        
                        <!-- Informações do tópico -->
                        <div class="mb-3" id="topicInfo">
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-folder me-2"></i>
                                <span id="topicPath"></span>
                            </div>
                        </div>

                        <!-- URL (se existir) -->
                        <div class="mb-3" id="urlSection" style="display: none;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-link me-2 text-primary"></i>
                                <a href="#" id="itemUrl" target="_blank" class="text-decoration-none">
                                    Abrir recurso externo
                                </a>
                            </div>
                        </div>

                        <!-- Conteúdo/Notas -->
                        <div class="mb-4" id="notesSection">
                            <h6>📝 Conteúdo/Notas</h6>
                            <div id="itemNotes" class="border rounded p-3 bg-light"></div>
                        </div>

                        <!-- Metadados -->
                        <div class="mb-3" id="metadataSection" style="display: none;">
                            <h6>🏷️ Metadados</h6>
                            <div id="metadataContent" class="small text-muted"></div>
                        </div>
                    </div>
                </div>

                <!-- Histórico de revisões -->
                <div class="card mt-4" id="reviewHistoryCard" style="display: none;">
                    <div class="card-header">
                        <h6 class="card-title mb-0">📊 Histórico de Revisões</h6>
                    </div>
                    <div class="card-body">
                        <div id="reviewHistory"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Estatísticas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">📈 Estatísticas</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h5 class="mb-1" id="reviewCount">-</h5>
                                    <small class="text-muted">Revisões</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h5 class="mb-1" id="easeValue">-</h5>
                                <small class="text-muted">Facilidade</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="mb-1" id="intervalValue">-</h6>
                                    <small class="text-muted">Intervalo</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="mb-1" id="dueDate">-</h6>
                                <small class="text-muted">Vencimento</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações rápidas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">⚡ Ações Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-sm" id="markMasteredBtn">
                                <i class="fas fa-star me-1"></i>Marcar como Dominado
                            </button>
                            <button class="btn btn-warning btn-sm" id="markLearningBtn">
                                <i class="fas fa-book me-1"></i>Marcar como Aprendendo
                            </button>
                            <button class="btn btn-info btn-sm" id="scheduleReviewBtn">
                                <i class="fas fa-calendar me-1"></i>Agendar Revisão
                            </button>
                            <button class="btn btn-secondary btn-sm" id="resetProgressBtn">
                                <i class="fas fa-undo me-1"></i>Resetar Progresso
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Itens relacionados -->
                <div class="card" id="relatedItemsCard" style="display: none;">
                    <div class="card-header">
                        <h6 class="card-title mb-0">🔗 Itens Relacionados</h6>
                    </div>
                    <div class="card-body">
                        <div id="relatedItems"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de alteração de status -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alterar Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Novo Status</label>
                        <select class="form-select" id="newStatus" name="status" required>
                            <option value="NEW">🆕 Novo</option>
                            <option value="LEARNING">📖 Aprendendo</option>
                            <option value="REVIEW">🔄 Revisão</option>
                            <option value="MASTERED">✅ Dominado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" id="statusNotes" name="notes" rows="3" 
                            placeholder="Adicione observações sobre a mudança de status..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveStatusBtn">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de agendamento de revisão -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agendar Revisão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <div class="mb-3">
                        <label for="scheduleDueAt" class="form-label">Data e Hora</label>
                        <input type="datetime-local" class="form-control" id="scheduleDueAt" name="due_at" required>
                    </div>
                    <div class="mb-3">
                        <label for="scheduleNotes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" id="scheduleNotes" name="notes" rows="3" 
                            placeholder="Adicione observações sobre o agendamento..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveScheduleBtn">Agendar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Atenção!</strong> Esta ação não pode ser desfeita.
                </div>
                <p>Tem certeza que deseja excluir este item de estudo?</p>
                <p class="mb-0"><strong id="deleteItemTitle"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>Excluir
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let studyItem = null;
    const itemId = window.location.pathname.split('/').pop();

    // Elementos DOM
    const loading = document.getElementById('loading');
    const content = document.getElementById('content');
    
    // Modals
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    // Event listeners
    document.getElementById('editBtn').addEventListener('click', () => {
        window.location.href = `/study-items/${itemId}/edit`;
    });
    
    document.getElementById('reviewBtn').addEventListener('click', markAsReviewed);
    document.getElementById('changeStatusBtn').addEventListener('click', () => statusModal.show());
    document.getElementById('duplicateBtn').addEventListener('click', duplicateItem);
    document.getElementById('deleteBtn').addEventListener('click', () => {
        document.getElementById('deleteItemTitle').textContent = studyItem.title;
        deleteModal.show();
    });
    
    // Ações rápidas
    document.getElementById('markMasteredBtn').addEventListener('click', () => updateStatus('MASTERED'));
    document.getElementById('markLearningBtn').addEventListener('click', () => updateStatus('LEARNING'));
    document.getElementById('scheduleReviewBtn').addEventListener('click', () => scheduleModal.show());
    document.getElementById('resetProgressBtn').addEventListener('click', resetProgress);
    
    // Modal actions
    document.getElementById('saveStatusBtn').addEventListener('click', saveStatusChange);
    document.getElementById('saveScheduleBtn').addEventListener('click', saveSchedule);
    document.getElementById('confirmDeleteBtn').addEventListener('click', deleteItem);

    // Carregar dados
    loadStudyItem();

    async function loadStudyItem() {
        try {
            const response = await fetch(`/api/study-items/${itemId}`);
            
            if (!response.ok) {
                throw new Error('Item não encontrado');
            }
            
            studyItem = await response.json();
            renderStudyItem();
            loadRelatedItems();
            
        } catch (error) {
            console.error('Erro ao carregar item:', error);
            showError('Erro ao carregar item de estudo');
            setTimeout(() => {
                window.location.href = '/study-items';
            }, 2000);
        } finally {
            loading.style.display = 'none';
            content.style.display = 'block';
        }
    }

    function renderStudyItem() {
        // Breadcrumb
        document.getElementById('breadcrumbTitle').textContent = studyItem.title;
        
        // Header
        document.getElementById('kindBadge').className = `badge ${getKindBadgeClass(studyItem.kind)}`;
        document.getElementById('kindBadge').textContent = getKindLabel(studyItem.kind);
        
        document.getElementById('statusBadge').className = `badge ${getStatusBadgeClass(studyItem.status)}`;
        document.getElementById('statusBadge').textContent = getStatusLabel(studyItem.status);
        
        document.getElementById('createdAt').textContent = formatDate(studyItem.created_at);
        
        // Conteúdo
        document.getElementById('itemTitle').textContent = studyItem.title;
        
        // Tópico
        if (studyItem.topic) {
            document.getElementById('topicPath').innerHTML = `
                <a href="/topics/${studyItem.topic.id}" class="text-decoration-none">
                    ${studyItem.topic.block.name} • ${studyItem.topic.name}
                </a>
            `;
        }
        
        // URL
        if (studyItem.url) {
            document.getElementById('urlSection').style.display = 'block';
            const urlLink = document.getElementById('itemUrl');
            urlLink.href = studyItem.url;
            urlLink.textContent = studyItem.url;
        }
        
        // Notas
        const notesDiv = document.getElementById('itemNotes');
        if (studyItem.notes) {
            notesDiv.innerHTML = formatNotes(studyItem.notes);
        } else {
            notesDiv.innerHTML = '<em class="text-muted">Nenhuma nota adicionada</em>';
        }
        
        // Metadados
        if (studyItem.metadata) {
            document.getElementById('metadataSection').style.display = 'block';
            document.getElementById('metadataContent').innerHTML = formatMetadata(studyItem.metadata);
        }
        
        // Estatísticas
        document.getElementById('reviewCount').textContent = studyItem.review_count || 0;
        document.getElementById('easeValue').textContent = parseFloat(studyItem.ease || 2.5).toFixed(1);
        document.getElementById('intervalValue').textContent = `${studyItem.interval || 1} dias`;
        document.getElementById('dueDate').textContent = studyItem.due_at ? 
            formatDate(studyItem.due_at) : 'Não agendado';
    }

    async function loadRelatedItems() {
        try {
            const response = await fetch(`/api/study-items?topic_id=${studyItem.topic_id}&limit=5`);
            const items = await response.json();
            
            const relatedItems = items.filter(item => item.id !== studyItem.id);
            
            if (relatedItems.length > 0) {
                document.getElementById('relatedItemsCard').style.display = 'block';
                const container = document.getElementById('relatedItems');
                
                container.innerHTML = relatedItems.map(item => `
                    <div class="mb-2">
                        <a href="/study-items/${item.id}" class="text-decoration-none">
                            <div class="d-flex align-items-center">
                                <span class="badge ${getKindBadgeClass(item.kind)} me-2">${getKindIcon(item.kind)}</span>
                                <span class="small">${truncateText(item.title, 40)}</span>
                            </div>
                        </a>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Erro ao carregar itens relacionados:', error);
        }
    }

    async function markAsReviewed() {
        try {
            const response = await fetch(`/api/study-items/${itemId}/review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quality: 3 }) // Qualidade média
            });

            if (response.ok) {
                showSuccess('Item marcado como revisado!');
                loadStudyItem(); // Recarregar dados
            } else {
                throw new Error('Erro ao marcar como revisado');
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao marcar como revisado');
        }
    }

    async function updateStatus(newStatus) {
        try {
            const response = await fetch(`/api/study-items/${itemId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: newStatus })
            });

            if (response.ok) {
                showSuccess('Status atualizado com sucesso!');
                loadStudyItem(); // Recarregar dados
            } else {
                throw new Error('Erro ao atualizar status');
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao atualizar status');
        }
    }

    async function saveStatusChange() {
        const form = document.getElementById('statusForm');
        const formData = new FormData(form);
        
        try {
            const response = await fetch(`/api/study-items/${itemId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: formData.get('status'),
                    notes: formData.get('notes')
                })
            });

            if (response.ok) {
                statusModal.hide();
                showSuccess('Status atualizado com sucesso!');
                loadStudyItem();
            } else {
                throw new Error('Erro ao atualizar status');
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao atualizar status');
        }
    }

    async function saveSchedule() {
        const form = document.getElementById('scheduleForm');
        const formData = new FormData(form);
        
        try {
            const response = await fetch(`/api/study-items/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    due_at: formData.get('due_at'),
                    notes: formData.get('notes')
                })
            });

            if (response.ok) {
                scheduleModal.hide();
                showSuccess('Revisão agendada com sucesso!');
                loadStudyItem();
            } else {
                throw new Error('Erro ao agendar revisão');
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao agendar revisão');
        }
    }

    async function duplicateItem() {
        try {
            const response = await fetch(`/api/study-items`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    ...studyItem,
                    title: `${studyItem.title} (Cópia)`,
                    status: 'NEW',
                    review_count: 0,
                    ease: 2.5,
                    interval: 1,
                    due_at: null
                })
            });

            if (response.ok) {
                const newItem = await response.json();
                showSuccess('Item duplicado com sucesso!');
                setTimeout(() => {
                    window.location.href = `/study-items/${newItem.id}`;
                }, 1500);
            } else {
                throw new Error('Erro ao duplicar item');
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao duplicar item');
        }
    }

    async function resetProgress() {
        if (!confirm('Tem certeza que deseja resetar o progresso deste item?')) {
            return;
        }

        try {
            const response = await fetch(`/api/study-items/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: 'NEW',
                    review_count: 0,
                    ease: 2.5,
                    interval: 1,
                    due_at: null
                })
            });

            if (response.ok) {
                showSuccess('Progresso resetado com sucesso!');
                loadStudyItem();
            } else {
                throw new Error('Erro ao resetar progresso');
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao resetar progresso');
        }
    }

    async function deleteItem() {
        try {
            const response = await fetch(`/api/study-items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                deleteModal.hide();
                showSuccess('Item excluído com sucesso!');
                setTimeout(() => {
                    window.location.href = '/study-items';
                }, 1500);
            } else {
                throw new Error('Erro ao excluir item');
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao excluir item');
        }
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
            default: return '📄 Item';
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

    function formatDate(dateString) {
        return new Date(dateString).toLocaleString('pt-BR');
    }

    function formatNotes(notes) {
        return notes.replace(/\n/g, '<br>');
    }

    function formatMetadata(metadata) {
        try {
            const data = typeof metadata === 'string' ? JSON.parse(metadata) : metadata;
            return Object.entries(data).map(([key, value]) => 
                `<strong>${key}:</strong> ${value}`
            ).join('<br>');
        } catch (e) {
            return metadata;
        }
    }

    function truncateText(text, maxLength) {
        return text && text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
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
});
</script>
@endpush