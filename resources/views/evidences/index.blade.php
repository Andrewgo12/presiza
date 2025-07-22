@extends('layouts.app')

@section('title', 'Evidencias')

@section('content')
<div class="space-y-6" x-data="evidenceManager()">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Gestión de Evidencias
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Administra y evalúa todas las evidencias del sistema
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:ml-4 md:mt-0">
            <button @click="showFilters = !showFilters" 
                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                Filtros
            </button>
            <a href="{{ route('evidences.create') }}" 
               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nueva Evidencia
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Estado -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="status" 
                        id="status"
                        x-model="filters.status"
                        @change="applyFilters()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendiente</option>
                    <option value="under_review">En revisión</option>
                    <option value="approved">Aprobado</option>
                    <option value="rejected">Rechazado</option>
                    <option value="archived">Archivado</option>
                </select>
            </div>

            <!-- Prioridad -->
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700">Prioridad</label>
                <select name="priority" 
                        id="priority"
                        x-model="filters.priority"
                        @change="applyFilters()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todas las prioridades</option>
                    <option value="low">Baja</option>
                    <option value="medium">Media</option>
                    <option value="high">Alta</option>
                    <option value="critical">Crítica</option>
                </select>
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
                    <option value="security">Seguridad</option>
                    <option value="investigation">Investigación</option>
                    <option value="compliance">Cumplimiento</option>
                    <option value="audit">Auditoría</option>
                    <option value="incident">Incidente</option>
                    <option value="other">Otro</option>
                </select>
            </div>

            <!-- Asignado a -->
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Asignado a</label>
                <select name="assigned_to" 
                        id="assigned_to"
                        x-model="filters.assigned_to"
                        @change="applyFilters()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todos los usuarios</option>
                    <option value="me">Asignado a mí</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Búsqueda -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                <input type="text" 
                       name="search" 
                       id="search"
                       x-model="filters.search"
                       @input.debounce.300ms="applyFilters()"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       placeholder="Título, descripción...">
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pendientes</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['pending'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">En Revisión</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['under_review'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Aprobadas</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['approved'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Críticas</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['critical'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de evidencias -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul role="list" class="divide-y divide-gray-200">
            @forelse($evidences as $evidence)
            <li>
                <a href="{{ route('evidences.show', $evidence) }}" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @php
                                        $priorityColors = [
                                            'low' => 'bg-gray-100 text-gray-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'high' => 'bg-orange-100 text-orange-800',
                                            'critical' => 'bg-red-100 text-red-800'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $priorityColors[$evidence->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($evidence->priority) }}
                                    </span>
                                </div>
                                <div class="ml-4 min-w-0 flex-1">
                                    <p class="text-sm font-medium text-indigo-600 truncate">
                                        {{ $evidence->title }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ Str::limit($evidence->description, 100) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <!-- Estado -->
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'under_review' => 'bg-blue-100 text-blue-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'archived' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pendiente',
                                        'under_review' => 'En Revisión',
                                        'approved' => 'Aprobado',
                                        'rejected' => 'Rechazado',
                                        'archived' => 'Archivado'
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$evidence->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$evidence->status] ?? ucfirst($evidence->status) }}
                                </span>

                                <!-- Archivos adjuntos -->
                                @if($evidence->files_count > 0)
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                        </svg>
                                        {{ $evidence->files_count }}
                                    </div>
                                @endif

                                <!-- Fecha -->
                                <div class="text-sm text-gray-500">
                                    {{ $evidence->created_at->diffForHumans() }}
                                </div>

                                <!-- Flecha -->
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                    Enviado por {{ $evidence->submitter->first_name }} {{ $evidence->submitter->last_name }}
                                </p>
                                @if($evidence->assigned_to)
                                    <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                        Asignado a {{ $evidence->assignee->first_name }} {{ $evidence->assignee->last_name }}
                                    </p>
                                @endif
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 capitalize">
                                    {{ $evidence->category }}
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            @empty
            <li class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">Sin evidencias</h3>
                <p class="mt-1 text-sm text-gray-500">Comienza creando tu primera evidencia.</p>
                <div class="mt-6">
                    <a href="{{ route('evidences.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nueva Evidencia
                    </a>
                </div>
            </li>
            @endforelse
        </ul>

        <!-- Paginación -->
        @if($evidences->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                {{ $evidences->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function evidenceManager() {
    return {
        showFilters: false,
        filters: {
            status: '{{ request('status') }}',
            priority: '{{ request('priority') }}',
            category: '{{ request('category') }}',
            assigned_to: '{{ request('assigned_to') }}',
            search: '{{ request('search') }}'
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
