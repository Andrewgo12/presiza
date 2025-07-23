@extends('layouts.app')

@section('title', 'Búsqueda Global')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                    Búsqueda Global
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Busca en proyectos, evidencias, archivos y más
                </p>
            </div>
        </div>

        <!-- Search form -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" action="{{ route('search') }}" class="space-y-4">
                    <div class="flex space-x-4">
                        <div class="flex-1">
                            <label for="q" class="block text-sm font-medium text-gray-700">Buscar</label>
                            <input type="text" 
                                   name="q" 
                                   id="q"
                                   value="{{ $query }}"
                                   placeholder="Escribe tu búsqueda aquí..."
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   autofocus>
                        </div>
                        
                        <div class="w-48">
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="all" {{ request('type') === 'all' ? 'selected' : '' }}>Todo</option>
                                <option value="projects" {{ request('type') === 'projects' ? 'selected' : '' }}>Proyectos</option>
                                <option value="evidences" {{ request('type') === 'evidences' ? 'selected' : '' }}>Evidencias</option>
                                <option value="files" {{ request('type') === 'files' ? 'selected' : '' }}>Archivos</option>
                                <option value="users" {{ request('type') === 'users' ? 'selected' : '' }}>Usuarios</option>
                                <option value="groups" {{ request('type') === 'groups' ? 'selected' : '' }}>Grupos</option>
                                <option value="milestones" {{ request('type') === 'milestones' ? 'selected' : '' }}>Hitos</option>
                            </select>
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($query)
            <!-- Search results -->
            <div class="mt-6">
                @if($total > 0)
                    <div class="mb-4">
                        <p class="text-sm text-gray-700">
                            Se encontraron <span class="font-medium">{{ $total }}</span> resultados para 
                            "<span class="font-medium">{{ $query }}</span>"
                        </p>
                    </div>

                    @foreach($results as $type => $items)
                        @if(count($items) > 0)
                            <div class="mb-8 bg-white shadow rounded-lg overflow-hidden">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        {{ ucfirst($type) }} ({{ count($items) }})
                                    </h3>
                                </div>
                                
                                <ul class="divide-y divide-gray-200">
                                    @foreach($items as $item)
                                        <li class="px-4 py-4 hover:bg-gray-50">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    @if($type === 'projects')
                                                        <div class="h-10 w-10 rounded-lg bg-blue-500 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                                                            </svg>
                                                        </div>
                                                    @elseif($type === 'evidences')
                                                        <div class="h-10 w-10 rounded-lg bg-green-500 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                                            </svg>
                                                        </div>
                                                    @elseif($type === 'files')
                                                        <div class="h-10 w-10 rounded-lg bg-yellow-500 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                            </svg>
                                                        </div>
                                                    @elseif($type === 'users')
                                                        <img class="h-10 w-10 rounded-full" 
                                                             src="{{ $item['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($item['title']) . '&color=7F9CF5&background=EBF4FF' }}" 
                                                             alt="{{ $item['title'] }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-lg bg-gray-500 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                                <a href="{{ $item['url'] }}" class="hover:text-indigo-600">
                                                                    {{ $item['title'] }}
                                                                </a>
                                                            </p>
                                                            @if(isset($item['subtitle']))
                                                                <p class="text-sm text-gray-500 truncate">
                                                                    {{ $item['subtitle'] }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        
                                                        @if(isset($item['status']))
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">
                                                                {{ $item['status'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="flex-shrink-0">
                                                    <a href="{{ $item['url'] }}" 
                                                       class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                        Ver
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron resultados</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            No se encontraron resultados para "{{ $query }}". Intenta con otros términos de búsqueda.
                        </p>
                    </div>
                @endif
            </div>
        @else
            <!-- Search suggestions -->
            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Sugerencias de búsqueda
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('search', ['type' => 'projects']) }}" class="focus:outline-none">
                                        <span class="absolute inset-0"></span>
                                        <p class="text-sm font-medium text-gray-900">Proyectos</p>
                                        <p class="text-sm text-gray-500">Buscar en proyectos activos</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('search', ['type' => 'evidences']) }}" class="focus:outline-none">
                                        <span class="absolute inset-0"></span>
                                        <p class="text-sm font-medium text-gray-900">Evidencias</p>
                                        <p class="text-sm text-gray-500">Buscar evidencias y casos</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('search', ['type' => 'files']) }}" class="focus:outline-none">
                                        <span class="absolute inset-0"></span>
                                        <p class="text-sm font-medium text-gray-900">Archivos</p>
                                        <p class="text-sm text-gray-500">Buscar documentos y archivos</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus search input
    const searchInput = document.getElementById('q');
    if (searchInput && !searchInput.value) {
        searchInput.focus();
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
        }
    });
});
</script>
@endpush
@endsection
