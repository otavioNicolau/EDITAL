@extends('layouts.app')

@section('title', 'Disciplina: ' . $discipline->name)

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
          <div class="me-3" style="width:4px;height:40px;background-color:{{ $discipline->color ?? '#007bff' }};border-radius:2px;"></div>
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

      {{-- Informações da Disciplina --}}
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

        {{-- Estatísticas --}}
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

      {{-- Tópicos --}}
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

          <div id="topicsContainer" class="d-none"></div>

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

{{-- CSS rápido para o chip --}}
<style>
  .btn-group .btn { border-radius: 999px !important; }
  .btn-group .dropdown-menu { min-width: 220px; }
</style>

{{-- Garante CSRF no <head> caso o layout não inclua --}}
@if(!View::hasSection('csrf_meta'))
  @push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
  @endpush
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
  // ===== Variáveis base
  const disciplineId = {{ $discipline->id }};
  const loadingSpinner = document.getElementById('loadingSpinner');
  const topicsContainer = document.getElementById('topicsContainer');
  const emptyState = document.getElementById('emptyState');
  const totalTopicsElement = document.getElementById('totalTopics');
  const totalStudyItemsElement = document.getElementById('totalStudyItems');
  const initialTopics = @json($topicsSummary ?? []);
  const initialMetrics = @json($disciplineMetrics ?? []);
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // ===== Mapa de status (texto/cor/ícone)
  const STATUS_META = {
    PLANNED:   { text: 'Planejado',  color: 'secondary', icon: 'fa-clipboard-list' },
    STUDYING:  { text: 'Estudando',  color: 'warning',   icon: 'fa-book-open' },
    REVIEW:    { text: 'Revisão',    color: 'info',      icon: 'fa-rotate' },
    COMPLETED: { text: 'Concluído',  color: 'success',   icon: 'fa-check-circle' }
  };

  // ===== Métricas iniciais
  if (initialMetrics?.topics)      totalTopicsElement.textContent = initialMetrics.topics.total;
  if (initialMetrics?.study_items) totalStudyItemsElement.textContent = initialMetrics.study_items.total;

  // ===== Pré-render se veio do controller
  if (Array.isArray(initialTopics) && initialTopics.length > 0) {
    renderTopics(initialTopics);
    loadingSpinner.classList.add('d-none');
  }

  // ===== Busca dinâmica
  loadDisciplineData();

  function loadDisciplineData() {
    Promise.all([
      fetch('/api/topics?discipline_id=' + disciplineId).then(r => r.json()),
      fetch('/api/study-items?discipline_id=' + disciplineId).then(r => r.json())
    ])
    .then(([topicsData, itemsData]) => {
      const topics = Array.isArray(topicsData) ? topicsData : (topicsData.data || []);
      const studyItems = Array.isArray(itemsData) ? itemsData : (itemsData.data || []);
      totalTopicsElement.textContent = topics.length;
      totalStudyItemsElement.textContent = studyItems.length;
      renderTopics(topics);
    })
    .catch(err => console.error('Erro ao carregar dados:', err))
    .finally(() => loadingSpinner.classList.add('d-none'));
  }

  // ===== Render dos cards com split dropdown de status
  function renderTopics(topics) {
    if (!topics.length) { emptyState.classList.remove('d-none'); return; }

    const topicsHtml = topics.map(t => {
      const st = t.status || 'PLANNED';
      const meta = STATUS_META[st] || STATUS_META.PLANNED;

      const opts = Object.entries(STATUS_META).map(([key, m]) => `
        <li>
          <a href="#" class="dropdown-item js-status-option" data-topic-id="${t.id}" data-status="${key}">
            <i class="fas ${m.icon} me-2"></i>${m.text}
            ${key === st ? '<i class="fas fa-check float-end text-muted"></i>' : ''}
          </a>
        </li>
      `).join('');

      return `
      <div class="card mb-3" data-topic-id="${t.id}" data-topic-status="${st}">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1 pe-3">
              <h6 class="card-title mb-2">
                <a href="/topics/${t.id}" class="text-decoration-none">${escapeHtml(t.name)}</a>
              </h6>
              ${t.description ? `<p class="card-text text-muted small mb-2">${escapeHtml(t.description)}</p>` : ''}
              <div class="d-flex align-items-center gap-3">
                <span class="badge js-topic-status-badge bg-${meta.color}">${meta.text}</span>
                ${t.block ? `<small class="text-muted"><i class="fas fa-cube me-1"></i>${escapeHtml(t.block.name)}</small>` : ''}
                <small class="text-muted"><i class="fas fa-calendar me-1"></i>${formatDate(t.created_at)}</small>
              </div>
            </div>

            <div class="btn-group">
              <button type="button" class="btn btn-sm btn-${meta.color} text-white js-status-toggle" data-topic-id="${t.id}">
                <i class="fas ${meta.icon} me-1"></i><span class="js-status-text">${meta.text}</span>
              </button>
              <button type="button" class="btn btn-sm btn-${meta.color} text-white dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Alternar status</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                ${opts}
              </ul>
            </div>
          </div>
        </div>
      </div>`;
    }).join('');

    topicsContainer.innerHTML = topicsHtml;
    topicsContainer.classList.remove('d-none');
  }

  // ===== Troca via dropdown (1 clique)
  topicsContainer.addEventListener('click', async (ev) => {
    const opt = ev.target.closest('.js-status-option');
    if (!opt) return;
    ev.preventDefault();

    const topicId = opt.getAttribute('data-topic-id');
    const newStatus = opt.getAttribute('data-status');
    const card = topicsContainer.querySelector(`.card[data-topic-id="${topicId}"]`);
    if (!card) return;

    const oldStatus = card.getAttribute('data-topic-status');
    applyStatusToCard(card, newStatus); // otimista

    try {
      const resp = await fetch(`/api/topics/${topicId}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ status: newStatus })
      });
      if (!resp.ok) throw new Error();
      showToast('Status atualizado com sucesso!', 'success');
    } catch (e) {
      applyStatusToCard(card, oldStatus); // reverte
      showToast('Erro ao atualizar status.', 'danger');
    }
  });

  // ===== Clique no chip principal: cicla os status (opcional)
  topicsContainer.addEventListener('click', async (ev) => {
    const btn = ev.target.closest('.js-status-toggle');
    if (!btn) return;

    const topicId = btn.getAttribute('data-topic-id');
    const card = topicsContainer.querySelector(`.card[data-topic-id="${topicId}"]`);
    if (!card) return;

    const order = ['PLANNED','STUDYING','REVIEW','COMPLETED'];
    const cur = card.getAttribute('data-topic-status') || 'PLANNED';
    const next = order[(order.indexOf(cur) + 1) % order.length];

    const oldStatus = cur;
    applyStatusToCard(card, next); // otimista

    try {
      const resp = await fetch(`/api/topics/${topicId}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ status: next })
      });
      if (!resp.ok) throw new Error();
      showToast('Status atualizado com sucesso!', 'success');
    } catch {
      applyStatusToCard(card, oldStatus);
      showToast('Erro ao atualizar status.', 'danger');
    }
  });

  // ===== Helpers
  function applyStatusToCard(cardEl, status) {
    cardEl.setAttribute('data-topic-status', status);
    const meta = STATUS_META[status] || STATUS_META.PLANNED;

    // badge
    const badge = cardEl.querySelector('.js-topic-status-badge');
    if (badge) {
      badge.className = 'badge js-topic-status-badge bg-' + meta.color;
      badge.textContent = meta.text;
    }

    // botões do split
    const mainBtn = cardEl.querySelector('.js-status-toggle');
    if (mainBtn) {
      mainBtn.className = 'btn btn-sm btn-' + meta.color + ' text-white js-status-toggle';
      mainBtn.innerHTML = `<i class="fas ${meta.icon} me-1"></i><span class="js-status-text">${meta.text}</span>`;
      const split = mainBtn.nextElementSibling;
      if (split && split.matches('.dropdown-toggle')) {
        split.className = 'btn btn-sm btn-' + meta.color + ' text-white dropdown-toggle dropdown-toggle-split';
      }
    }
  }

  function formatDate(s) {
    const d = new Date(s);
    return isNaN(d) ? '' : d.toLocaleDateString('pt-BR');
    }

  function escapeHtml(s) {
    if (s == null) return '';
    return String(s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function showToast(message, variant) {
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-' + (variant || 'success') +
                      ' border-0 position-fixed top-0 end-0 m-3';
    toast.style.zIndex = '9999';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          ${variant === 'danger' ? '<i class="fas fa-times-circle me-2"></i>' : '<i class="fas fa-check-circle me-2"></i>'}
          ${escapeHtml(message)}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>`;

    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 2500 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => document.body.removeChild(toast));
  }
});
</script>
@endsection
