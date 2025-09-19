@extends('layouts.app')

@section('title', 'Sessão de Revisão')

@section('content')
<div class="container-fluid">
    <!-- Loading State -->
    <div id="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">Preparando sua sessão de revisão...</p>
    </div>

    <!-- No Items State -->
    <div id="noItemsState" class="text-center py-5" style="display: none;">
        <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
        <h3 class="text-success">Parabéns!</h3>
        <p class="text-muted mb-4">Você não tem itens para revisar no momento.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="/dashboard" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Voltar ao Dashboard
            </a>
            <a href="/study-items" class="btn btn-outline-primary">
                <i class="fas fa-book me-2"></i>Ver Itens de Estudo
            </a>
        </div>
    </div>

    <!-- Review Session -->
    <div id="reviewSession" style="display: none;">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button class="btn btn-outline-secondary" onclick="exitSession()">
                <i class="fas fa-arrow-left me-2"></i>Sair da Revisão
            </button>
            
            <div class="text-center">
                <h2 class="h4 mb-0">Revisão SRS</h2>
                <p class="text-muted mb-0">
                    <span id="currentItemNumber">1</span> de <span id="totalItems">0</span> itens
                </p>
            </div>
            
            <div class="text-end">
                <p class="text-muted mb-0">Concluídas</p>
                <h3 class="text-success mb-0" id="completedCount">0</h3>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress mb-4" style="height: 8px;">
            <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width: 0%"></div>
        </div>

        <!-- Current Item Card -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0" id="itemTitle">Carregando...</h5>
                                <small id="itemTopic">Tópico</small>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-light text-dark" id="itemType">Tipo</div>
                                <div class="small mt-1">
                                    Facilidade: <span id="itemEase">2.5</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Question/Content -->
                        <div id="itemContent" class="mb-4">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando conteúdo...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Show Answer Button -->
                        <div id="showAnswerSection" class="text-center mb-4">
                            <button class="btn btn-primary btn-lg" onclick="showAnswer()">
                                <i class="fas fa-eye me-2"></i>Mostrar Resposta
                            </button>
                            <p class="text-muted small mt-2">
                                Tente lembrar da resposta antes de clicar
                            </p>
                        </div>

                        <!-- Answer Section -->
                        <div id="answerSection" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-lightbulb me-2"></i>Resposta/Conteúdo:</h6>
                                <div id="itemAnswer">Carregando resposta...</div>
                            </div>

                            <!-- Grade Buttons -->
                            <div class="mb-4">
                                <h6 class="mb-3">Como foi sua lembrança?</h6>
                                <div class="row g-2">
                                    <div class="col-6 col-md-2">
                                        <button class="btn btn-outline-danger w-100 grade-btn" data-grade="0" onclick="selectGrade(0)">
                                            <div class="fw-bold">0</div>
                                            <small>Não lembrei</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <button class="btn btn-outline-warning w-100 grade-btn" data-grade="1" onclick="selectGrade(1)">
                                            <div class="fw-bold">1</div>
                                            <small>Muito difícil</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <button class="btn btn-outline-warning w-100 grade-btn" data-grade="2" onclick="selectGrade(2)">
                                            <div class="fw-bold">2</div>
                                            <small>Hesitei</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <button class="btn btn-outline-primary w-100 grade-btn" data-grade="3" onclick="selectGrade(3)">
                                            <div class="fw-bold">3</div>
                                            <small>Fácil</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <button class="btn btn-outline-success w-100 grade-btn" data-grade="4" onclick="selectGrade(4)">
                                            <div class="fw-bold">4</div>
                                            <small>Muito fácil</small>
                                        </button>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <button class="btn btn-outline-success w-100 grade-btn" data-grade="5" onclick="selectGrade(5)">
                                            <div class="fw-bold">5</div>
                                            <small>Perfeito</small>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button class="btn btn-success btn-lg" id="submitBtn" onclick="submitReview()" disabled>
                                    <i class="fas fa-check me-2"></i>
                                    <span id="submitBtnText">Próximo Item</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock text-primary fa-2x mb-2"></i>
                        <h6>Restantes</h6>
                        <h4 id="remainingCount">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                        <h6>Sucessos</h6>
                        <h4 id="successCount">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-times-circle text-danger fa-2x mb-2"></i>
                        <h6>Falhas</h6>
                        <h4 id="failureCount">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-star text-warning fa-2x mb-2"></i>
                        <h6>Média</h6>
                        <h4 id="averageGrade">0.0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Complete -->
    <div id="sessionComplete" class="text-center py-5" style="display: none;">
        <i class="fas fa-trophy fa-4x text-warning mb-4"></i>
        <h2 class="text-success mb-3">Sessão Concluída!</h2>
        <p class="text-muted mb-4">Parabéns! Você completou todas as revisões pendentes.</p>
        
        <!-- Final Stats -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Resumo da Sessão</h5>
                        <div class="row text-center">
                            <div class="col-3">
                                <h4 id="finalTotal">0</h4>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-3">
                                <h4 id="finalSuccess" class="text-success">0</h4>
                                <small class="text-muted">Sucessos</small>
                            </div>
                            <div class="col-3">
                                <h4 id="finalFailure" class="text-danger">0</h4>
                                <small class="text-muted">Falhas</small>
                            </div>
                            <div class="col-3">
                                <h4 id="finalAverage" class="text-warning">0.0</h4>
                                <small class="text-muted">Média</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-center gap-3">
            <a href="/dashboard" class="btn btn-primary btn-lg">
                <i class="fas fa-home me-2"></i>Voltar ao Dashboard
            </a>
            <a href="/reviews" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-history me-2"></i>Ver Histórico
            </a>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="alert alert-danger" style="display: none;">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Erro na Sessão</h5>
        <p class="mb-2">Ocorreu um erro durante a sessão de revisão.</p>
        <button class="btn btn-outline-danger" onclick="loadDueItems()">
            <i class="fas fa-retry me-2"></i>Tentar Novamente
        </button>
    </div>
