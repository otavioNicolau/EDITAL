@extends('layouts.app')

@section('title', 'Revisões')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Revisões</h1>
            <p class="text-muted">Histórico e estatísticas das suas revisões</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportReviews()">
                <i class="fas fa-download me-2"></i>Exportar
            </button>
            <button class="btn btn-primary" onclick="startReviewSession()">
                <i class="fas fa-play me-2"></i>Iniciar Revisão
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Carregando revisões...</p>
    </div>

    <!-- Main Content -->
    <div id="content" style="display: none;">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total de Revisões</h6>
                                <h3 class="mb-0" id="totalReviews">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-history fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Sucessos</h6>
                                <h3 class="mb-0" id="successfulReviews">0</h3>
                                <small id="successRate">0%</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Média de Notas</h6>
                                <h3 class="mb-0" id="averageGrade">0.0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-star fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Esta Semana</h6>
                                <h3 class="mb-0" id="weeklyReviews">0</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Item de Estudo</label>
                        <select class="form-select" id="studyItemFilter">
                            <option value="">Todos os itens</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nota</label>
                        <select class="form-select" id="gradeFilter">
                            <option value="">Todas as notas</option>
                            <option value="0">0 - Não lembrei</option>
                            <option value="1">1 - Difícil</option>
                            <option value="2">2 - Hesitei</option>
                            <option value="3">3 - Fácil</option>
                            <option value="4">4 - Muito fácil</option>
                            <option value="5">5 - Perfeito</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Resultado</label>
                        <select class="form-select" id="resultFilter">
                            <option value="">Todos</option>
                            <option value="successful">Sucessos (3-5)</option>
                            <option value="failed">Falhas (0-2)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Inicial</label>
                        <input type="date" class="form-control" id="startDateFilter">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Final</label>
                        <input type="date" class="form-control" id="endDateFilter">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Histórico de Revisões</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshReviews()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Item de Estudo</th>
                                <th>Tópico</th>
                                <th>Nota</th>
                                <th>Facilidade</th>
                                <th>Intervalo</th>
                                <th>Próxima Revisão</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="reviewsTableBody">
                            <!-- Reviews will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5" style="display: none;">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhuma revisão encontrada</h5>
                    <p class="text-muted">Comece fazendo suas primeiras revisões!</p>
                    <button class="btn btn-primary" onclick="startReviewSession()">
                        <i class="fas fa-play me-2"></i>Iniciar Revisão
                    </button>
                </div>

                <!-- Pagination -->
                <nav aria-label="Paginação de revisões" id="pagination" style="display: none;">
                    <ul class="pagination justify-content-center">
                        <!-- Pagination will be generated here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="alert alert-danger" style="display: none;">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Erro ao carregar revisões</h5>
        <p class="mb-2">Ocorreu um erro ao carregar as revisões. Tente novamente.</p>
        <button class="btn btn-outline-danger" onclick="loadReviews()">
            <i class="fas fa-retry me-2"></i>Tentar Novamente
        </button>
    </div>
</div>

<!-- Review Detail Modal -->
<div class="modal fade" id="reviewDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Revisão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reviewDetailContent">
                    <!-- Review details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta revisão?</p>
                <p class="text-muted small">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
let reviews = [];
let filteredReviews = [];
let currentPage = 1;
let itemsPerPage = 20;
let deleteReviewId = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadReviews();
    loadStudyItems();
    setupFilters();
});

