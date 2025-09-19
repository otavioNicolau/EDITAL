@extends('layouts.app')

@section('title', 'Métricas - Estudo Concurso')

@section('content')
<div class="px-4 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Métricas de Estudo</h1>
        <p class="mt-2 text-gray-600">Análise detalhada do seu desempenho e progresso</p>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Reviews -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-redo text-2xl text-blue-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Revisões</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-reviews">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-2xl text-green-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Taxa de Sucesso</dt>
                            <dd class="text-lg font-medium text-gray-900" id="success-rate">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Grade -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-star text-2xl text-yellow-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Nota Média</dt>
                            <dd class="text-lg font-medium text-gray-900" id="average-grade">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Study Sessions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-2xl text-purple-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Sessões de Estudo</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-sessions">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Grade Distribution -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Distribuição de Notas</h3>
                <div class="relative">
                    <canvas id="gradeChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Progress by Topic -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Progresso por Tópico</h3>
                <div class="relative">
                    <canvas id="progressChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Analysis -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Análise Temporal</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600" id="total-hours">-</div>
                    <div class="text-sm text-gray-500">Horas Totais</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600" id="avg-session">-</div>
                    <div class="text-sm text-gray-500">Média por Sessão</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600" id="study-streak">-</div>
                    <div class="text-sm text-gray-500">Sequência (dias)</div>
                </div>
            </div>
            <div class="mt-6">
                <canvas id="timeChart" width="800" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Difficulty Analysis -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Análise de Dificuldade</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-medium text-gray-700 mb-3">Por Tipo de Item</h4>
                    <div id="difficulty-by-kind" class="space-y-2">
                        <!-- Será preenchido via JavaScript -->
                    </div>
                </div>
                <div>
                    <h4 class="text-md font-medium text-gray-700 mb-3">Por Tópico</h4>
                    <div id="difficulty-by-topic" class="space-y-2">
                        <!-- Será preenchido via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-content-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
        <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
        <p class="text-gray-700">Carregando métricas...</p>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadMetrics();
});

async function loadMetrics() {
    try {
        const response = await fetch('/metrics', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Erro ao carregar métricas');
        }
        
        const data = await response.json();
        updateUI(data);
        hideLoading();
    } catch (error) {
        console.error('Erro:', error);
        hideLoading();
        showError('Erro ao carregar métricas. Tente novamente.');
    }
}

function updateUI(data) {
    // Performance metrics
    document.getElementById('total-reviews').textContent = data.performance.total_reviews || 0;
    document.getElementById('success-rate').textContent = (data.performance.success_rate || 0) + '%';
    document.getElementById('average-grade').textContent = data.performance.average_grade || '0.0';
    
    // Progress metrics
    document.getElementById('total-sessions').textContent = data.progress.total_sessions || 0;
    
    // Time analysis
    document.getElementById('total-hours').textContent = Math.round(data.time_analysis.total_hours || 0) + 'h';
    document.getElementById('avg-session').textContent = Math.round(data.time_analysis.average_session_duration || 0) + 'min';
    document.getElementById('study-streak').textContent = data.time_analysis.study_streak || 0;
    
    // Charts
    createGradeChart(data.performance.grade_distribution);
    createProgressChart(data.progress.topics_by_status);
    createTimeChart(data.time_analysis.sessions_by_day);
    
    // Difficulty analysis
    updateDifficultyAnalysis(data.difficulty_analysis);
}

function createGradeChart(gradeData) {
    const ctx = document.getElementById('gradeChart').getContext('2d');
    const grades = Object.keys(gradeData || {}).map(Number).sort();
    const counts = grades.map(grade => gradeData[grade] || 0);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: grades.map(g => `Nota ${g}`),
            datasets: [{
                label: 'Quantidade',
                data: counts,
                backgroundColor: [
                    '#ef4444', '#f97316', '#eab308', 
                    '#22c55e', '#3b82f6', '#8b5cf6'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
}

function createProgressChart(progressData) {
    const ctx = document.getElementById('progressChart').getContext('2d');
    const statuses = Object.keys(progressData || {});
    const counts = statuses.map(status => progressData[status] || 0);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statuses,
            datasets: [{
                data: counts,
                backgroundColor: ['#22c55e', '#eab308', '#ef4444', '#6b7280']
            }]
        },
        options: {
            responsive: true
        }
    });
}

function createTimeChart(sessionsData) {
    const ctx = document.getElementById('timeChart').getContext('2d');
    const days = Object.keys(sessionsData || {}).sort();
    const hours = days.map(day => (sessionsData[day]?.total_minutes || 0) / 60);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: days.map(day => new Date(day).toLocaleDateString('pt-BR')),
            datasets: [{
                label: 'Horas Estudadas',
                data: hours,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Horas'
                    }
                }
            }
        }
    });
}

function updateDifficultyAnalysis(difficultyData) {
    // Por tipo de item
    const kindContainer = document.getElementById('difficulty-by-kind');
    kindContainer.innerHTML = '';
    
    if (difficultyData.by_kind) {
        Object.entries(difficultyData.by_kind).forEach(([kind, avg]) => {
            const div = document.createElement('div');
            div.className = 'flex justify-between items-center p-2 bg-gray-50 rounded';
            div.innerHTML = `
                <span class="font-medium">${kind}</span>
                <span class="text-sm text-gray-600">Média: ${avg.toFixed(1)}</span>
            `;
            kindContainer.appendChild(div);
        });
    }
    
    // Por tópico
    const topicContainer = document.getElementById('difficulty-by-topic');
    topicContainer.innerHTML = '';
    
    if (difficultyData.by_topic) {
        Object.entries(difficultyData.by_topic).slice(0, 5).forEach(([topic, avg]) => {
            const div = document.createElement('div');
            div.className = 'flex justify-between items-center p-2 bg-gray-50 rounded';
            div.innerHTML = `
                <span class="font-medium text-sm">${topic}</span>
                <span class="text-sm text-gray-600">Média: ${avg.toFixed(1)}</span>
            `;
            topicContainer.appendChild(div);
        });
    }
}

function hideLoading() {
    document.getElementById('loading-overlay').style.display = 'none';
}

function showError(message) {
    const overlay = document.getElementById('loading-overlay');
    overlay.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <i class="fas fa-exclamation-triangle text-3xl text-red-500 mb-4"></i>
            <p class="text-gray-700">${message}</p>
            <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Tentar Novamente
            </button>
        </div>
    `;
}
</script>
@endpush