</div>

<!-- Exit Confirmation Modal -->
<div class="modal fade" id="exitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sair da Revisão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja sair da sessão de revisão?</p>
                <p class="text-muted small">Seu progresso será perdido.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continuar Revisão</button>
                <button type="button" class="btn btn-danger" onclick="confirmExit()">Sair</button>
            </div>
        </div>
    </div>
</div>

<script>
let dueItems = [];
let currentItemIndex = 0;
let selectedGrade = null;
let sessionStats = {
    completed: 0,
    success: 0,
    failure: 0,
    totalGrade: 0
};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadDueItems();
});

// Load due items from API
async function loadDueItems() {
    try {
        showLoading();
        
        const response = await fetch('/api/study-items/due/items');
        if (!response.ok) throw new Error('Erro ao carregar itens');
        
        dueItems = await response.json();
        
        if (dueItems.length === 0) {
            showNoItems();
        } else {
            startSession();
        }
        
    } catch (error) {
        console.error('Erro ao carregar itens:', error);
        showError();
    }
}

// Start review session
function startSession() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('reviewSession').style.display = 'block';
    
    document.getElementById('totalItems').textContent = dueItems.length;
    updateStats();
    loadCurrentItem();
}

// Load current item
function loadCurrentItem() {
    if (currentItemIndex >= dueItems.length) {
        completeSession();
        return;
    }
    
    const item = dueItems[currentItemIndex];
    
    // Update header
    document.getElementById('currentItemNumber').textContent = currentItemIndex + 1;
    document.getElementById('itemTitle').textContent = item.title;
    document.getElementById('itemTopic').textContent = item.topic?.name || 'Sem tópico';
    document.getElementById('itemType').textContent = getTypeLabel(item.kind);
    document.getElementById('itemEase').textContent = item.ease?.toFixed(1) || '2.5';
    
    // Update progress
    const progress = ((currentItemIndex) / dueItems.length) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    
    // Load content
    loadItemContent(item);
    
    // Reset state
    document.getElementById('showAnswerSection').style.display = 'block';
    document.getElementById('answerSection').style.display = 'none';
    selectedGrade = null;
    updateSubmitButton();
    clearGradeSelection();
}

