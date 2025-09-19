@extends('layouts.app')

@section('title', 'Novo Item de Estudo')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">📚 Novo Item de Estudo</h5>
                </div>
                <div class="card-body">
                    <form id="studyItemForm">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Título *</label>
                                    <input type="text" class="form-control" id="title" name="title" required
                                        placeholder="Ex: Princípios da Administração Pública">
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
                            <textarea class="form-control" id="notes" name="notes" rows="6" 
                                placeholder="Descreva o conteúdo, adicione suas anotações, resumos ou observações importantes..."></textarea>
                            <div class="form-text">Use este campo para adicionar o conteúdo principal do item de estudo</div>
                        </div>

                        <div class="mb-3" id="urlField" style="display: none;">
                            <label for="url" class="form-label">URL/Link</label>
                            <input type="url" class="form-control" id="url" name="url" 
                                placeholder="https://exemplo.com/video-aula">
                            <div class="form-text">Link para vídeo, artigo ou recurso externo</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="metadata" class="form-label">Metadados (JSON)</label>
                            <textarea class="form-control" id="metadata" name="metadata" rows="3" 
                                placeholder='{"fonte": "Livro X", "pagina": 123, "dificuldade": "alta"}'></textarea>
                            <div class="form-text">Informações adicionais em formato JSON (opcional)</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Campos avançados (inicialmente ocultos) -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="toggleAdvanced">
                                <i class="fas fa-cog me-1"></i>Configurações Avançadas
                            </button>
                        </div>

                        <div id="advancedFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="ease" class="form-label">Facilidade</label>
                                        <input type="number" class="form-control" id="ease" name="ease" 
                                            min="1.3" max="5.0" step="0.1" value="2.5">
                                        <div class="form-text">Fator de facilidade (1.3 - 5.0)</div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="interval" class="form-label">Intervalo (dias)</label>
                                        <input type="number" class="form-control" id="interval" name="interval" 
                                            min="0" value="1">
                                        <div class="form-text">Intervalo para próxima revisão</div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                                <i class="fas fa-save me-2"></i>Salvar Item
                            </button>
                            <a href="{{ route('study-items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
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
                        <p class="mb-0">Preencha os campos para ver o preview</p>
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
                        <p><strong>Metadados:</strong> Use JSON para informações extras como fonte, página, dificuldade, etc.</p>
                        <p class="mb-0"><strong>Configurações Avançadas:</strong> Para controle fino do sistema de repetição espaçada</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let topics = [];

    // Elementos DOM
    const studyItemForm = document.getElementById('studyItemForm');
    const saveBtn = document.getElementById('saveBtn');
    const toggleAdvancedBtn = document.getElementById('toggleAdvanced');
    const advancedFields = document.getElementById('advancedFields');
    const urlField = document.getElementById('urlField');
    const previewCard = document.getElementById('previewCard');
    
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
    toggleAdvancedBtn.addEventListener('click', toggleAdvancedFields);
    kindSelect.addEventListener('change', handleKindChange);
    
    // Preview listeners
    titleInput.addEventListener('input', updatePreview);
    kindSelect.addEventListener('change', updatePreview);
    topicSelect.addEventListener('change', updatePreview);
    statusSelect.addEventListener('change', updatePreview);
    notesTextarea.addEventListener('input', updatePreview);

    // Carregar dados iniciais
    loadTopics();
    
    // Verificar se há topic_id na URL
    const urlParams = new URLSearchParams(window.location.search);
    const preselectedTopicId = urlParams.get('topic_id');

    async function loadTopics() {
        try {
            const response = await fetch('/api/topics');
            topics = await response.json();
            
            populateTopicSelect();
            
            // Preselecionar tópico se especificado na URL
            if (preselectedTopicId) {
                topicSelect.value = preselectedTopicId;
                updatePreview();
            }
        } catch (error) {
            console.error('Erro ao carregar tópicos:', error);
            showError('Erro ao carregar tópicos');
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
        const title = titleInput.value || 'Título do Item';
        const kind = kindSelect.value;
        const topicId = topicSelect.value;
        const status = statusSelect.value || 'NEW';
        const notes = notesTextarea.value;
        
        const selectedTopic = topics.find(t => t.id == topicId);
        
        previewCard.innerHTML = `
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge ${getKindBadgeClass(kind)}">${getKindLabel(kind)}</span>
                <span class="badge ${getStatusBadgeClass(status)}">${getStatusLabel(status)}</span>
            </div>
            <h6 class="card-title">${title}</h6>
            ${selectedTopic ? `
                <p class="card-text small text-muted mb-2">
                    <i class="fas fa-folder me-1"></i>${selectedTopic.block?.name || 'Sem bloco'}${selectedTopic.discipline?.name ? ' • ' + selectedTopic.discipline.name : ''} • ${selectedTopic.name}
                </p>
            ` : ''}
            ${notes ? `<p class="card-text small">${truncateText(notes, 100)}</p>` : ''}
            <div class="card-footer bg-transparent p-0 mt-2">
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>Criado agora
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
            const response = await fetch('/api/study-items', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                showSuccess('Item de estudo criado com sucesso!');
                setTimeout(() => {
                    window.location.href = `/study-items/${result.id}`;
                }, 1500);
            } else {
                if (result.details) {
                    showValidationErrors(result.details);
                } else {
                    throw new Error(result.error || 'Erro ao criar item de estudo');
                }
            }
        } catch (error) {
            console.error('Erro ao salvar:', error);
            showError(error.message || 'Erro ao salvar item de estudo');
        } finally {
            // Reabilitar botão
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Item';
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