// Load reviews from API
async function loadReviews() {
    try {
        showLoading();
        
        const response = await fetch('/reviews', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        if (!response.ok) throw new Error('Erro ao carregar revisões');
        
        reviews = await response.json();
        filteredReviews = [...reviews];
        
        await loadStats();
        renderReviews();
        hideLoading();
        
    } catch (error) {
        console.error('Erro ao carregar revisões:', error);
        showError();
    }
}

// Load statistics
async function loadStats() {
    try {
        const response = await fetch('/reviews/stats', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        if (!response.ok) throw new Error('Erro ao carregar estatísticas');
        
        const stats = await response.json();
        
        document.getElementById('totalReviews').textContent = stats.total_reviews || 0;
        document.getElementById('successfulReviews').textContent = stats.successful_reviews || 0;
        document.getElementById('averageGrade').textContent = stats.average_grade || '0.0';
        
        // Calculate success rate
        const successRate = stats.total_reviews > 0 
            ? Math.round((stats.successful_reviews / stats.total_reviews) * 100)
            : 0;
        document.getElementById('successRate').textContent = `${successRate}%`;
        
        // Calculate weekly reviews
        const oneWeekAgo = new Date();
        oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
        const weeklyCount = reviews.filter(review => 
            new Date(review.created_at) >= oneWeekAgo
        ).length;
        document.getElementById('weeklyReviews').textContent = weeklyCount;
        
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// Load study items for filter
async function loadStudyItems() {
    try {
        const response = await fetch('/api/study-items');
        if (!response.ok) throw new Error('Erro ao carregar itens');
        
        const studyItems = await response.json();
        const select = document.getElementById('studyItemFilter');
        
        studyItems.data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.title} (${item.topic.name})`;
            select.appendChild(option);
        });
        
    } catch (error) {
        console.error('Erro ao carregar itens de estudo:', error);
    }
}

// Setup filter event listeners
function setupFilters() {
    const filters = ['studyItemFilter', 'gradeFilter', 'resultFilter', 'startDateFilter', 'endDateFilter'];
    
    filters.forEach(filterId => {
        document.getElementById(filterId).addEventListener('change', applyFilters);
    });
}

// Apply filters
function applyFilters() {
    const studyItemId = document.getElementById('studyItemFilter').value;
    const grade = document.getElementById('gradeFilter').value;
    const result = document.getElementById('resultFilter').value;
    const startDate = document.getElementById('startDateFilter').value;
    const endDate = document.getElementById('endDateFilter').value;
    
    filteredReviews = reviews.filter(review => {
        // Study item filter
        if (studyItemId && review.study_item_id !== studyItemId) return false;
        
        // Grade filter
        if (grade && review.grade.toString() !== grade) return false;
        
        // Result filter
        if (result === 'successful' && review.grade < 3) return false;
        if (result === 'failed' && review.grade >= 3) return false;
        
        // Date filters
        const reviewDate = new Date(review.created_at);
        if (startDate && reviewDate < new Date(startDate)) return false;
        if (endDate && reviewDate > new Date(endDate + 'T23:59:59')) return false;
        
        return true;
    });
    
    currentPage = 1;
    renderReviews();
}

// Clear all filters
function clearFilters() {
    document.getElementById('studyItemFilter').value = '';
    document.getElementById('gradeFilter').value = '';
    document.getElementById('resultFilter').value = '';
    document.getElementById('startDateFilter').value = '';
    document.getElementById('endDateFilter').value = '';
    
    filteredReviews = [...reviews];
    currentPage = 1;
    renderReviews();
}

// Render reviews table
function renderReviews() {
    const tbody = document.getElementById('reviewsTableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (filteredReviews.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        document.getElementById('pagination').style.display = 'none';
        return;
    }
    
    emptyState.style.display = 'none';
    
    // Pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedReviews = filteredReviews.slice(startIndex, endIndex);
    
    tbody.innerHTML = paginatedReviews.map(review => `
        <tr>
            <td>
                <div class="fw-medium">${formatDate(review.created_at)}</div>
                <small class="text-muted">${formatTime(review.created_at)}</small>
            </td>
            <td>
                <div class="fw-medium">${review.study_item?.title || 'Item removido'}</div>
            </td>
            <td>
                <span class="badge bg-light text-dark">
                    ${review.study_item?.topic?.name || 'Tópico removido'}
                </span>
            </td>
            <td>
                <span class="badge ${getGradeBadgeClass(review.grade)}">
                    ${review.grade} - ${getGradeLabel(review.grade)}
                </span>
            </td>
           <td>
               <span class="text-muted">
                    ${formatEase(review.ease_before)} → ${formatEase(review.ease_after)}
                </span>
            </td>
            <td>
                <span class="text-muted">
                    ${formatInterval(review.interval_before)}d → ${formatInterval(review.interval_after)}d
                </span>
            </td>
            <td>
                <div class="fw-medium">${formatDate(review.due_after)}</div>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="showReviewDetail('${review.id}')" title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteReview('${review.id}')" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    renderPagination();
}

// Render pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredReviews.length / itemsPerPage);
    const pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.style.display = 'none';
        return;
    }
    
    pagination.style.display = 'block';
    const ul = pagination.querySelector('ul');
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Anterior</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Próximo</a>
        </li>
    `;
    
    ul.innerHTML = paginationHTML;
}

// Change page
function changePage(page) {
    if (page < 1 || page > Math.ceil(filteredReviews.length / itemsPerPage)) return;
    
    currentPage = page;
    renderReviews();
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function formatEase(value) {
    if (value === null || value === undefined) {
        return '-';
    }

    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric.toFixed(2) : '-';
}

function formatInterval(value) {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric : 0;
}

// Show review detail modal
async function showReviewDetail(reviewId) {
    try {
        const response = await fetch(`/api/reviews/${reviewId}`);
        if (!response.ok) throw new Error('Erro ao carregar detalhes');
        
        const review = await response.json();
        
        document.getElementById('reviewDetailContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Informações Gerais</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Data/Hora:</strong></td>
                            <td>${formatDateTime(review.created_at)}</td>
                        </tr>
                        <tr>
                            <td><strong>Item:</strong></td>
                            <td>${review.study_item?.title || 'Item removido'}</td>
                        </tr>
                        <tr>
                            <td><strong>Tópico:</strong></td>
                            <td>${review.study_item?.topic?.name || 'Tópico removido'}</td>
                        </tr>
                        <tr>
                            <td><strong>Nota:</strong></td>
                            <td>
                                <span class="badge ${getGradeBadgeClass(review.grade)}">
                                    ${review.grade} - ${getGradeLabel(review.grade)}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Algoritmo SRS</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Facilidade:</strong></td>
                            <td>${formatEase(review.ease_before)} → ${formatEase(review.ease_after)}</td>
                        </tr>
                        <tr>
                            <td><strong>Intervalo:</strong></td>
                            <td>${formatInterval(review.interval_before)} dias → ${formatInterval(review.interval_after)} dias</td>
                        </tr>
                        <tr>
                            <td><strong>Vencimento:</strong></td>
                            <td>${formatDate(review.due_before)} → ${formatDate(review.due_after)}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        
        new bootstrap.Modal(document.getElementById('reviewDetailModal')).show();
        
    } catch (error) {
        console.error('Erro ao carregar detalhes:', error);
        alert('Erro ao carregar detalhes da revisão');
    }
}

// Delete review
function deleteReview(reviewId) {
    deleteReviewId = reviewId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Confirm delete
async function confirmDelete() {
    if (!deleteReviewId) return;
    
    try {
        const response = await fetch(`/api/reviews/${deleteReviewId}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) throw new Error('Erro ao excluir revisão');
        
        // Remove from arrays
        reviews = reviews.filter(r => r.id !== deleteReviewId);
        filteredReviews = filteredReviews.filter(r => r.id !== deleteReviewId);
        
        renderReviews();
        await loadStats();
        
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        deleteReviewId = null;
        
    } catch (error) {
        console.error('Erro ao excluir revisão:', error);
        alert('Erro ao excluir revisão');
    }
}

