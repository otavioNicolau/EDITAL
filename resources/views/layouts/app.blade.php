<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Estudo Concurso')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('concursos.index') }}" class="text-xl font-bold text-blue-600">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            PROJETO PRF
                        </a>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('concursos.index') }}" 
                           class="@if(request()->routeIs('concursos.*')) border-blue-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-university mr-2"></i>
                            CURSOS                        </a>
                        
                        {{-- <a href="{{ route('dashboard') }}" 
                           class="@if(request()->routeIs('dashboard')) border-blue-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            DASHBOARD
                        </a>
                         --}}
                        <a href="{{ route('blocks.index') }}" 
                           class="@if(request()->routeIs('blocks.*')) border-blue-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-cube mr-2"></i>
                            EDITAL
                        </a>
                        
                        {{-- <a href="{{ route('disciplines.index') }}" 
                           class="@if(request()->routeIs('disciplines.*')) border-blue-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            Disciplinas
                        </a>
                        
                        <a href="{{ route('topics.index') }}" 
                           class="@if(request()->routeIs('topics.*')) border-blue-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-list mr-2"></i>
                            Tópicos
                        </a> --}}
                        
                        {{-- <a href="{{ route('study-items.index') }}" 
                           class="@if(request()->routeIs('study-items.*')) border-blue-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-book mr-2"></i>
                            Itens de Estudo
                        </a> --}}
                        
                        {{-- <a href="{{ route('reviews.index') }}" 
                           class="@if(request()->routeIs('reviews.*')) border-blue-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-redo mr-2"></i>
                            Revisões
                        </a>
                         --}}
                        {{-- <a href="{{ route('metrics.index') }}" 
                           class="@if(request()->routeIs('metrics.*')) border-blue-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-chart-line mr-2"></i>
                            Métricas
                        </a> --}}
                    </div>
                
                <!-- Mobile menu button -->
                <div class="sm:hidden flex items-center">
                    <button x-data x-on:click="$dispatch('toggle-mobile-menu')" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div x-data="{ open: false }" x-on:toggle-mobile-menu.window="open = !open" x-show="open" x-cloak class="sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('concursos.index') }}" class="@if(request()->routeIs('concursos.*')) bg-blue-50 border-blue-500 text-blue-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-university mr-2"></i>Concursos
                </a>
                <a href="{{ route('dashboard') }}" class="@if(request()->routeIs('dashboard')) bg-blue-50 border-blue-500 text-blue-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="{{ route('blocks.index') }}" class="@if(request()->routeIs('blocks.*')) bg-blue-50 border-blue-500 text-blue-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-cube mr-2"></i>Blocos
                </a>
                <a href="{{ route('disciplines.index') }}" class="@if(request()->routeIs('disciplines.*')) bg-blue-50 border-blue-500 text-blue-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-graduation-cap mr-2"></i>Disciplinas
                </a>
                <a href="{{ route('topics.index') }}" class="@if(request()->routeIs('topics.*')) bg-blue-50 border-blue-500 text-blue-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-list mr-2"></i>Tópicos
                </a>
                <a href="{{ route('study-items.index') }}" class="@if(request()->routeIs('study-items.*')) bg-blue-50 border-blue-500 text-blue-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-book mr-2"></i>Itens de Estudo
                </a>
                <a href="{{ route('reviews.index') }}" class="@if(request()->routeIs('reviews.*')) bg-blue-50 border-blue-500 text-blue-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-redo mr-2"></i>Revisões
                </a>
                <a href="{{ route('metrics.index') }}" class="@if(request()->routeIs('metrics.*')) bg-blue-50 border-blue-500 text-blue-700 @else border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-chart-line mr-2"></i>Métricas
                </a>
            </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Scripts -->
    <script>
        // CSRF Token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
        
        // Set CSRF token for all AJAX requests
        if (window.axios) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
        }
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('video-controls.js') }}" defer></script>

    @stack('scripts')
</body>
</html>
