@extends('layouts.app')

@section('title', 'Criar Bloco - Estudo Concurso')

@section('content')
<div class="px-4 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('blocks.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Criar Novo Bloco</h1>
        </div>
        <p class="text-gray-600">Crie um novo bloco de estudos para organizar seus tópicos</p>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form id="block-form" class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nome do Bloco <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input type="text" 
                               id="name" 
                               name="name" 
                               required
                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               placeholder="Ex: Direito Constitucional">
                    </div>
                    <div id="name-error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Descrição
                    </label>
                    <div class="mt-1">
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                  placeholder="Descreva o conteúdo e objetivos deste bloco de estudos..."></textarea>
                    </div>
                    <div id="description-error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700">
                        Cor do Bloco
                    </label>
                    <div class="mt-1">
                        <div class="flex space-x-3">
                            <label class="inline-flex items-center">
                                <input type="radio" name="color" value="blue" checked class="form-radio text-blue-600">
                                <span class="ml-2 w-6 h-6 bg-blue-500 rounded-full"></span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="color" value="green" class="form-radio text-green-600">
                                <span class="ml-2 w-6 h-6 bg-green-500 rounded-full"></span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="color" value="purple" class="form-radio text-purple-600">
                                <span class="ml-2 w-6 h-6 bg-purple-500 rounded-full"></span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="color" value="red" class="form-radio text-red-600">
                                <span class="ml-2 w-6 h-6 bg-red-500 rounded-full"></span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="color" value="yellow" class="form-radio text-yellow-600">
                                <span class="ml-2 w-6 h-6 bg-yellow-500 rounded-full"></span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="color" value="indigo" class="form-radio text-indigo-600">
                                <span class="ml-2 w-6 h-6 bg-indigo-500 rounded-full"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        Status Inicial
                    </label>
                    <div class="mt-1">
                        <select id="status" 
                                name="status" 
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="not_started">Não Iniciado</option>
                            <option value="in_progress">Em Progresso</option>
                            <option value="completed">Concluído</option>
                        </select>
                    </div>
                    <div id="status-error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>

                <!-- Goals -->
                <div>
                    <label for="goals" class="block text-sm font-medium text-gray-700">
                        Objetivos de Aprendizado
                    </label>
                    <div class="mt-1">
                        <textarea id="goals" 
                                  name="goals" 
                                  rows="3"
                                  class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                  placeholder="Liste os principais objetivos que você deseja alcançar com este bloco..."></textarea>
                    </div>
                    <div id="goals-error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('blocks.index') }}" 
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancelar
                </a>
                <button type="submit" 
                        id="submit-btn"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submit-text">Criar Bloco</span>
                    <i id="submit-spinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('block-form');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        clearErrors();
        
        // Show loading state
        setLoading(true);
        
        // Get form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Submit form
        fetch('/api/blocks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            // Success - redirect to blocks list
            window.location.href = '/blocks';
        })
        .catch(error => {
            console.error('Error creating block:', error);
            setLoading(false);
            
            if (error.errors) {
                // Validation errors
                showErrors(error.errors);
            } else {
                // General error
                alert(error.message || 'Erro ao criar o bloco. Tente novamente.');
            }
        });
    });

    function setLoading(loading) {
        submitBtn.disabled = loading;
        if (loading) {
            submitText.textContent = 'Criando...';
            submitSpinner.classList.remove('hidden');
        } else {
            submitText.textContent = 'Criar Bloco';
            submitSpinner.classList.add('hidden');
        }
    }

    function clearErrors() {
        const errorElements = document.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });
        
        const inputElements = document.querySelectorAll('input, textarea, select');
        inputElements.forEach(element => {
            element.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            element.classList.add('border-gray-300', 'focus:border-blue-500', 'focus:ring-blue-500');
        });
    }

    function showErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`${field}-error`);
            const inputElement = document.getElementById(field);
            
            if (errorElement && inputElement) {
                errorElement.textContent = errors[field][0];
                errorElement.classList.remove('hidden');
                
                inputElement.classList.remove('border-gray-300', 'focus:border-blue-500', 'focus:ring-blue-500');
                inputElement.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            }
        });
    }
});
</script>
@endpush
@endsection