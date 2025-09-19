@extends('layouts.app')

@section('title', 'Dashboard - Estudo Concurso')

@section('content')
<div class="px-4 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-gray-600">Acompanhe seu progresso nos estudos</p>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Blocks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-cube text-2xl text-blue-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Blocos</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $overview['total_blocks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Topics -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-list text-2xl text-green-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Tópicos</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $overview['total_topics'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Study Items -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-book text-2xl text-purple-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Itens de Estudo</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $overview['total_study_items'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Reviews -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-redo text-2xl text-orange-500"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Revisões</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $overview['total_reviews'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress and Due Items -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Progress Chart -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Progresso Geral</h3>
                <div class="relative">
                    <canvas id="progressChart" 
                            width="400" 
                            height="200"
                            data-completed="{{ $progress['completed_percentage'] }}"
                            data-in-progress="{{ $progress['in_progress_percentage'] }}"
                            data-not-started="{{ $progress['not_started_percentage'] }}"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-green-600">{{ $progress['completed_percentage'] }}%</div>
                        <div class="text-sm text-gray-500">Concluído</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-yellow-600">{{ $progress['in_progress_percentage'] }}%</div>
                        <div class="text-sm text-gray-500">Em Progresso</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-600">{{ $progress['not_started_percentage'] }}%</div>
                        <div class="text-sm text-gray-500">Não Iniciado</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Due Items -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Itens Vencidos</h3>
                @if(count($dueItems['items']) > 0)
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($dueItems['items'] as $item)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $item->title }}</h4>
                                    <p class="text-xs text-gray-500">{{ $item->topic->name ?? 'Sem tópico' }}</p>
                                    <p class="text-xs text-red-600">Vencido há {{ \Carbon\Carbon::parse($item->due_at)->diffForHumans() }}</p>
                                </div>
                                <a href="{{ route('study-items.show', $item->id) }}" 
                                   class="ml-3 inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Revisar
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('study-items.review') }}" 
                           class="w-full flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-play mr-2"></i>
                            Iniciar Sessão de Revisão
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                        <p class="text-gray-500">Nenhum item vencido!</p>
                        <p class="text-sm text-gray-400">Você está em dia com seus estudos.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Reviews and Study Streak -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Reviews -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Revisões Recentes</h3>
                @if(count($recentReviews['recent']) > 0)
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($recentReviews['recent'] as $review)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $review->studyItem->title }}</h4>
                                    <p class="text-xs text-gray-500">{{ $review->studyItem->topic->name ?? 'Sem tópico' }}</p>
                                    <div class="flex items-center mt-1">
                                        <span class="text-xs text-gray-500 mr-2">Nota:</span>
                                        <div class="flex">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= $review->grade ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-500 ml-2">{{ \Carbon\Carbon::parse($review->created_at)->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-history text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500">Nenhuma revisão recente</p>
                        <p class="text-sm text-gray-400">Comece a revisar seus itens de estudo.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Study Streak -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Sequência de Estudos</h3>
                <div class="text-center">
                    <div class="text-6xl font-bold text-orange-500 mb-2">{{ $studyStreak['current_streak'] }}</div>
                    <p class="text-lg text-gray-600 mb-4">dias consecutivos</p>
                    
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-green-600">{{ $studyStreak['longest_streak'] }}</div>
                            <div class="text-sm text-gray-500">Maior Sequência</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600">{{ $studyStreak['total_study_days'] }}</div>
                            <div class="text-sm text-gray-500">Total de Dias</div>
                        </div>
                    </div>
                    
                    @if($studyStreak['current_streak'] > 0)
                        <div class="mt-4 p-3 bg-orange-50 rounded-lg">
                            <p class="text-sm text-orange-800">
                                <i class="fas fa-fire mr-1"></i>
                                Continue assim! Você está em uma ótima sequência de estudos.
                            </p>
                        </div>
                    @else
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-play mr-1"></i>
                                Comece uma nova sequência de estudos hoje!
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Progress Chart
    const ctx = document.getElementById('progressChart').getContext('2d');
    
    // Get progress data from data attributes
    const progressElement = document.getElementById('progressChart');
    const completedPercentage = parseFloat(progressElement.getAttribute('data-completed') || '0');
    const inProgressPercentage = parseFloat(progressElement.getAttribute('data-in-progress') || '0');
    const notStartedPercentage = parseFloat(progressElement.getAttribute('data-not-started') || '0');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Concluído', 'Em Progresso', 'Não Iniciado'],
            datasets: [{
                data: [
                    completedPercentage,
                    inProgressPercentage,
                    notStartedPercentage
                ],
                backgroundColor: [
                    '#10B981',
                    '#F59E0B',
                    '#6B7280'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush
@endsection