@extends('layouts.app')

@section('title', 'Milestones - ' . $project->name)

@section('content')
<div class="min-h-full" x-data="milestonesManager()">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center">
                        <div>
                            <div class="flex items-center">
                                <a href="{{ route('projects.show', $project) }}" class="text-gray-500 hover:text-gray-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                </a>
                                <h1 class="ml-3 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                                    Milestones
                                </h1>
                            </div>
                            <dl class="mt-2 flex flex-col sm:ml-8 sm:mt-1 sm:flex-row sm:flex-wrap">
                                <dt class="sr-only">Proyecto</dt>
                                <dd class="text-sm text-gray-500">{{ $project->name }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex space-x-3 md:ml-4 md:mt-0">
                    @can('update', $project)
                        <a href="{{ route('projects.milestones.create', $project) }}" 
                           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Nuevo Milestone
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v8.25c0 .621.504 1.125 1.125 1.125h2.25m0 0V21h4.125c.621 0 1.125-.504 1.125-1.125v-2.5c0-.621-.504-1.125-1.125-1.125H9.375c-.621 0-1.125.504-1.125 1.125v2.5z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ $milestones->count() }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">En Progreso</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ $milestones->where('status', 'in_progress')->count() }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Completados</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ $milestones->where('status', 'completed')->count() }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Atrasados</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ $milestones->filter(fn($m) => $m->is_overdue)->count() }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-4">
                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <label for="search" class="sr-only">Buscar milestones</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   value="{{ request('search') }}"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="Buscar milestones...">
                        </div>
                    </div>

                    <div>
                        <select name="status" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos los estados</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <select name="assigned_to" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos los asignados</option>
                            @foreach($projectMembers as $member)
                                <option value="{{ $member->id }}" {{ request('assigned_to') == $member->id ? 'selected' : '' }}>
                                    {{ $member->first_name }} {{ $member->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filtrar
                    </button>

                    @if(request()->hasAny(['search', 'status', 'assigned_to']))
                        <a href="{{ route('projects.milestones.index', $project) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Milestones List -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($milestones->count() > 0)
                <!-- Timeline View -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Timeline de Milestones</h3>
                            @can('update', $project)
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-500">Reordenar:</span>
                                    <button @click="enableReorder = !enableReorder" 
                                            :class="enableReorder ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                                            class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium transition-colors">
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                        <span x-text="enableReorder ? 'Guardar' : 'Activar'"></span>
                                    </button>
                                </div>
                            @endcan
                        </div>
                    </div>
                    <div class="px-6 py-6">
                        <div class="flow-root">
                            <ul role="list" class="-mb-8" x-ref="milestonesList">
                                @foreach($milestones as $milestone)
                                    <li data-milestone-id="{{ $milestone->id }}" 
                                        :class="enableReorder ? 'cursor-move' : ''"
                                        class="milestone-item">
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                        {{ $milestone->status === 'completed' ? 'bg-green-500' : 
                                                           ($milestone->status === 'in_progress' ? 'bg-blue-500' : 
                                                           ($milestone->is_overdue ? 'bg-red-500' : 'bg-gray-400')) }}">
                                                        @if($milestone->status === 'completed')
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        @else
                                                            <span class="text-white text-sm font-medium">{{ $milestone->order }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center space-x-3 mb-2">
                                                            <h4 class="text-sm font-medium text-gray-900">
                                                                <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}" 
                                                                   class="hover:text-indigo-600">
                                                                    {{ $milestone->name }}
                                                                </a>
                                                            </h4>
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $milestone->status_badge_color }}">
                                                                {{ $milestone->status_display_name }}
                                                            </span>
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $milestone->priority_badge_color }}">
                                                                {{ $milestone->priority_display_name }}
                                                            </span>
                                                        </div>
                                                        @if($milestone->description)
                                                            <p class="text-sm text-gray-500 mb-2">{{ Str::limit($milestone->description, 150) }}</p>
                                                        @endif
                                                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                            @if($milestone->assignedUser)
                                                                <span class="flex items-center">
                                                                    <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                                    </svg>
                                                                    {{ $milestone->assignedUser->full_name }}
                                                                </span>
                                                            @endif
                                                            @if($milestone->due_date)
                                                                <span class="flex items-center {{ $milestone->is_overdue ? 'text-red-600' : '' }}">
                                                                    <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5m-18 0h18" />
                                                                    </svg>
                                                                    {{ $milestone->due_date->format('d/m/Y') }}
                                                                </span>
                                                            @endif
                                                            @if($milestone->estimated_hours)
                                                                <span class="flex items-center">
                                                                    <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    {{ $milestone->estimated_hours }}h estimadas
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <!-- Progress Bar -->
                                                        <div class="mt-3">
                                                            <div class="flex items-center justify-between text-xs">
                                                                <span class="text-gray-600">Progreso</span>
                                                                <span class="font-medium">{{ $milestone->progress_percentage }}%</span>
                                                            </div>
                                                            <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                                                                     style="width: {{ $milestone->progress_percentage }}%"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-sm text-gray-500 flex items-center space-x-2">
                                                        @can('update', $project)
                                                            <div class="flex items-center space-x-1">
                                                                <a href="{{ route('projects.milestones.edit', [$project, $milestone]) }}" 
                                                                   class="text-indigo-600 hover:text-indigo-900">
                                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                                    </svg>
                                                                </a>
                                                                @if($milestone->status !== 'completed')
                                                                    <button @click="markCompleted({{ $milestone->id }})" 
                                                                            class="text-green-600 hover:text-green-900">
                                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @endcan
                                                        <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}" 
                                                           class="text-indigo-600 hover:text-indigo-900">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v8.25c0 .621.504 1.125 1.125 1.125h2.25m0 0V21h4.125c.621 0 1.125-.504 1.125-1.125v-2.5c0-.621-.504-1.125-1.125-1.125H9.375c-.621 0-1.125.504-1.125 1.125v2.5z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay milestones</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza creando el primer milestone para este proyecto.</p>
                    @can('update', $project)
                        <div class="mt-6">
                            <a href="{{ route('projects.milestones.create', $project) }}" 
                               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Crear Milestone
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function milestonesManager() {
    return {
        enableReorder: false,
        sortable: null,

        init() {
            this.$watch('enableReorder', (value) => {
                if (value) {
                    this.initSortable();
                } else {
                    this.destroySortable();
                    this.saveOrder();
                }
            });
        },

        initSortable() {
            const list = this.$refs.milestonesList;
            this.sortable = Sortable.create(list, {
                animation: 150,
                ghostClass: 'opacity-50',
                chosenClass: 'bg-gray-50',
                handle: '.milestone-item',
            });
        },

        destroySortable() {
            if (this.sortable) {
                this.sortable.destroy();
                this.sortable = null;
            }
        },

        async saveOrder() {
            const items = this.$refs.milestonesList.querySelectorAll('.milestone-item');
            const milestoneIds = Array.from(items).map(item => item.dataset.milestoneId);

            try {
                const response = await fetch('{{ route("projects.milestones.reorder", $project) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ milestones: milestoneIds })
                });

                if (!response.ok) {
                    throw new Error('Error al guardar el orden');
                }
            } catch (error) {
                console.error('Error saving order:', error);
                alert('Error al guardar el orden de los milestones');
            }
        },

        async markCompleted(milestoneId) {
            if (!confirm('¿Marcar este milestone como completado?')) {
                return;
            }

            try {
                const response = await fetch(`{{ route("projects.milestones.complete", [$project, "MILESTONE_ID"]) }}`.replace('MILESTONE_ID', milestoneId), {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al marcar como completado');
                }
            } catch (error) {
                console.error('Error marking completed:', error);
                alert('Error al marcar como completado');
            }
        }
    }
}
</script>
@endpush
@endsection