// Load item content
function loadItemContent(item) {
    const contentDiv = document.getElementById('itemContent');
    const answerDiv = document.getElementById('itemAnswer');
    
    let contentHTML = '';
    let answerHTML = '';
    
    switch (item.kind) {
        case 'SUMMARY':
            contentHTML = `
                <div class="alert alert-primary">
                    <h6><i class="fas fa-book me-2"></i>Resumo para Revisar</h6>
                    <p class="mb-0">Tente lembrar dos pontos principais deste tópico.</p>
                </div>
            `;
            answerHTML = `<div class="content-text">${item.content || 'Conteúdo não disponível'}</div>`;
            break;
            
        case 'QUESTION':
            contentHTML = `
                <div class="alert alert-warning">
                    <h6><i class="fas fa-question-circle me-2"></i>Questão</h6>
                    <div class="content-text">${item.content || 'Questão não disponível'}</div>
                </div>
            `;
            answerHTML = `<div class="content-text">${item.metadata || 'Resposta não disponível'}</div>`;
            break;
            
        case 'LAW':
            contentHTML = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-gavel me-2"></i>Lei/Artigo</h6>
                    <p class="mb-0">Tente lembrar do conteúdo desta lei ou artigo.</p>
                </div>
            `;
            answerHTML = `<div class="content-text">${item.content || 'Conteúdo não disponível'}</div>`;
            break;
            
        case 'VIDEO':
            contentHTML = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-video me-2"></i>Vídeo</h6>
                    <p class="mb-0">Tente lembrar dos pontos principais deste vídeo.</p>
                    ${item.url ? `<a href="${item.url}" target="_blank" class="btn btn-sm btn-outline-success mt-2">
                        <i class="fas fa-external-link-alt me-1"></i>Abrir Vídeo
                    </a>` : ''}
                </div>
            `;
            answerHTML = `<div class="content-text">${item.content || 'Conteúdo não disponível'}</div>`;
            break;
            
        default:
            contentHTML = `
                <div class="alert alert-secondary">
                    <h6><i class="fas fa-file me-2"></i>Item de Estudo</h6>
                    <p class="mb-0">Tente lembrar do conteúdo deste item.</p>
                </div>
            `;
            answerHTML = `<div class="content-text">${item.content || 'Conteúdo não disponível'}</div>`;
    }
    
    contentDiv.innerHTML = contentHTML;
    answerDiv.innerHTML = answerHTML;
}

// Show answer
function showAnswer() {
    document.getElementById('showAnswerSection').style.display = 'none';
    document.getElementById('answerSection').style.display = 'block';
}

// Select grade
function selectGrade(grade) {
    selectedGrade = grade;
    
    // Update button states
    clearGradeSelection();
    const selectedBtn = document.querySelector(`[data-grade="${grade}"]`);
    selectedBtn.classList.remove('btn-outline-danger', 'btn-outline-warning', 'btn-outline-primary', 'btn-outline-success');
    
    if (grade >= 4) {
        selectedBtn.classList.add('btn-success');
    } else if (grade === 3) {
        selectedBtn.classList.add('btn-primary');
    } else if (grade === 2) {
        selectedBtn.classList.add('btn-warning');
    } else {
        selectedBtn.classList.add('btn-danger');
    }
    
    updateSubmitButton();
}

// Clear grade selection
function clearGradeSelection() {
    document.querySelectorAll('.grade-btn').forEach(btn => {
        btn.classList.remove('btn-success', 'btn-primary', 'btn-warning', 'btn-danger');
        const grade = parseInt(btn.dataset.grade);
        
        if (grade >= 4) {
            btn.classList.add('btn-outline-success');
        } else if (grade === 3) {
            btn.classList.add('btn-outline-primary');
        } else if (grade === 2) {
            btn.classList.add('btn-outline-warning');
        } else {
            btn.classList.add('btn-outline-danger');
        }
    });
}

// Update submit button
function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    
    submitBtn.disabled = selectedGrade === null;
    
    if (currentItemIndex === dueItems.length - 1) {
        submitBtnText.textContent = 'Finalizar Revisões';
    } else {
        submitBtnText.textContent = 'Próximo Item';
    }
}