// Start review session
function startReviewSession() {
    window.location.href = '/study-items/due/review';
}

// Export reviews
function exportReviews() {
    const csvContent = generateCSV(filteredReviews);
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `revisoes_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Generate CSV content
function generateCSV(data) {
    const headers = ['Data', 'Item', 'Tópico', 'Nota', 'Facilidade Antes', 'Facilidade Depois', 'Intervalo Antes', 'Intervalo Depois'];
    const csvRows = [headers.join(',')];
    
    data.forEach(review => {
        const row = [
            formatDateTime(review.created_at),
            `"${review.study_item?.title || 'Item removido'}"`,
            `"${review.study_item?.topic?.name || 'Tópico removido'}"`,
            review.grade,
            review.ease_before?.toFixed(2) || '',
            review.ease_after?.toFixed(2) || '',
            review.interval_before || '',
            review.interval_after || ''
        ];
        csvRows.push(row.join(','));
    });
    
    return csvRows.join('\n');
}

// Refresh reviews
function refreshReviews() {
    loadReviews();
}

// Utility functions
function showLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('content').style.display = 'none';
    document.getElementById('errorState').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = 'block';
}

function showError() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('content').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('pt-BR');
}

function formatTime(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleTimeString('pt-BR', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('pt-BR');
}

function getGradeLabel(grade) {
    const labels = {
        0: 'Não lembrei',
        1: 'Difícil',
        2: 'Hesitei',
        3: 'Fácil',
        4: 'Muito fácil',
        5: 'Perfeito'
    };
    return labels[grade] || 'Desconhecido';
}

function getGradeBadgeClass(grade) {
    if (grade >= 4) return 'bg-success';
    if (grade === 3) return 'bg-primary';
    if (grade === 2) return 'bg-warning';
    return 'bg-danger';
}
</script>
@endsection
