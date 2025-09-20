@extends('layouts.app')

@section('title', 'Criar Tópico')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('topics.index') }}">Tópicos</a></li>
                    <li class="breadcrumb-item active">Criar Novo</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">📝 Criar Novo Tópico</h1>
            <p class="mb-0 text-muted">Adicione um novo tópico ao seu plano de estudos</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Tópico</h5>
                </div>
                <div class="card-body">
                    @php($requestedDisciplineId = request()->query('discipline_id'))
                    <form id="createTopicForm">
                        @csrf
                        <input type="hidden" id="block_id" name="block_id">
                        
                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome do Tópico <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   placeholder="Ex: Princípios Constitucionais">
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Descrição opcional do tópico..."></textarea>
                            <div class="form-text">Adicione uma descrição para facilitar a identificação do tópico.</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Disciplina -->
                        <div class="mb-3">
                            <label for="discipline_id" class="form-label">Disciplina <span class="text-danger">*</span></label>
                            <select class="form-select" id="discipline_id" name="discipline_id" required>
                                <option value="">Selecione uma disciplina</option>
                            </select>
                            <div class="form-text">Escolha a disciplina; o bloco correspondente é definido automaticamente.</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status Inicial</label>
                            <select class="form-select" id="status" name="status">
                                <option value="PLANNED">📋 Planejado</option>
                                <option value="STUDYING">📖 Estudando</option>
                                <option value="COMPLETED">✅ Concluído</option>
                            </select>
                            <div class="form-text">Defina o status inicial do tópico.</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Tags -->
                        <div class="mb-4">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags"
                                   placeholder="Ex: constitucional, direitos, fundamentais">
                            <div class="form-text">Adicione tags separadas por vírgula para facilitar a busca.</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('topics.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Criar Tópico
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card de Dicas -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info bg-opacity-10">
                    <h6 class="card-title mb-0 text-info">
                        <i class="fas fa-lightbulb me-2"></i>Dicas para Organizar seus Tópicos
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li><strong>Nome claro:</strong> Use nomes descritivos que facilitem a identificação</li>
                        <li><strong>Descrição útil:</strong> Adicione detalhes sobre o que será estudado</li>
                        <li><strong>Tags organizadas:</strong> Use tags consistentes para agrupar tópicos relacionados</li>
                        <li><strong>Status apropriado:</strong> Comece com "Planejado" e atualize conforme progride</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="position-fixed top-0 start-0 w-100 h-100 d-none" id="loadingOverlay" 
     style="background: rgba(0,0,0,0.5); z-index: 9999;">
    <div class="d-flex align-items-center justify-content-center h-100">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p>Criando tópico...</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createTopicForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const blockInput = document.getElementById('block_id');
    const disciplineSelect = document.getElementById('discipline_id');

    const params = new URLSearchParams(window.location.search);
    const preselectedDisciplineId = params.get('discipline_id');
    const preselectedBlockId = params.get('block_id');
    let blocks = [];

    loadBlocks();

    form.addEventListener('submit', handleSubmit);
    disciplineSelect.addEventListener('change', handleDisciplineChange);

    async function loadBlocks() {
        try {
            const response = await fetch('/api/blocks');
            const data = await response.json();
            blocks = Array.isArray(data) ? data : (data.data || []);

            populateDisciplineOptions();
            applyInitialSelection();
            toggleSubmitState();
        } catch (error) {
            console.error('Erro ao carregar blocos:', error);
            showError('Erro ao carregar blocos.');
            submitBtn.disabled = true;
        }
    }

    function populateDisciplineOptions() {
        disciplineSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';

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

    function applyInitialSelection() {
        if (preselectedDisciplineId) {
            const option = Array.from(disciplineSelect.options).find(opt => opt.value === preselectedDisciplineId);
            if (option) {
                disciplineSelect.value = preselectedDisciplineId;
                updateBlockInput(option);
                return;
            }
        }

        if (preselectedBlockId) {
            const block = blocks.find(item => String(item.id) === String(preselectedBlockId));
            const firstDiscipline = block?.disciplines?.[0];
            if (firstDiscipline) {
                const option = Array.from(disciplineSelect.options).find(opt => opt.value == firstDiscipline.id);
                disciplineSelect.value = String(firstDiscipline.id);
                updateBlockInput(option);
                return;
            }
        }

        if (disciplineSelect.options.length === 2 && disciplineSelect.options[1].value) {
            disciplineSelect.selectedIndex = 1;
            updateBlockInput(disciplineSelect.options[1]);
            return;
        }

        blockInput.value = '';
    }

    function handleDisciplineChange() {
        updateBlockInput(disciplineSelect.selectedOptions[0]);
        toggleSubmitState();
        clearDisciplineError();
    }

    function updateBlockInput(option) {
        const blockId = option && option.dataset ? option.dataset.blockId : '';
        blockInput.value = blockId || '';
    }

    function toggleSubmitState() {
        submitBtn.disabled = !disciplineSelect.value;
    }

    async function handleSubmit(event) {
        event.preventDefault();

        clearErrors();

        if (!disciplineSelect.value) {
            showDisciplineError('Selecione uma disciplina.');
            toggleSubmitState();
            return;
        }

        showLoading();

        try {
            updateBlockInput(disciplineSelect.selectedOptions[0]);

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.block_id = blockInput.value;

            const response = await fetch('/api/topics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                showSuccess('Tópico criado com sucesso!');
                setTimeout(() => {
                    window.location.href = '/topics';
                }, 1500);
            } else {
                if (result.details) {
                    showValidationErrors(result.details);
                } else {
                    showError(result.error || 'Erro ao criar tópico');
                }
            }
        } catch (error) {
            console.error('Erro ao criar tópico:', error);
            showError('Erro de conexão. Tente novamente.');
        } finally {
            hideLoading();
        }
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

            const input = form.querySelector(`[name="${field}"]`);
            if (!input) {
                return;
            }

            input.classList.add('is-invalid');
            const feedback = input.closest('.mb-3')?.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = messages[0];
            }
        });
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

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(element => element.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(element => element.textContent = '');
        clearDisciplineError();
    }

    function showLoading() {
        loadingOverlay.classList.remove('d-none');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando...';
    }

    function hideLoading() {
        loadingOverlay.classList.add('d-none');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Criar Tópico';
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
