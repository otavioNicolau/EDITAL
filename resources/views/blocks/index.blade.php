@extends('layouts.app')

@section('title', 'Blocos - Estudo Concurso')

@section('content')
<div class="px-4 sm:px-0">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Blocos de Estudo</h1>
            <p class="mt-2 text-gray-600">Organize seus estudos em blocos temáticos</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('blocks.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i>
                Novo Bloco
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                    <div class="mt-1 relative">
                        <input type="text" 
                               id="search" 
                               name="search" 
                               placeholder="Nome do bloco..."
                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" 
                            name="status" 
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">Todos os status</option>
                        <option value="not_started">Não Iniciado</option>
                        <option value="in_progress">Em Progresso</option>
                        <option value="completed">Concluído</option>
                    </select>
                </div>
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700">Ordenar por</label>
                    <select id="sort" 
                            name="sort" 
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="name">Nome</option>
                        <option value="created_at">Data de Criação</option>
                        <option value="updated_at">Última Atualização</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Blocks Grid -->
    <div id="blocks-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Blocks will be loaded here via AJAX -->
    </div>

    <!-- Loading State -->
    <div id="loading" class="text-center py-8">
        <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        <p class="mt-2 text-gray-500">Carregando blocos...</p>
    </div>

    <!-- Empty State -->
    <div id="empty-state" class="text-center py-12 hidden">
        <i class="fas fa-cube text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum bloco encontrado</h3>
        <p class="text-gray-500 mb-6">Comece criando seu primeiro bloco de estudos.</p>
        <a href="{{ route('blocks.create') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>
            Criar Primeiro Bloco
        </a>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const sortSelect = document.getElementById('sort');
    const blocksContainer = document.getElementById('blocks-container');
    const loading = document.getElementById('loading');
    const emptyState = document.getElementById('empty-state');

    let debounceTimer;

    function loadBlocks() {
        loading.classList.remove('hidden');
        blocksContainer.innerHTML = '';
        emptyState.classList.add('hidden');

        const params = new URLSearchParams({
            search: searchInput.value,
            status: statusSelect.value,
            sort: sortSelect.value
        });

        fetch(`/api/blocks?${params}`)
            .then(response => response.json())
            .then(data => {
                loading.classList.add('hidden');

                const blocks = Array.isArray(data) ? data : (data.data || []);

                if (blocks.length > 0) {
                    renderBlocks(blocks);
                } else {
                    emptyState.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading blocks:', error);
                loading.classList.add('hidden');
                emptyState.classList.remove('hidden');
            });
    }

    function renderBlocks(blocks) {
        blocksContainer.innerHTML = blocks.map(block => `
            <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 truncate">${block.name}</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(block.status)}">
                            ${getStatusText(block.status)}
                        </span>
                    </div>
                    ${block.description ? `<p class="text-sm text-gray-600 mb-4 line-clamp-2">${block.description}</p>` : ''}

                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <span><i class="fas fa-book mr-1"></i>${block.disciplines_count || 0} matérias</span>
                        <span><i class="fas fa-calendar mr-1"></i>${formatDate(block.created_at)}</span>
                    </div>

                    ${renderDisciplinesPreview(block.disciplines)}

                    ${typeof block.progress_percentage !== 'undefined' ? `
                        <div class="mt-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Progresso dos tópicos</span>
                                <span>${Math.round(block.progress_percentage)}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${block.progress_percentage}%"></div>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="flex justify-between">
                        <a href="/blocks/${block.id}" 
                           class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i>Visualizar
                        </a>
                        <div class="space-x-2">
                            <a href="/blocks/${block.id}/edit" 
                               class="text-gray-600 hover:text-gray-500 text-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteBlock(${block.id})" 
                                    class="text-red-600 hover:text-red-500 text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderDisciplinesPreview(disciplines = []) {
        if (!disciplines || disciplines.length === 0) {
            return '<p class="text-sm text-gray-500">Nenhuma matéria cadastrada ainda.</p>';
        }

        const preview = disciplines.slice(0, 3)
            .map(discipline => `<li class="text-sm text-gray-600">${discipline.name}</li>`)
            .join('');

        const remaining = disciplines.length - 3;

        return `
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">Matérias vinculadas</p>
                <ul class="space-y-1">
                    ${preview}
                </ul>
                ${remaining > 0 ? `<p class="text-xs text-gray-500 mt-2">+ ${remaining} matéria(s) adicionais</p>` : ''}
            </div>
        `;
    }

    function getStatusBadgeClass(status) {
        const classes = {
            'not_started': 'bg-gray-100 text-gray-800',
            'in_progress': 'bg-yellow-100 text-yellow-800',
            'completed': 'bg-green-100 text-green-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    function getStatusText(status) {
        const texts = {
            'not_started': 'Não Iniciado',
            'in_progress': 'Em Progresso',
            'completed': 'Concluído'
        };
        return texts[status] || 'Desconhecido';
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR');
    }

    function debounce(func, wait) {
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(debounceTimer);
                func(...args);
            };
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(later, wait);
        };
    }

    // Event listeners
    searchInput.addEventListener('input', debounce(loadBlocks, 300));
    statusSelect.addEventListener('change', loadBlocks);
    sortSelect.addEventListener('change', loadBlocks);

    // Initial load
    loadBlocks();

    // Delete function
    window.deleteBlock = function(blockId) {
        if (confirm('Tem certeza que deseja excluir este bloco? Esta ação não pode ser desfeita.')) {
            fetch(`/api/blocks/${blockId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    loadBlocks();
                } else {
                    alert('Erro ao excluir o bloco. Tente novamente.');
                }
            })
            .catch(error => {
                console.error('Error deleting block:', error);
                alert('Erro ao excluir o bloco. Tente novamente.');
            });
        }
    };
});
</script>
@endpush
@endsection
