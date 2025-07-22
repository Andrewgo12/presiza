@extends('layouts.app')

@section('title', 'Proyectos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Proyectos
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Gestiona y supervisa el progreso de todos los proyectos de desarrollo
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create') }}" 
                   class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo Proyecto
                </a>
            @endcan
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-5">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">En Progreso</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['in_progress'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completados</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['completed'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Atrasados</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['overdue'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Mis Proyectos</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['my_projects'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white shadow rounded-lg p-6">
        <form method="GET" action="{{ route('projects.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                <!-- Búsqueda -->
                <div class="sm:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ request('search') }}"
                           placeholder="Buscar por nombre, descripción o cliente..."
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos los estados</option>
                        <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Planificación</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                        <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>En Pausa</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <!-- Prioridad -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Prioridad</label>
                    <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todas las prioridades</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Baja</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Media</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                        <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Crítica</option>
                    </select>
                </div>

                <!-- Solo mis proyectos -->
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="my_projects" 
                               value="1" 
                               {{ request('my_projects') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Solo mis proyectos</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('projects.index') }}" 
                   class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Limpiar
                </a>
                <button type="submit" 
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de proyectos -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                Proyectos
                <span class="ml-2 text-sm text-gray-500">({{ $projects->total() }} proyectos)</span>
            </h3>
        </div>

        @if($projects->count() > 0)
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($projects as $project)
                    <div class="relative rounded-lg border border-gray-300 bg-white p-6 shadow-sm hover:border-gray-400 hover:shadow-md transition-all duration-200">
                        <div class="flex items-start justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $project->status_badge_color }}">
                                        {{ $project->status_display_name }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $project->priority_badge_color }}">
                                        {{ $project->priority_display_name }}
                                    </span>
                                </div>
                                
                                <h4 class="text-lg font-medium text-gray-900 mb-2">
                                    <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">
                                        {{ $project->name }}
                                    </a>
                                </h4>
                                
                                <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $project->description }}</p>
                                
                                <!-- Barra de progreso -->
                                <div class="mb-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Progreso</span>
                                        <span class="font-medium">{{ $project->progress_percentage }}%</span>
                                    </div>
                                    <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                                             style="width: {{ $project->progress_percentage }}%"></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <div class="flex items-center space-x-4">
                                        <span class="flex items-center">
                                            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                            {{ $project->members_count }} miembros
                                        </span>
                                        @if($project->deadline)
                                            <span class="flex items-center {{ $project->is_overdue ? 'text-red-600' : '' }}">
                                                <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5m-18 0h18" />
                                                </svg>
                                                {{ $project->deadline->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mt-3 text-xs text-gray-500">
                                    <span>Gerente: {{ $project->manager->full_name ?? 'No asignado' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <a href="{{ route('projects.show', $project) }}" class="absolute inset-0" aria-hidden="true"></a>
                    </div>
                @endforeach
            </div>

            <!-- Paginación -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $projects->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay proyectos</h3>
                <p class="mt-1 text-sm text-gray-500">No se encontraron proyectos con los filtros aplicados.</p>
                @can('create', App\Models\Project::class)
                    <div class="mt-6">
                        <a href="{{ route('projects.create') }}" 
                           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Crear Primer Proyecto
                        </a>
                    </div>
                @endcan
            </div>
        @endif
    </div>
</div>
@endsection
