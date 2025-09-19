@extends('layouts.app')

@section('title', 'Bloco: ' . $block->name . ' - Estudo Concurso')

@section('content')
<div class="px-4 sm:px-0">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('blocks.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <i class="fas fa-layer-group mr-2"></i>
                    Blocos
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $block->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $block->name }}</h1>
            @if($block->description)
                <p class="mt-2 text-gray-600">{{ $block->description }}</p>
            @endif
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('blocks.edit', $block) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-edit mr-2"></i>
                Editar
            </a>
            <button type="button" 
                    onclick="deleteBlock('{{ $block->id }}')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <i class="fas fa-trash mr-2"></i>
                Excluir
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-list-ul text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Disciplinas</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-disciplines">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-book text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Itens de Estudo</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-items">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-redo text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Revisões</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-reviews">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Progresso</dt>
                            <dd class="text-lg font-medium text-gray-900" id="progress-percent">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Disciplines List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center sm:justify-between mb-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Disciplinas do Bloco</h3>
                <a href="{{ route('disciplines.create') }}?block_id={{ $block->id }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-plus mr-2"></i>
                    Nova Disciplina
                </a>
            </div>

            <!-- Loading State -->
            <div id="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Carregando disciplinas...</p>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="text-center py-8 hidden">
                <i class="fas fa-graduation-cap text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma disciplina encontrada</h3>
                <p class="text-gray-600 mb-4">Este bloco ainda não possui disciplinas de estudo.</p>
                <a href="{{ route('disciplines.create') }}?block_id={{ $block->id }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Primeira Disciplina
                </a>
            </div>

            <!-- Disciplines Grid -->
            <div id="disciplines-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                <!-- Disciplines will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmar Exclusão</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Tem certeza que deseja excluir este bloco? Esta ação não pode ser desfeita e todos os tópicos e itens relacionados também serão excluídos.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDelete" 
                        class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Excluir
                </button>
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const blockId = '{{ $block->id }}';
    
    loadBlockData();
    
    async function loadBlockData() {
        try {
            // Load disciplines for this block
            const response = await fetch(`/api/disciplines?block_id=${blockId}`);
            if (!response.ok) throw new Error('Erro ao carregar dados');
            
            const data = await response.json();
            const disciplines = Array.isArray(data) ? data : (data.data || []);
            
            // Update stats based on disciplines
            let totalTopics = 0;
            let completedTopics = 0;
            let totalItems = 0;
            let totalReviews = 0;

            disciplines.forEach(discipline => {
                totalTopics += discipline.topics_count || 0;
                completedTopics += discipline.completed_topics_count || 0;
                totalItems += discipline.study_items_count || 0;
                totalReviews += discipline.reviews_count || 0;
            });

            document.getElementById('total-disciplines').textContent = disciplines.length;
            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('total-reviews').textContent = totalReviews;

            const progress = totalTopics > 0 ? Math.round((completedTopics / totalTopics) * 100) : 0;
            document.getElementById('progress-percent').textContent = progress + '%';
            
            // Load disciplines
            loadDisciplines(disciplines);
            
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao carregar dados do bloco');
        }
    }
    
    function loadDisciplines(disciplines) {
        const loading = document.getElementById('loading');
        const emptyState = document.getElementById('empty-state');
        const disciplinesGrid = document.getElementById('disciplines-grid');
        
        loading.classList.add('hidden');
        
        if (disciplines.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        }
        
        disciplinesGrid.innerHTML = '';
        
        disciplines.forEach(discipline => {
            const disciplineCard = createDisciplineCard(discipline);
            disciplinesGrid.appendChild(disciplineCard);
        });
        
        disciplinesGrid.classList.remove('hidden');
    }
    
    function createDisciplineCard(discipline) {
        const div = document.createElement('div');
        div.className = 'bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow cursor-pointer';
        div.onclick = () => window.location.href = `/disciplines/${discipline.id}`;
        
        const statusColors = {
            'active': 'bg-green-100 text-green-800',
            'inactive': 'bg-gray-100 text-gray-800'
        };
        
        div.innerHTML = `
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h4 class="text-lg font-medium text-gray-900 mb-2">${discipline.name}</h4>
                    ${discipline.description ? `<p class="text-sm text-gray-600 mb-3">${discipline.description}</p>` : ''}
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span><i class="fas fa-list-ul mr-1"></i>${discipline.topics_count || 0} tópicos</span>
                        <span><i class="fas fa-book mr-1"></i>${discipline.study_items_count || 0} itens</span>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[discipline.status] || statusColors.inactive}">
                    ${discipline.status === 'active' ? 'Ativa' : 'Inativa'}
                </span>
            </div>
        `;
        
        return div;
    }
    
    function showError(message) {
        // Simple error display - you can enhance this
        alert(message);
    }
});

function deleteBlock(blockId) {
    document.getElementById('deleteModal').classList.remove('hidden');
    
    document.getElementById('confirmDelete').onclick = async function() {
        try {
            const response = await fetch(`/api/blocks/${blockId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                window.location.href = '/blocks';
            } else {
                throw new Error('Erro ao excluir bloco');
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao excluir bloco');
        }
        
        closeDeleteModal();
    };
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endsection
