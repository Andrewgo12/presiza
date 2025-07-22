@extends('layouts.app')

@section('title', 'Archivos')

@section('content')
<div class="space-y-6" x-data="filesManager()">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Gestión de Archivos
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Administra y organiza todos tus archivos y documentos
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:ml-4 md:mt-0">
            <button @click="toggleView()" 
                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg x-show="viewMode === 'grid'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 17.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <svg x-show="viewMode === 'list'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span x-text="viewMode === 'grid' ? 'Vista Lista' : 'Vista Cuadrícula'" class="ml-2"></span>
            </button>
            <a href="{{ route('files.create') }}" 
               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Subir Archivo
            </a>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Búsqueda -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Buscar archivos</label>
                <input type="text" 
                       name="search" 
                       id="search"
                       x-model="filters.search"
                       @input.debounce.300ms="applyFilters()"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       placeholder="Nombre, descripción...">
            </div>

            <!-- Categoría -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Categoría</label>
                <select name="category" 
                        id="category"
                        x-model="filters.category"
                        @change="applyFilters()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todas las categorías</option>
                    <option value="document">Documentos</option>
                    <option value="image">Imágenes</option>
                    <option value="video">Videos</option>
                    <option value="audio">Audio</option>
                    <option value="archive">Archivos</option>
                    <option value="other">Otros</option>
                </select>
            </div>

            <!-- Fecha -->
            <div>
                <label for="date_range" class="block text-sm font-medium text-gray-700">Fecha de subida</label>
                <select name="date_range" 
                        id="date_range"
                        x-model="filters.dateRange"
                        @change="applyFilters()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Cualquier fecha</option>
                    <option value="today">Hoy</option>
                    <option value="week">Esta semana</option>
                    <option value="month">Este mes</option>
                    <option value="year">Este año</option>
                </select>
            </div>

            <!-- Ordenar -->
            <div>
                <label for="sort" class="block text-sm font-medium text-gray-700">Ordenar por</label>
                <select name="sort" 
                        id="sort"
                        x-model="filters.sort"
                        @change="applyFilters()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="created_at_desc">Más recientes</option>
                    <option value="created_at_asc">Más antiguos</option>
                    <option value="name_asc">Nombre A-Z</option>
                    <option value="name_desc">Nombre Z-A</option>
                    <option value="size_desc">Tamaño mayor</option>
                    <option value="size_asc">Tamaño menor</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Lista de archivos -->
    <div class="bg-white shadow rounded-lg">
        <!-- Vista de cuadrícula -->
        <div x-show="viewMode === 'grid'" class="p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($files as $file)
                <div class="group relative rounded-lg border border-gray-300 bg-white p-4 hover:border-gray-400 hover:shadow-md transition-all duration-200">
                    <!-- Thumbnail -->
                    <div class="aspect-square w-full overflow-hidden rounded-lg bg-gray-100 mb-3">
                        @if(str_starts_with($file->mime_type, 'image/'))
                            <img src="{{ $file->thumbnail_url ?? Storage::url($file->path) }}" 
                                 alt="{{ $file->original_name }}"
                                 class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                @include('components.file-icon', ['mimeType' => $file->mime_type, 'size' => 'h-12 w-12'])
                            </div>
                        @endif
                    </div>

                    <!-- Información del archivo -->
                    <div class="space-y-2">
                        <h3 class="text-sm font-medium text-gray-900 truncate" title="{{ $file->original_name }}">
                            {{ $file->original_name }}
                        </h3>
                        <p class="text-xs text-gray-500">
                            {{ $file->size_formatted }} • {{ $file->created_at->diffForHumans() }}
                        </p>
                        @if($file->tags && count($file->tags) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($file->tags, 0, 2) as $tag)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                                @if(count($file->tags) > 2)
                                    <span class="text-xs text-gray-500">+{{ count($file->tags) - 2 }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Acciones -->
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <div class="flex space-x-1">
                            <a href="{{ route('files.show', $file) }}" 
                               class="rounded-full bg-white p-1.5 text-gray-400 hover:text-gray-600 shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </a>
                            <a href="{{ route('files.download', $file) }}" 
                               class="rounded-full bg-white p-1.5 text-gray-400 hover:text-gray-600 shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Link invisible para toda la card -->
                    <a href="{{ route('files.show', $file) }}" class="absolute inset-0 z-0">
                        <span class="sr-only">Ver {{ $file->original_name }}</span>
                    </a>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 18H3.75c-.621 0-1.125-.504-1.125-1.125V1.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v4.5c0 .621.504 1.125 1.125 1.125h4.125c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125H16.5a1.125 1.125 0 01-1.125-1.125v-1.5a1.125 1.125 0 00-1.125-1.125H12" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Sin archivos</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza subiendo tu primer archivo.</p>
                    <div class="mt-6">
                        <a href="{{ route('files.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Subir Archivo
                        </a>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Vista de lista -->
        <div x-show="viewMode === 'list'" x-cloak>
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamaño</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($files as $file)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if(str_starts_with($file->mime_type, 'image/'))
                                            <img class="h-10 w-10 rounded object-cover" src="{{ $file->thumbnail_url ?? Storage::url($file->path) }}" alt="{{ $file->original_name }}">
                                        @else
                                            <div class="h-10 w-10 rounded bg-gray-100 flex items-center justify-center">
                                                @include('components.file-icon', ['mimeType' => $file->mime_type, 'size' => 'h-6 w-6'])
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $file->original_name }}</div>
                                        @if($file->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($file->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $file->size_formatted }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 capitalize">
                                    {{ $file->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $file->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('files.show', $file) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                    <a href="{{ route('files.download', $file) }}" class="text-green-600 hover:text-green-900">Descargar</a>
                                    @can('update', $file)
                                        <a href="{{ route('files.edit', $file) }}" class="text-yellow-600 hover:text-yellow-900">Editar</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 18H3.75c-.621 0-1.125-.504-1.125-1.125V1.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v4.5c0 .621.504 1.125 1.125 1.125h4.125c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125H16.5a1.125 1.125 0 01-1.125-1.125v-1.5a1.125 1.125 0 00-1.125-1.125H12" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900">Sin archivos</h3>
                                <p class="mt-1 text-sm text-gray-500">Comienza subiendo tu primer archivo.</p>
                                <div class="mt-6">
                                    <a href="{{ route('files.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Subir Archivo
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        @if($files->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                {{ $files->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function filesManager() {
    return {
        viewMode: localStorage.getItem('files_view_mode') || 'grid',
        filters: {
            search: '{{ request('search') }}',
            category: '{{ request('category') }}',
            dateRange: '{{ request('date_range') }}',
            sort: '{{ request('sort', 'created_at_desc') }}'
        },
        
        toggleView() {
            this.viewMode = this.viewMode === 'grid' ? 'list' : 'grid';
            localStorage.setItem('files_view_mode', this.viewMode);
        },
        
        applyFilters() {
            const params = new URLSearchParams();
            
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.append(key, this.filters[key]);
                }
            });
            
            const url = new URL(window.location);
            url.search = params.toString();
            window.location.href = url.toString();
        }
    }
}
</script>
@endsection
