@extends('layouts.app')

@section('title', 'Editar Item de Estudo')

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
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">✏️ Editar Item de Estudo</h5>
                    </div>
                    <div class="card-body">
                        <form id="studyItemForm">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Título *</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="kind" class="form-label">Tipo *</label>
                                        <select class="form-select" id="kind" name="kind" required>
                                            <option value="">Selecione o tipo...</option>
                                            <option value="QUESTION">❓ Questão</option>
                                            <option value="CONCEPT">💡 Conceito</option>
                                            <option value="EXERCISE">✏️ Exercício</option>
                                            <option value="VIDEO">🎥 Vídeo</option>
                                            <option value="ARTICLE">📄 Artigo</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="topic_id" class="form-label">Tópico *</label>
                                        <select class="form-select" id="topic_id" name="topic_id" required>
                                            <option value="">Selecione um tópico...</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="NEW">🆕 Novo</option>
                                            <option value="LEARNING">📖 Aprendendo</option>
                                            <option value="REVIEW">🔄 Revisão</option>
                                            <option value="MASTERED">✅ Dominado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Conteúdo/Notas</label>
                                <textarea class="form-control" id="notes" name="notes" rows="6"></textarea>
                                <div class="form-text">Use este campo para adicionar o conteúdo principal do item de estudo</div>
                            </div>

                            <div class="mb-3" id="urlField" style="display: none;">
                                <label for="url" class="form-label">URL/Link</label>
                                <input type="url" class="form-control" id="url" name="url">
                                <div class="form-text">Link para vídeo, artigo ou recurso externo</div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="metadata" class="form-label">Metadados (JSON)</label>
                                <textarea class="form-control" id="metadata" name="metadata" rows="3"></textarea>
                                <div class="form-text">Informações adicionais em formato JSON (opcional)</div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Campos avançados -->
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="toggleAdvanced">
                                    <i class="fas fa-cog me-1"></i>Configurações Avançadas
                                </button>
                            </div>

                            <div id="advancedFields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="ease" class="form-label">Facilidade</label>
                                            <input type="number" class="form-control" id="ease" name="ease" 
                                                min="1.3" max="5.0" step="0.1">
                                            <div class="form-text">Fator de facilidade (1.3 - 5.0)</div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="interval" class="form-label">Intervalo (dias)</label>
                                            <input type="number" class="form-control" id="interval" name="interval" min="0">
                                            <div class="form-text">Intervalo para próxima revisão</div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="review_count" class="form-label">Revisões</label>
                                            <input type="number" class="form-control" id="review_count" name="review_count" min="0">
                                            <div class="form-text">Número de revisões</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="due_at" class="form-label">Data de Vencimento</label>
                                            <input type="datetime-local" class="form-control" id="due_at" name="due_at">
                                            <div class="form-text">Quando deve ser revisado</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="saveBtn">
                                    <i class="fas fa-save me-2"></i>Salvar Alterações
                                </button>
                                <button type="button" class="btn btn-secondary" id="cancelBtn">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="deleteBtn">
                                    <i class="fas fa-trash me-2"></i>Excluir
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Preview do Item -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">👁️ Preview</h6>
                    </div>
                    <div class="card-body" id="previewCard">
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-eye fa-2x mb-2"></i>
                            <p class="mb-0">Carregando preview...</p>
                        </div>
                    </div>
                </div>

                <!-- Histórico de alterações -->
                <div class="card mb-4" id="historyCard" style="display: none;">
                    <div class="card-header">
                        <h6 class="card-title mb-0">📝 Histórico</h6>
                    </div>
                    <div class="card-body">
                        <div class="small text-muted">
                            <p class="mb-1"><strong>Criado:</strong> <span id="createdAt"></span></p>
                            <p class="mb-1"><strong>Última atualização:</strong> <span id="updatedAt"></span></p>
                            <p class="mb-0"><strong>Revisões:</strong> <span id="reviewCount"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Dicas -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">💡 Dicas</h6>
                    </div>
                    <div class="card-body">
                        <div class="small text-muted">
                            <p><strong>Título:</strong> Use títulos descritivos e específicos</p>
                            <p><strong>Tipos:</strong>
                                <br>• Questão: Perguntas e exercícios
                                <br>• Conceito: Definições e teorias
                                <br>• Exercício: Práticas e simulados
                                <br>• Vídeo: Aulas e tutoriais
                                <br>• Artigo: Textos e documentos
                            </p>
                            <p><strong>Status:</strong>
                                <br>• Novo: Ainda não estudado
                                <br>• Aprendendo: Em processo de aprendizado
                                <br>• Revisão: Pronto para revisão
                                <br>• Dominado: Já dominado
                            </p>
                            <p><strong>Metadados:</strong> Use JSON para informações extras</p>
                            <p class="mb-0"><strong>Configurações Avançadas:</strong> Para controle fino do sistema de repetição espaçada</p>
                        </div>
                    </div>
                </div>
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
    let topics = [];
    const itemId = window.location.pathname.split('/').pop();

    // Elementos DOM
    const loading = document.getElementById('loading');
    const content = document.getElementById('content');
    const studyItemForm = document.getElementById('studyItemForm');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const deleteBtn = document.getElementById('deleteBtn');
    const toggleAdvancedBtn = document.getElementById('toggleAdvanced');
    const advancedFields = document.getElementById('advancedFields');
    const urlField = document.getElementById('urlField');
    const previewCard = document.getElementById('previewCard');
    
    // Modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Form elements
    const titleInput = document.getElementById('title');
    const kindSelect = document.getElementById('kind');
    const topicSelect = document.getElementById('topic_id');
    const statusSelect = document.getElementById('status');
    const notesTextarea = document.getElementById('notes');
    const urlInput = document.getElementById('url');
    const metadataTextarea = document.getElementById('metadata');

    // Event listeners
    studyItemForm.addEventListener('submit', handleSubmit);
    cancelBtn.addEventListener('click', () => {
        window.location.href = `/study-items/${itemId}`;
    });
    deleteBtn.addEventListener('click', () => {
        document.getElementById('deleteItemTitle').textContent = studyItem.title;
        deleteModal.show();
    });
    document.getElementById('confirmDeleteBtn').addEventListener('click', deleteItem);
    toggleAdvancedBtn.addEventListener('click', toggleAdvancedFields);
    kindSelect.addEventListener('change', handleKindChange);
    
    // Preview listeners
    titleInput.addEventListener('input', updatePreview);
    kindSelect.addEventListener('change', updatePreview);
    topicSelect.addEventListener('change', updatePreview);
    statusSelect.addEventListener('change', updatePreview);
    notesTextarea.addEventListener('input', updatePreview);

    // Carregar dados iniciais
    loadData();

    async function loadData() {
        try {
            // Carregar tópicos e item em paralelo
            const [topicsResponse, itemResponse] = await Promise.all([
                fetch('/api/topics'),
                fetch(`/api/study-items/${itemId}`)
            ]);
            
            if (!itemResponse.ok) {
                throw new Error('Item não encontrado');
            }
            
            topics = await topicsResponse.json();
            studyItem = await itemResponse.json();
            
            populateTopicSelect();
            populateForm();
            updatePreview();
            updateHistory();
            
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            showError('Erro ao carregar item de estudo');
            setTimeout(() => {
                window.location.href = '/study-items';
            }, 2000);
        } finally {
            loading.style.display = 'none';
            content.style.display = 'block';
        }
    }

    function populateTopicSelect() {
        topicSelect.innerHTML = '<option value="">Selecione um tópico...</option>';
        topics.forEach(topic => {
            const option = document.createElement('option');
            option.value = topic.id;
            const blockLabel = topic.block?.name || 'Sem bloco';
            const disciplineLabel = topic.discipline?.name ? ` • ${topic.discipline.name}` : '';
            option.textContent = `${blockLabel}${disciplineLabel} • ${topic.name}`;
            topicSelect.appendChild(option);
        });
    }

    function populateForm() {
        titleInput.value = studyItem.title || '';
        kindSelect.value = studyItem.kind || '';
        topicSelect.value = studyItem.topic_id || '';
        statusSelect.value = studyItem.status || 'NEW';
        notesTextarea.value = studyItem.notes || '';
        urlInput.value = studyItem.url || '';
        
        // Metadados
        if (studyItem.metadata) {
            metadataTextarea.value = typeof studyItem.metadata === 'string' ? 
                studyItem.metadata : JSON.stringify(studyItem.metadata, null, 2);
        }
        
        // Campos avançados
        document.getElementById('ease').value = studyItem.ease || 2.5;
        document.getElementById('interval').value = studyItem.interval || 1;
        document.getElementById('review_count').value = studyItem.review_count || 0;
        
        if (studyItem.due_at) {
            const dueDate = new Date(studyItem.due_at);
            document.getElementById('due_at').value = dueDate.toISOString().slice(0, 16);
        }
        
        // Mostrar campo URL se necessário
        handleKindChange();
    }

    function updateHistory() {
        document.getElementById('historyCard').style.display = 'block';
        document.getElementById('createdAt').textContent = formatDate(studyItem.created_at);
        document.getElementById('updatedAt').textContent = formatDate(studyItem.updated_at);
        document.getElementById('reviewCount').textContent = studyItem.review_count || 0;
    }

    function handleKindChange() {
        // Mostrar campo URL para vídeos e artigos
        if (kindSelect.value === 'VIDEO' || kindSelect.value === 'ARTICLE') {
            urlField.style.display = 'block';
            urlInput.required = kindSelect.value === 'VIDEO';
        } else {
            urlField.style.display = 'none';
            urlInput.required = false;
        }
        updatePreview();
    }

    function toggleAdvancedFields() {
        const isVisible = advancedFields.style.display !== 'none';
        advancedFields.style.display = isVisible ? 'none' : 'block';
        toggleAdvancedBtn.innerHTML = isVisible ? 
            '<i class="fas fa-cog me-1"></i>Configurações Avançadas' :
            '<i class="fas fa-cog me-1"></i>Ocultar Configurações';
    }

    function updatePreview() {
        const title = titleInput.value || studyItem.title || 'Título do Item';
        const kind = kindSelect.value || studyItem.kind;
        const topicId = topicSelect.value || studyItem.topic_id;
        const status = statusSelect.value || studyItem.status || 'NEW';
        const notes = notesTextarea.value || studyItem.notes;
        
        const selectedTopic = topics.find(t => t.id == topicId) || studyItem.topic;
        
        previewCard.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge ${getKindBadgeClass(kind)}">${getKindLabel(kind)}</span>
                <span class="badge ${getStatusBadgeClass(status)}">${getStatusLabel(status)}</span>
            </div>
            <h6 class="card-title">${title}</h6>
            ${selectedTopic ? `
                <p class="card-text small text-muted mb-2">
                    <i class="fas fa-folder me-1"></i>${selectedTopic.block?.name || selectedTopic.block_name || 'Sem bloco'}${selectedTopic.discipline?.name ? ' • ' + selectedTopic.discipline.name : ''} • ${selectedTopic.name}
                </p>
            ` : ''}
            ${notes ? `<p class="card-text small">${truncateText(notes, 100)}</p>` : ''}
            <div class="card-footer bg-transparent p-0 mt-2">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>Última atualização: ${formatDate(studyItem.updated_at)}
                </small>
            </div>
        `;
    }

    async function handleSubmit(e) {
        e.preventDefault();
        
        if (saveBtn.disabled) return;
        
        try {
            // Desabilitar botão e mostrar loading
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
            
            // Limpar erros anteriores
            clearErrors();
            
            // Preparar dados
            const formData = new FormData(studyItemForm);
            const data = Object.fromEntries(formData.entries());
            
            // Validar JSON de metadados se preenchido
            if (data.metadata) {
                try {
                    JSON.parse(data.metadata);
                } catch (e) {
                    throw new Error('Metadados devem estar em formato JSON válido');
                }
            }
            
            // Fazer requisição
            const response = await fetch(`/api/study-items/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                showSuccess('Item de estudo atualizado com sucesso!');
                setTimeout(() => {
                    window.location.href = `/study-items/${itemId}`;
                }, 1500);
            } else {
                if (result.details) {
                    showValidationErrors(result.details);
                } else {
                    throw new Error(result.error || 'Erro ao atualizar item de estudo');
                }
            }
        } catch (error) {
            console.error('Erro ao salvar:', error);
            showError(error.message || 'Erro ao salvar item de estudo');
        } finally {
            // Reabilitar botão
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Alterações';
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

    function showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors[field][0];
                }
            }
        });
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
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
            default: return '📄 Selecione o tipo';
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