// Submit review
async function submitReview() {
    if (selectedGrade === null) return;
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
        
        const currentItem = dueItems[currentItemIndex];
        
        const response = await fetch('/api/reviews', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                study_item_id: currentItem.id,
                grade: selectedGrade
            })
        });
        
        if (!response.ok) throw new Error('Erro ao submeter revisão');
        
        // Update stats
        sessionStats.completed++;
        sessionStats.totalGrade += selectedGrade;
        
        if (selectedGrade >= 3) {
            sessionStats.success++;
        } else {
            sessionStats.failure++;
        }
        
        updateStats();
        
        // Move to next item
        currentItemIndex++;
        
        setTimeout(() => {
            loadCurrentItem();
        }, 500);
        
    } catch (error) {
        console.error('Erro ao submeter revisão:', error);
        alert('Erro ao submeter revisão. Tente novamente.');
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Update stats
function updateStats() {
    document.getElementById('completedCount').textContent = sessionStats.completed;
    document.getElementById('remainingCount').textContent = dueItems.length - currentItemIndex;
    document.getElementById('successCount').textContent = sessionStats.success;
    document.getElementById('failureCount').textContent = sessionStats.failure;
    
    const average = sessionStats.completed > 0 
        ? (sessionStats.totalGrade / sessionStats.completed).toFixed(1)
        : '0.0';
    document.getElementById('averageGrade').textContent = average;
}

// Complete session
function completeSession() {
    document.getElementById('reviewSession').style.display = 'none';
    document.getElementById('sessionComplete').style.display = 'block';
    
    // Update final stats
    document.getElementById('finalTotal').textContent = sessionStats.completed;
    document.getElementById('finalSuccess').textContent = sessionStats.success;
    document.getElementById('finalFailure').textContent = sessionStats.failure;
    
    const finalAverage = sessionStats.completed > 0 
        ? (sessionStats.totalGrade / sessionStats.completed).toFixed(1)
        : '0.0';
    document.getElementById('finalAverage').textContent = finalAverage;
}

// Exit session
function exitSession() {
    if (sessionStats.completed > 0) {
        new bootstrap.Modal(document.getElementById('exitModal')).show();
    } else {
        window.location.href = '/dashboard';
    }
}

// Confirm exit
function confirmExit() {
    window.location.href = '/dashboard';
}

// Utility functions
function showLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('noItemsState').style.display = 'none';
    document.getElementById('reviewSession').style.display = 'none';
    document.getElementById('errorState').style.display = 'none';
}

function showNoItems() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('noItemsState').style.display = 'block';
}

function showError() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
}

function getTypeLabel(kind) {
    const labels = {
        'SUMMARY': 'Resumo',
        'QUESTION': 'Questão',
        'LAW': 'Lei',
        'VIDEO': 'Vídeo'
    };
    return labels[kind] || 'Item';
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Only if answer is shown
    if (document.getElementById('answerSection').style.display === 'none') return;
    
    // Number keys 0-5 for grades
    if (e.key >= '0' && e.key <= '5') {
        e.preventDefault();
        selectGrade(parseInt(e.key));
    }
    
    // Enter to submit
    if (e.key === 'Enter' && selectedGrade !== null) {
        e.preventDefault();
        submitReview();
    }
    
    // Space to show answer
    if (e.key === ' ' && document.getElementById('showAnswerSection').style.display === 'block') {
        e.preventDefault();
        showAnswer();
    }
});
</script>

<style>
.content-text {
    white-space: pre-wrap;
    line-height: 1.6;
}

.grade-btn {
    transition: all 0.2s ease;
    min-height: 60px;
}

.grade-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    transition: width 0.5s ease;
}

.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.alert {
    border: none;
    border-radius: 10px;
}

@media (max-width: 768px) {
    .grade-btn {
        min-height: 50px;
        font-size: 0.9rem;
    }
    
    .grade-btn small {
        font-size: 0.7rem;
    }
}
</style>
@endsection