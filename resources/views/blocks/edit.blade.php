@extends('layouts.app')

@section('title', 'Editar Bloco - Estudo Concurso')

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
                    <a href="{{ route('blocks.show', $block) }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">{{ $block->name }}</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Editar</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Editar Bloco</h1>
        <p class="mt-2 text-gray-600">Modifique as informações do bloco de estudo</p>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form id="editBlockForm">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Nome -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nome do Bloco <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ $block->name }}"
                                   required
                                   maxlength="100"
                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   placeholder="Ex: Direito Constitucional, Língua Portuguesa...">
                        </div>
                        <div class="mt-1 flex justify-between">
                            <p class="text-sm text-red-600 hidden" id="name-error"></p>
                            <p class="text-sm text-gray-500" id="name-counter">0/100</p>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Descrição
                        </label>
                        <div class="mt-1">
                            <textarea id="description" 
                                      name="description" 
                                      rows="4"
                                      maxlength="500"
                                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                      placeholder="Descreva brevemente o conteúdo deste bloco de estudo...">{{ $block->description }}</textarea>
                        </div>
                        <div class="mt-1 flex justify-between">
                            <p class="text-sm text-red-600 hidden" id="description-error"></p>
                            <p class="text-sm text-gray-500" id="description-counter">0/500</p>
                        </div>
                    </div>

                    <!-- Ordem -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700">
                            Ordem de Exibição
                        </label>
                        <div class="mt-1">
                            <input type="number" 
                                   id="order" 
                                   name="order" 
                                   value="{{ $block->order ?? 0 }}"
                                   min="0"
                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   placeholder="0">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            Número que define a ordem de exibição dos blocos (0 = primeiro)
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('blocks.show', $block) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    <button type="submit" 
                            id="submitBtn"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Bloco Atualizado!</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    As alterações foram salvas com sucesso.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button onclick="closeSuccessModal()" 
                        class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editBlockForm');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const nameCounter = document.getElementById('name-counter');
    const descriptionCounter = document.getElementById('description-counter');
    const submitBtn = document.getElementById('submitBtn');
    
    // Update character counters
    function updateCounters() {
        nameCounter.textContent = `${nameInput.value.length}/100`;
        descriptionCounter.textContent = `${descriptionInput.value.length}/500`;
    }
    
    nameInput.addEventListener('input', updateCounters);
    descriptionInput.addEventListener('input', updateCounters);
    
    // Initialize counters
    updateCounters();
    
    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous errors
        clearErrors();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
        
        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            const response = await fetch(`/api/blocks/{{ $block->id }}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                showSuccessModal();
            } else {
                if (result.details) {
                    showValidationErrors(result.details);
                } else {
                    showError(result.error || 'Erro ao salvar bloco');
                }
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro de conexão. Tente novamente.');
        } finally {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Salvar Alterações';
        }
    });
    
    function validateForm() {
        let isValid = true;
        
        // Validate name
        if (!nameInput.value.trim()) {
            showFieldError('name', 'Nome é obrigatório');
            isValid = false;
        } else if (nameInput.value.length > 100) {
            showFieldError('name', 'Nome deve ter no máximo 100 caracteres');
            isValid = false;
        }
        
        // Validate description
        if (descriptionInput.value.length > 500) {
            showFieldError('description', 'Descrição deve ter no máximo 500 caracteres');
            isValid = false;
        }
        
        return isValid;
    }
    
    function showFieldError(fieldName, message) {
        const errorElement = document.getElementById(`${fieldName}-error`);
        const inputElement = document.getElementById(fieldName);
        
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
        inputElement.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
    }
    
    function clearErrors() {
        const errorElements = document.querySelectorAll('[id$="-error"]');
        const inputElements = document.querySelectorAll('input, textarea');
        
        errorElements.forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        
        inputElements.forEach(el => {
            el.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        });
    }
    
    function showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            if (errors[field] && errors[field].length > 0) {
                showFieldError(field, errors[field][0]);
            }
        });
    }
    
    function showError(message) {
        alert(message); // Simple error display - you can enhance this
    }
    
    function showSuccessModal() {
        document.getElementById('successModal').classList.remove('hidden');
    }
});

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    window.location.href = '/blocks/{{ $block->id }}';
}
</script>
@endsection