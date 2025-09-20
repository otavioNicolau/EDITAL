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
                        <input type="hidden" id="discipline_id" name="discipline_id" value="{{ $requestedDisciplineId }}">
                        
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
                            <select class="form-select" id="discipline_id" name="discipline_id" required disabled>
                                <option value="">Selecione uma disciplina</option>
                                <!-- Será preenchido via JavaScript -->
                            </select>
                            <div class="form-text">Escolha a disciplina dentro do bloco selecionado.</div>
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
    const disciplineInput = document.getElementById('discipline_id');
    const disciplineLabel = document.getElementById('discipline_label');
    const disciplineFeedback = document.getElementById('discipline_feedback');
    const disciplineNotice = document.getElementById('discipline_notice');

    const params = new URLSearchParams(window.location.search);
    const queryDisciplineId = params.get('discipline_id');
    const providedDisciplineId = disciplineInput.value || queryDisciplineId;

    if (providedDisciplineId) {
        loadDiscipline(providedDisciplineId);
    } else {
        showDisciplineNotice();
    }

    form.addEventListener('submit', handleSubmit);

    async function loadDiscipline(id) {
        try {
            const response = await fetch(`/api/disciplines/${id}`);
            if (!response.ok) {
                throw new Error('Disciplina não encontrada');
            }

            const data = await response.json();
            disciplineInput.value = data.id;
            disciplineLabel.textContent = data.name;
            blockInput.value = data.block_id;
            hideDisciplineNotice();
            clearDisciplineFeedback();
        } catch (error) {
            console.error('Erro ao carregar disciplina:', error);
            showError('Não foi possível carregar a disciplina selecionada.');
            showDisciplineNotice();
        }
    }

    function showDisciplineNotice() {
        if (disciplineNotice) {
            disciplineNotice.classList.remove('d-none');
        }
        if (disciplineLabel) {
            disciplineLabel.textContent = '—';
        }
        blockInput.value = '';
        disciplineInput.value = '';
        submitBtn.disabled = true;
    }

    function hideDisciplineNotice() {
        if (disciplineNotice) {
            disciplineNotice.classList.add('d-none');
        }
        submitBtn.disabled = false;
    }

    function clearDisciplineFeedback() {
        if (disciplineFeedback) {
            disciplineFeedback.textContent = '';
            disciplineFeedback.classList.add('d-none');
            disciplineFeedback.classList.remove('text-danger');
        }
    }

    async function handleSubmit(event) {
        event.preventDefault();

        clearErrors();
        showLoading();

        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            if (!data.discipline_id) {
                showError('Selecione uma disciplina antes de criar o tópico.');
                hideLoading();
                showDisciplineNotice();
                return;
            }

            if (!data.block_id) {
                data.block_id = blockInput.value;
            }

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
                if (disciplineFeedback) {
                    disciplineFeedback.textContent = messages[0];
                    disciplineFeedback.classList.remove('d-none');
                    disciplineFeedback.classList.add('text-danger');
                }
                showDisciplineNotice();
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

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(element => element.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(element => element.textContent = '');
        clearDisciplineFeedback();
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
