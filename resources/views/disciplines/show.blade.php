@extends('layouts.app')

@section('title', 'Disciplina: ' . $discipline->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="width: 4px; height: 40px; background-color: {{ $discipline->color ?? '#007bff' }}; border-radius: 2px;"></div>
                    <div>
                        <h1 class="h3 mb-0">{{ $discipline->name }}</h1>
                        @if($discipline->code)
                            <small class="text-muted">{{ $discipline->code }}</small>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('disciplines.edit', $discipline) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <a href="{{ route('disciplines.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                </div>
            </div>

            <!-- Informações da Disciplina -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informações da Disciplina</h5>
                        </div>
                        <div class="card-body">
                            @if($discipline->description)
                                <div class="mb-3">
                                    <strong>Descrição:</strong>
                                    <p class="mt-1">{{ $discipline->description }}</p>
                                </div>
                            @endif
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Status:</strong>
                                    <span class="badge bg-{{ $discipline->status === 'active' ? 'success' : 'secondary' }} ms-2">
                                        {{ $discipline->status === 'active' ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Criada em:</strong>
                                    <span class="ms-2">{{ $discipline->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Estatísticas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-0" id="totalTopics">-</h4>
                                        <small class="text-muted">Tópicos</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-0" id="totalStudyItems">-</h4>
                                    <small class="text-muted">Itens de Estudo</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tópicos da Disciplina -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tópicos da Disciplina</h5>
                    <a href="{{ route('topics.create') }}?discipline_id={{ $discipline->id }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Novo Tópico
                    </a>
                </div>
                <div class="card-body">
                    <div id="loadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                    
                    <div id="topicsContainer" class="d-none">
                        <!-- Tópicos serão carregados aqui via JavaScript -->
                    </div>
                    
                    <div id="emptyState" class="text-center py-5 d-none">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum tópico encontrado</h5>
                        <p class="text-muted">Esta disciplina ainda não possui tópicos cadastrados.</p>
                        <a href="{{ route('topics.create') }}?discipline_id={{ $discipline->id }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Criar Primeiro Tópico
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição Rápida de Status (DISCIPLINE PAGE) -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Alterar Status do Tópico</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Novo Status</label>
          <select class="form-select" id="newStatus">
            <option value="PLANNED">📋 Planejado</option>
            <option value="STUDYING">📖 Estudando</option>
            <option value="REVIEW">🔁 Revisão</option>
            <option value="COMPLETED">✅ Concluído</option>
          </select>
        </div>
        <small class="text-muted d-block">Isso afeta apenas o tópico selecionado.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="updateStatusBtn">Atualizar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var disciplineId = {{ $discipline->id }};
    var loadingSpinner = document.getElementById('loadingSpinner');
    var topicsContainer = document.getElementById('topicsContainer');
    var emptyState = document.getElementById('emptyState');
    var totalTopicsElement = document.getElementById('totalTopics');
    var totalStudyItemsElement = document.getElementById('totalStudyItems');
    var initialTopics = @json($topicsSummary ?? []);
    var initialMetrics = @json($disciplineMetrics ?? []);

    // ===== Estado para alteração de status
    var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
    var newStatusSelect = document.getElementById('newStatus');
    var updateStatusBtn = document.getElementById('updateStatusBtn');
    var selectedTopicId = null;
    var selectedCardEl = null;
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ===== Métricas iniciais
    if (initialMetrics.topics) {
        totalTopicsElement.textContent = initialMetrics.topics.total;
    }
    if (initialMetrics.study_items) {
        totalStudyItemsElement.textContent = initialMetrics.study_items.total;
    }

    // ===== Pré-render com dados do controller
    if (initialTopics.length > 0) {
        renderTopics(initialTopics);
        loadingSpinner.classList.add('d-none');
    }

    // ===== Busca dinâmica
    loadDisciplineData();

    function loadDisciplineData() {
        Promise.all([
            fetch('/api/topics?discipline_id=' + disciplineId).then(function(response) { return response.json(); }),
            fetch('/api/study-items?discipline_id=' + disciplineId).then(function(response) { return response.json(); })
        ]).then(function(results) {
            var topicsData = results[0];
            var studyItemsData = results[1];
            var topics = Array.isArray(topicsData) ? topicsData : (topicsData.data || []);
            var studyItems = Array.isArray(studyItemsData) ? studyItemsData : (studyItemsData.data || []);

            totalTopicsElement.textContent = topics.length;
            totalStudyItemsElement.textContent = studyItems.length;

            renderTopics(topics);
        }).catch(function(error) {
            console.error('Erro ao carregar dados da disciplina:', error);
        }).finally(function() {
            loadingSpinner.classList.add('d-none');
        });
    }

    // ===== Renderiza cartões de tópicos (com "Alterar Status")
    {{-- function renderTopics(topics) {
        if (topics.length === 0) {
            emptyState.classList.remove('d-none');
            return;
        }

        var topicsHtml = topics.map(function(topic) {
            var status = topic.status || 'PLANNED';
            return '' +
            '<div class="card mb-3" data-topic-id="'+ topic.id +'" data-topic-status="'+ status +'">' +
                '<div class="card-body">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                        '<div class="flex-grow-1">' +
                            '<h6 class="card-title mb-2">' +
                                '<a href="/topics/' + topic.id + '" class="text-decoration-none">' + escapeHtml(topic.name) + '</a>' +
                            '</h6>' +
                            (topic.description ? '<p class="card-text text-muted small mb-2">' + escapeHtml(topic.description) + '</p>' : '') +
                            '<div class="d-flex align-items-center gap-3">' +
                                '<span class="badge js-topic-status-badge bg-' + getStatusColor(status) + '">' + getStatusText(status) + '</span>' +
                                (topic.block ? '<small class="text-muted"><i class="fas fa-cube me-1"></i>' + escapeHtml(topic.block.name) + '</small>' : '') +
                                '<small class="text-muted"><i class="fas fa-calendar me-1"></i>' + formatDate(topic.created_at) + '</small>' +
                            '</div>' +
                        '</div>' +
                        '<div class="dropdown">' +
                            '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">' +
                                '<i class="fas fa-ellipsis-v"></i>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                                '<li><a class="dropdown-item" href="/topics/' + topic.id + '"><i class="fas fa-eye me-2"></i>Visualizar</a></li>' +
                                '<li><a class="dropdown-item" href="/topics/' + topic.id + '/edit"><i class="fas fa-edit me-2"></i>Editar</a></li>' +
                                '<li><hr class="dropdown-divider"></li>' +
                                '<li><a class="dropdown-item js-change-status" href="#" data-topic-id="'+ topic.id +'"><i class="fas fa-arrows-rotate me-2"></i>Alterar Status</a></li>' +
                            '</ul>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');

        topicsContainer.innerHTML = topicsHtml;
        topicsContainer.classList.remove('d-none');
    } --}}


function renderTopics(topics) {
    if (topics.length === 0) {
        emptyState.classList.remove('d-none');
        return;
    }

    var topicsHtml = topics.map(function(topic) {
        var status = topic.status || 'PLANNED';
        return '' +
        '<div class="card mb-3" data-topic-id="'+ topic.id +'" data-topic-status="'+ status +'">' +
          '<div class="card-body">' +
            '<div class="d-flex justify-content-between align-items-start">' +

              // ESQUERDA: título/descrição/metas
              '<div class="flex-grow-1 pe-3">' +
                '<h6 class="card-title mb-2">' +
                  '<a href="/topics/' + topic.id + '" class="text-decoration-none">' + escapeHtml(topic.name) + '</a>' +
                '</h6>' +
                (topic.description ? '<p class="card-text text-muted small mb-2">' + escapeHtml(topic.description) + '</p>' : '') +
                '<div class="d-flex align-items-center gap-3">' +
                  '<span class="badge js-topic-status-badge bg-' + getStatusColor(status) + '">' + getStatusText(status) + '</span>' +
                  (topic.block ? '<small class="text-muted"><i class="fas fa-cube me-1"></i>' + escapeHtml(topic.block.name) + '</small>' : '') +
                  '<small class="text-muted"><i class="fas fa-calendar me-1"></i>' + formatDate(topic.created_at) + '</small>' +
                '</div>' +
              '</div>' +

              // DIREITA: **ÚNICO BOTÃO**
              '<div class="text-nowrap">' +
                '<a href="#" class="btn btn-sm btn-outline-primary js-change-status" ' +
                   'data-topic-id="'+ topic.id +'" title="Alterar Status">' +
                   '<i class="fas fa-arrows-rotate me-1"></i>Alterar Status' +
                '</a>' +
                // (Opcional) Botões de ver/editar separados — remova se não quiser
                // '<a href="/topics/' + topic.id + '" class="btn btn-sm btn-outline-secondary ms-1">Ver</a>' +
                // '<a href="/topics/' + topic.id + '/edit" class="btn btn-sm btn-outline-secondary ms-1">Editar</a>' +
              '</div>' +

            '</div>' +
          '</div>' +
        '</div>';
    }).join('');

    topicsContainer.innerHTML = topicsHtml;
    topicsContainer.classList.remove('d-none');
}

    // ======= Delegação de evento para abrir modal de status
    topicsContainer.addEventListener('click', function(ev) {
        var a = ev.target.closest('.js-change-status');
        if (!a) return;

        ev.preventDefault();
        var topicId = a.getAttribute('data-topic-id');
        var card = a.closest('.card');
        var currentStatus = (card?.getAttribute('data-topic-status')) || 'PLANNED';

        selectedTopicId = topicId;
        selectedCardEl = card;

        newStatusSelect.value = currentStatus;
        statusModal.show();
    });

    // ======= Botão atualizar modal
    updateStatusBtn.addEventListener('click', updateStatusForSelectedTopic);

    async function updateStatusForSelectedTopic() {
        if (!selectedTopicId || !selectedCardEl) return;

        var newStatus = newStatusSelect.value;
        var oldStatus = selectedCardEl.getAttribute('data-topic-status');

        // UI otimista
        applyStatusToCard(selectedCardEl, newStatus);

        try {
            var resp = await fetch('/api/topics/' + selectedTopicId + '/status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ status: newStatus })
            });

            if (!resp.ok) {
                // Reverte em caso de erro
                applyStatusToCard(selectedCardEl, oldStatus);
                throw new Error('Falha ao atualizar status');
            }

            statusModal.hide();
            showToast('Status atualizado com sucesso!', 'success');
        } catch (err) {
            console.error(err);
            showToast('Erro ao atualizar status.', 'danger');
        }
    }

    // ===== Helpers visuais
    function applyStatusToCard(cardEl, status) {
        cardEl.setAttribute('data-topic-status', status);
        var badge = cardEl.querySelector('.js-topic-status-badge');
        if (badge) {
            badge.className = 'badge js-topic-status-badge bg-' + getStatusColor(status);
            badge.textContent = getStatusText(status);
        }
    }

    function getStatusColor(status) {
        var colors = {
            'PLANNED': 'secondary',
            'STUDYING': 'warning',
            'REVIEW': 'info',
            'COMPLETED': 'success'
        };
        return colors[status] || 'secondary';
    }

    function getStatusText(status) {
        var texts = {
            'PLANNED': 'Planejado',
            'STUDYING': 'Estudando',
            'REVIEW': 'Revisão',
            'COMPLETED': 'Concluído'
        };
        return texts[status] || status;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR');
    }

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showToast(message, variant) {
        var toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-' + (variant || 'success') + ' border-0 position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = '' +
        '<div class="d-flex">' +
            '<div class="toast-body">' +
                (variant === 'danger' ? '<i class="fas fa-times-circle me-2"></i>' : '<i class="fas fa-check-circle me-2"></i>') +
                escapeHtml(message) +
            '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
        '</div>';

        document.body.appendChild(toast);
        var bsToast = new bootstrap.Toast(toast, { delay: 2500 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', function() {
            document.body.removeChild(toast);
        });
    }
});
</script>
@endsection
