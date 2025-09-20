@extends('layouts.app')

@section('title', 'Editar Tópico')

@section('content')
<div class="container-fluid py-4">
    <!-- Loading -->
    <div class="text-center py-5" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando tópico...</p>
    </div>

    <!-- Formulário de Edição (será preenchido via JavaScript) -->
    <div id="editForm" class="d-none">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">✏️ Editar Tópico</h5>
                    </div>
                    <div class="card-body">
                        <form id="topicEditForm">
                            <input type="hidden" id="block_id" name="block_id">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nome do Tópico *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="PLANNED">📋 Planejado</option>
                                            <option value="STUDYING">📖 Estudando</option>
                                            <option value="COMPLETED">✅ Concluído</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="discipline_id" class="form-label">Disciplina *</label>
                                <select class="form-select" id="discipline_id" name="discipline_id" required>
                                    <option value="">Selecione uma disciplina...</option>
                                </select>
                                <div class="form-text">O bloco correspondente é definido automaticamente.</div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                    placeholder="Descreva o conteúdo e objetivos deste tópico..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                    placeholder="Ex: importante, revisão, difícil (separadas por vírgula)">
                                <div class="form-text">Separe as tags com vírgulas para melhor organização</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="saveBtn">
                                    <i class="fas fa-save me-2"></i>Salvar Alterações
                                </button>
                                <a href="#" class="btn btn-secondary" id="cancelBtn">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Informações do Tópico -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">📊 Informações</h6>
                    </div>
                    <div class="card-body" id="topicInfo">
                        <!-- Será preenchido via JavaScript -->
                    </div>
                </div>

                <!-- Dicas -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">💡 Dicas</h6>
                    </div>
                    <div class="card-body">
                        <div class="small text-muted">
                            <p><strong>Nome:</strong> Use nomes descritivos e específicos</p>
                            <p><strong>Status:</strong> 
                                <br>• Planejado: Tópico criado mas ainda não iniciado
                                <br>• Estudando: Em processo de estudo
                                <br>• Concluído: Estudo finalizado
                            </p>
                            <p><strong>Tags:</strong> Use para categorizar e facilitar buscas</p>
                            <p class="mb-0"><strong>Descrição:</strong> Detalhe o conteúdo e objetivos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const topicId = window.location.pathname.split('/')[2]; // /topics/{id}/edit
    let currentTopic = null;

    // Elementos DOM
    const loadingSpinner = document.getElementById('loadingSpinner');
    const editForm = document.getElementById('editForm');
    const errorState = document.getElementById('errorState');
    const errorMessage = document.getElementById('errorMessage');
    const topicEditForm = document.getElementById('topicEditForm');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    // Form elements
    const nameInput = document.getElementById('name');
    const statusSelect = document.getElementById('status');
    const blockInput = document.getElementById('block_id');
    const disciplineSelect = document.getElementById('discipline_id');
    const descriptionTextarea = document.getElementById('description');
    const tagsInput = document.getElementById('tags');

    // Event listeners
    topicEditForm.addEventListener('submit', handleSubmit);
    cancelBtn.addEventListener('click', handleCancel);
    disciplineSelect.addEventListener('change', handleDisciplineChange);
    let blocks = [];

    // Carregar dados
    loadData();

    async function loadData() {
        try {
            showLoading();
            
            const [topicResponse, blocksResponse] = await Promise.all([
                fetch(`/api/topics/${topicId}`),
                fetch('/api/blocks')
            ]);

            if (!topicResponse.ok) {
                throw new Error('Tópico não encontrado');
            }
            if (!blocksResponse.ok) {
                throw new Error('Não foi possível carregar os blocos e disciplinas.');
            }

            currentTopic = await topicResponse.json();
            const blocksData = await blocksResponse.json();
            blocks = Array.isArray(blocksData) ? blocksData : (blocksData.data || []);

            populateDisciplineOptions();
            populateForm();
            renderTopicInfo();
            showForm();
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            showError(error.message);
        }
    }

    function populateForm() {
        nameInput.value = currentTopic.name || '';
        statusSelect.value = currentTopic.status || 'PLANNED';
        descriptionTextarea.value = currentTopic.description || '';
        tagsInput.value = currentTopic.tags || '';

        if (currentTopic.discipline_id) {
            disciplineSelect.value = String(currentTopic.discipline_id);
            updateBlockInput(disciplineSelect.selectedOptions[0]);
        } else {
            disciplineSelect.value = '';
            blockInput.value = currentTopic.block_id || '';
        }

        cancelBtn.href = `/topics/${topicId}`;
    }

    function populateDisciplineOptions() {
        disciplineSelect.innerHTML = '<option value="">Selecione uma disciplina...</option>';

        blocks.forEach(block => {
            if (!Array.isArray(block.disciplines) || block.disciplines.length === 0) {
                return;
            }

            const group = document.createElement('optgroup');
            group.label = block.name;

            block.disciplines.forEach(discipline => {
                const option = document.createElement('option');
                option.value = discipline.id;
                option.dataset.blockId = block.id;
                option.textContent = discipline.name;
                group.appendChild(option);
            });

            disciplineSelect.appendChild(group);
        });
    }

    function renderTopicInfo() {
        const createdAt = new Date(currentTopic.created_at).toLocaleDateString('pt-BR');
        const updatedAt = new Date(currentTopic.updated_at).toLocaleDateString('pt-BR');
        const blockName = currentTopic.block?.name || '—';
        const disciplineName = currentTopic.discipline?.name || '—';
        
        document.getElementById('topicInfo').innerHTML = `
            <div class="mb-3">
                <small class="text-muted">Bloco</small>
                <div class="fw-bold">${blockName}</div>
            </div>
            <div class="mb-3">
                <small class="text-muted">Disciplina</small>
                <div class="fw-bold">${disciplineName}</div>
            </div>
            <div class="mb-3">
                <small class="text-muted">Criado em</small>
                <div class="fw-bold">${createdAt}</div>
            </div>
            <div class="mb-3">
                <small class="text-muted">Última atualização</small>
                <div class="fw-bold">${updatedAt}</div>
            </div>
            <div class="mb-3">
                <small class="text-muted">Itens de estudo</small>
                <div class="fw-bold">${currentTopic.study_items_count || 0}</div>
            </div>
            <div class="mb-0">
                <small class="text-muted">Revisões</small>
                <div class="fw-bold">${currentTopic.reviews_count || 0}</div>
            </div>
        `;
    }

    function handleDisciplineChange() {
        updateBlockInput(disciplineSelect.selectedOptions[0]);
        clearDisciplineError();
    }

    function updateBlockInput(option) {
        const blockId = option && option.dataset ? option.dataset.blockId : '';
        blockInput.value = blockId || '';
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
            const formData = new FormData(topicEditForm);
            const data = Object.fromEntries(formData.entries());
            if (!disciplineSelect.value) {
                showDisciplineError('Selecione uma disciplina.');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Alterações';
                return;
            }
            updateBlockInput(disciplineSelect.selectedOptions[0]);
            data.block_id = blockInput.value;
            
            // Fazer requisição
            const response = await fetch(`/api/topics/${topicId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                showSuccess('Tópico atualizado com sucesso!');
                setTimeout(() => {
                    window.location.href = `/topics/${topicId}`;
                }, 1500);
            } else {
                if (result.errors) {
                    showValidationErrors(result.errors);
                } else {
                    throw new Error(result.message || 'Erro ao atualizar tópico');
                }
            }
        } catch (error) {
            console.error('Erro ao salvar:', error);
            showError('Erro ao salvar alterações');
        } finally {
            // Reabilitar botão
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Alterações';
        }
    }

    function handleCancel(e) {
        e.preventDefault();
        
        if (hasChanges()) {
            if (confirm('Você tem alterações não salvas. Deseja realmente cancelar?')) {
                window.location.href = `/topics/${topicId}`;
            }
        } else {
            window.location.href = `/topics/${topicId}`;
        }
    }

    function hasChanges() {
        return (
            nameInput.value !== (currentTopic.name || '') ||
            statusSelect.value !== (currentTopic.status || 'PLANNED') ||
            String(blockInput.value || '') !== String(currentTopic.block_id || '') ||
            String(disciplineSelect.value || '') !== String(currentTopic.discipline_id || '') ||
            descriptionTextarea.value !== (currentTopic.description || '') ||
            tagsInput.value !== (currentTopic.tags || '')
        );
    }

    function showValidationErrors(errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            if (field === 'block_id') {
                showError(messages[0]);
                return;
            }

            if (field === 'discipline_id') {
                showDisciplineError(messages[0]);
                return;
            }

            const input = document.getElementById(field);
            if (input) {
                input.classList.add('is-invalid');
                const fieldFeedback = input.closest('.mb-3')?.querySelector('.invalid-feedback');
                if (fieldFeedback) {
                    fieldFeedback.textContent = messages[0];
                }
            }
        });
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
        topicEditForm.querySelectorAll('.invalid-feedback').forEach(element => {
            element.textContent = '';
        });
        clearDisciplineError();
    }

    function showDisciplineError(message) {
        disciplineSelect.classList.add('is-invalid');
        const feedback = disciplineSelect.closest('.mb-3')?.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = message;
        }
    }

    function clearDisciplineError() {
        disciplineSelect.classList.remove('is-invalid');
        const feedback = disciplineSelect.closest('.mb-3')?.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = '';
        }
    }

    function showLoading() {
        loadingSpinner.classList.remove('d-none');
        editForm.classList.add('d-none');
        errorState.classList.add('d-none');
    }

    function showForm() {
        loadingSpinner.classList.add('d-none');
        editForm.classList.remove('d-none');
        errorState.classList.add('d-none');
    }

    function showError(message) {
        loadingSpinner.classList.add('d-none');
        editForm.classList.add('d-none');
        errorMessage.textContent = message;
        errorState.classList.remove('d-none');
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
});
</script>
@endpush
