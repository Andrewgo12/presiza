@extends('layouts.app')

@section('title', $project->name)

@push('styles')
<style>
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .progress-ring {
        transform: rotate(-90deg);
    }
    .progress-ring-circle {
        transition: stroke-dasharray 0.35s;
        transform-origin: 50% 50%;
    }
</style>
@endpush

@section('content')
<div class="min-h-full" x-data="projectDetails()">
    <!-- Project Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center">
                        <div>
                            <div class="flex items-center">
                                <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                </a>
                                <h1 class="ml-3 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                                    {{ $project->name }}
                                </h1>
                                <div class="ml-4 flex items-center space-x-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $project->status_badge_color }}">
                                        {{ $project->status_display_name }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $project->priority_badge_color }}">
                                        {{ $project->priority_display_name }}
                                    </span>
                                    @if($project->is_overdue)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                            </svg>
                                            Atrasado
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <dl class="mt-2 flex flex-col sm:ml-8 sm:mt-1 sm:flex-row sm:flex-wrap">
                                <dt class="sr-only">Cliente</dt>
                                <dd class="flex items-center text-sm text-gray-500">
                                    <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m2.25-18v18m13.5-18v18m2.25-18v18M6.75 9h.008v.008H6.75V9zm0 3h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm3-6h.008v.008H9.75V9zm0 3h.008v.008H9.75V12zm0 3h.008v.008H9.75V15z" />
                                    </svg>
                                    {{ $project->client_name ?? 'Cliente no especificado' }}
                                </dd>
                                <dt class="sr-only">Gerente</dt>
                                <dd class="mt-1 flex items-center text-sm text-gray-500 sm:mr-6 sm:mt-0">
                                    <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                    {{ $project->manager->full_name ?? 'Sin asignar' }}
                                </dd>
                                @if($project->deadline)
                                    <dt class="sr-only">Deadline</dt>
                                    <dd class="mt-1 flex items-center text-sm {{ $project->is_overdue ? 'text-red-600' : 'text-gray-500' }} sm:mr-6 sm:mt-0">
                                        <svg class="mr-1.5 h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5m-18 0h18" />
                                        </svg>
                                        Deadline: {{ $project->deadline->format('d/m/Y') }}
                                        @if($project->days_remaining !== null)
                                            ({{ $project->days_remaining > 0 ? $project->days_remaining . ' días restantes' : abs($project->days_remaining) . ' días de retraso' }})
                                        @endif
                                    </dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex space-x-3 md:ml-4 md:mt-0">
                    @can('update', $project)
                        <button type="button"
                                @click="showAddMemberModal = true"
                                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-3-3h1.5a3 3 0 013 3v6a3 3 0 01-3 3H9a3 3 0 01-3-3v-6a3 3 0 013-3H15z" />
                            </svg>
                            Agregar Miembro
                        </button>
                        <a href="{{ route('projects.edit', $project) }}"
                           class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Editar
                        </a>
                    @endcan
                    <div class="relative" x-data="{ open: false }">
                        <button type="button"
                                @click="open = !open"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                            </svg>
                            Acciones
                        </button>
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <a href="{{ route('projects.milestones.index', $project) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ver Milestones</a>
                            <a href="{{ route('time-logs.index', ['project_id' => $project->id]) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Registros de Tiempo</a>
                            <a href="{{ route('evidences.index', ['project_id' => $project->id]) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Evidencias</a>
                            @if($project->repository_url)
                                <a href="{{ $project->repository_url }}"
                                   target="_blank"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ver Repositorio</a>
                            @endif
                            @if($project->documentation_url)
                                <a href="{{ $project->documentation_url }}"
                                   target="_blank"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Documentación</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Progress Circle -->
                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="relative w-16 h-16">
                                        <svg class="w-16 h-16 progress-ring" viewBox="0 0 42 42">
                                            <circle class="progress-ring-circle stroke-gray-200"
                                                    stroke-width="3"
                                                    fill="transparent"
                                                    r="19"
                                                    cx="21"
                                                    cy="21"/>
                                            <circle class="progress-ring-circle stroke-indigo-600"
                                                    stroke-width="3"
                                                    fill="transparent"
                                                    r="19"
                                                    cx="21"
                                                    cy="21"
                                                    stroke-dasharray="{{ 119.38 * ($project->progress_percentage / 100) }} 119.38"/>
                                        </svg>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-sm font-semibold text-gray-900">{{ $project->progress_percentage }}%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Progreso General</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ $project->completion_rate }}% Completado</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Team Members -->
                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Equipo</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total_members'] }} Miembros</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Milestones -->
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
                                        <dt class="text-sm font-medium text-gray-500 truncate">Milestones</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ $stats['completed_milestones'] }}/{{ $stats['total_milestones'] }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget -->
                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Presupuesto</dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            @if($project->budget)
                                                ${{ number_format($project->budget, 2) }}
                                            @else
                                                No definido
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'overview'"
                            :class="activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Resumen
                    </button>
                    <button @click="activeTab = 'milestones'"
                            :class="activeTab === 'milestones' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Milestones
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $stats['total_milestones'] }}
                        </span>
                    </button>
                    <button @click="activeTab = 'team'"
                            :class="activeTab === 'team' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Equipo
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $stats['total_members'] }}
                        </span>
                    </button>
                    <button @click="activeTab = 'activity'"
                            :class="activeTab === 'activity' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Actividad
                    </button>
                    <button @click="activeTab = 'files'"
                            :class="activeTab === 'files' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Archivos
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-8">
                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'" class="tab-content">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <!-- Project Details -->
                        <div class="lg:col-span-2">
                            <div class="bg-white shadow rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">Detalles del Proyecto</h3>
                                </div>
                                <div class="px-6 py-4">
                                    @if($project->description)
                                        <div class="mb-6">
                                            <h4 class="text-sm font-medium text-gray-900 mb-2">Descripción</h4>
                                            <p class="text-sm text-gray-700 leading-relaxed">{{ $project->description }}</p>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 mb-3">Información General</h4>
                                            <dl class="space-y-3">
                                                <div>
                                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Estado</dt>
                                                    <dd class="mt-1">
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $project->status_badge_color }}">
                                                            {{ $project->status_display_name }}
                                                        </span>
                                                    </dd>
                                                </div>
                                                <div>
                                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Prioridad</dt>
                                                    <dd class="mt-1">
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $project->priority_badge_color }}">
                                                            {{ $project->priority_display_name }}
                                                        </span>
                                                    </dd>
                                                </div>
                                                @if($project->client_name)
                                                    <div>
                                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cliente</dt>
                                                        <dd class="mt-1 text-sm text-gray-900">{{ $project->client_name }}</dd>
                                                    </div>
                                                @endif
                                                @if($project->budget)
                                                    <div>
                                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Presupuesto</dt>
                                                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($project->budget, 2) }}</dd>
                                                    </div>
                                                @endif
                                            </dl>
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 mb-3">Cronograma</h4>
                                            <dl class="space-y-3">
                                                @if($project->start_date)
                                                    <div>
                                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha de Inicio</dt>
                                                        <dd class="mt-1 text-sm text-gray-900">{{ $project->start_date->format('d/m/Y') }}</dd>
                                                    </div>
                                                @endif
                                                @if($project->end_date)
                                                    <div>
                                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha de Fin</dt>
                                                        <dd class="mt-1 text-sm text-gray-900">{{ $project->end_date->format('d/m/Y') }}</dd>
                                                    </div>
                                                @endif
                                                @if($project->deadline)
                                                    <div>
                                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Deadline</dt>
                                                        <dd class="mt-1 text-sm {{ $project->is_overdue ? 'text-red-600' : 'text-gray-900' }}">
                                                            {{ $project->deadline->format('d/m/Y') }}
                                                            @if($project->days_remaining !== null)
                                                                <span class="text-xs text-gray-500">
                                                                    ({{ $project->days_remaining > 0 ? $project->days_remaining . ' días restantes' : abs($project->days_remaining) . ' días de retraso' }})
                                                                </span>
                                                            @endif
                                                        </dd>
                                                    </div>
                                                @endif
                                                <div>
                                                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Creado</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $project->created_at->format('d/m/Y H:i') }}</dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>

                                    @if($project->repository_url || $project->documentation_url)
                                        <div class="mt-6 pt-6 border-t border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-900 mb-3">Recursos</h4>
                                            <div class="flex space-x-4">
                                                @if($project->repository_url)
                                                    <a href="{{ $project->repository_url }}"
                                                       target="_blank"
                                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                        Repositorio
                                                    </a>
                                                @endif
                                                @if($project->documentation_url)
                                                    <a href="{{ $project->documentation_url }}"
                                                       target="_blank"
                                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                        </svg>
                                                        Documentación
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="space-y-6">
                            <!-- Progress Chart -->
                            <div class="bg-white shadow rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">Progreso por Milestones</h3>
                                </div>
                                <div class="px-6 py-4">
                                    <div class="h-64">
                                        <canvas id="milestoneChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Activity -->
                            <div class="bg-white shadow rounded-lg">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">Actividad Reciente</h3>
                                </div>
                                <div class="px-6 py-4">
                                    <div class="flow-root">
                                        <ul role="list" class="-mb-8">
                                            @forelse($recentActivity->take(5) as $activity)
                                                <li>
                                                    <div class="relative pb-8">
                                                        @if(!$loop->last)
                                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                        @endif
                                                        <div class="relative flex space-x-3">
                                                            <div>
                                                                <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                                    </svg>
                                                                </span>
                                                            </div>
                                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                                <div>
                                                                    <p class="text-sm text-gray-500">
                                                                        {{ class_basename($activity) === 'Evidence' ? 'Nueva evidencia: ' . $activity->title : 'Actividad registrada' }}
                                                                    </p>
                                                                </div>
                                                                <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                                    <time datetime="{{ $activity->created_at->toISOString() }}">
                                                                        {{ $activity->created_at->diffForHumans() }}
                                                                    </time>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="text-sm text-gray-500 text-center py-4">
                                                    No hay actividad reciente
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Milestones Tab -->
                <div x-show="activeTab === 'milestones'" class="tab-content">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Milestones del Proyecto</h3>
                            @can('update', $project)
                                <a href="{{ route('projects.milestones.create', $project) }}"
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Nuevo Milestone
                                </a>
                            @endcan
                        </div>
                        <div class="px-6 py-4">
                            @if($project->milestones->count() > 0)
                                <div class="space-y-4">
                                    @foreach($project->milestones->sortBy('order') as $milestone)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-3">
                                                        <h4 class="text-sm font-medium text-gray-900">{{ $milestone->name }}</h4>
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $milestone->status_badge_color }}">
                                                            {{ $milestone->status_display_name }}
                                                        </span>
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $milestone->priority_badge_color }}">
                                                            {{ $milestone->priority_display_name }}
                                                        </span>
                                                    </div>
                                                    @if($milestone->description)
                                                        <p class="mt-1 text-sm text-gray-500">{{ Str::limit($milestone->description, 100) }}</p>
                                                    @endif
                                                    <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
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
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-3">
                                                    <div class="text-right">
                                                        <div class="text-sm font-medium text-gray-900">{{ $milestone->progress_percentage }}%</div>
                                                        <div class="w-20 bg-gray-200 rounded-full h-2 mt-1">
                                                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                                                 style="width: {{ $milestone->progress_percentage }}%"></div>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('projects.milestones.show', [$project, $milestone]) }}"
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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

                <!-- Team Tab -->
                <div x-show="activeTab === 'team'" class="tab-content">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Equipo del Proyecto</h3>
                            @can('update', $project)
                                <button @click="showAddMemberModal = true"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-3-3h1.5a3 3 0 013 3v6a3 3 0 01-3 3H9a3 3 0 01-3-3v-6a3 3 0 013-3H15z" />
                                    </svg>
                                    Agregar Miembro
                                </button>
                            @endcan
                        </div>
                        <div class="px-6 py-4">
                            @if($project->members->count() > 0)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($project->members as $member)
                                        <div class="relative rounded-lg border border-gray-300 bg-white p-6 shadow-sm hover:border-gray-400">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <img class="h-10 w-10 rounded-full"
                                                         src="{{ $member->avatar ? Storage::url($member->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($member->first_name . ' ' . $member->last_name) . '&color=7F9CF5&background=EBF4FF' }}"
                                                         alt="{{ $member->full_name }}">
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ $member->full_name }}</p>
                                                    <p class="text-sm text-gray-500">{{ ucfirst($member->pivot->role ?? 'developer') }}</p>
                                                    @if($member->pivot->hourly_rate > 0)
                                                        <p class="text-xs text-gray-400">${{ number_format($member->pivot->hourly_rate, 2) }}/hora</p>
                                                    @endif
                                                </div>
                                                @can('update', $project)
                                                    @if($project->project_manager_id !== $member->id)
                                                        <div class="flex-shrink-0">
                                                            <button @click="removeMember({{ $member->id }})"
                                                                    class="text-red-400 hover:text-red-600">
                                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @endif
                                                @endcan
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay miembros</h3>
                                    <p class="mt-1 text-sm text-gray-500">Agrega miembros al equipo del proyecto.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Activity Tab -->
                <div x-show="activeTab === 'activity'" class="tab-content">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Actividad del Proyecto</h3>
                        </div>
                        <div class="px-6 py-4">
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    @forelse($recentActivity as $activity)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                        <div>
                                                            <p class="text-sm text-gray-500">
                                                                @if(class_basename($activity) === 'Evidence')
                                                                    <span class="font-medium text-gray-900">{{ $activity->submitter->full_name ?? 'Usuario' }}</span>
                                                                    subió nueva evidencia: <span class="font-medium">{{ $activity->title }}</span>
                                                                @elseif(class_basename($activity) === 'TimeLog')
                                                                    <span class="font-medium text-gray-900">{{ $activity->user->full_name ?? 'Usuario' }}</span>
                                                                    registró {{ $activity->hours }} horas: <span class="font-medium">{{ $activity->task_description }}</span>
                                                                @else
                                                                    Actividad registrada
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                            <time datetime="{{ $activity->created_at->toISOString() }}">
                                                                {{ $activity->created_at->diffForHumans() }}
                                                            </time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-sm text-gray-500 text-center py-8">
                                            No hay actividad registrada para este proyecto
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files Tab -->
                <div x-show="activeTab === 'files'" class="tab-content">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Archivos del Proyecto</h3>
                            <a href="{{ route('files.create', ['project_id' => $project->id]) }}"
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Subir Archivo
                            </a>
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-sm text-gray-500 mb-4">
                                Los archivos relacionados con este proyecto aparecerán aquí.
                                <a href="{{ route('files.index', ['project_id' => $project->id]) }}" class="text-indigo-600 hover:text-indigo-500">
                                    Ver todos los archivos →
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div x-show="showAddMemberModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAddMemberModal = false"></div>

            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <form @submit.prevent="addMember()">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-3-3h1.5a3 3 0 013 3v6a3 3 0 01-3 3H9a3 3 0 01-3-3v-6a3 3 0 013-3H15z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Agregar Miembro al Proyecto</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Selecciona un usuario y asigna su rol en el proyecto.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Usuario</label>
                            <select x-model="newMember.user_id"
                                    id="user_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Seleccionar usuario...</option>
                                <!-- Users will be loaded dynamically -->
                            </select>
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
                            <select x-model="newMember.role"
                                    id="role"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="developer">Desarrollador</option>
                                <option value="senior_developer">Desarrollador Senior</option>
                                <option value="designer">Diseñador</option>
                                <option value="tester">Tester</option>
                                <option value="analyst">Analista</option>
                            </select>
                        </div>

                        <div>
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700">Tarifa por Hora (USD)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number"
                                       x-model="newMember.hourly_rate"
                                       id="hourly_rate"
                                       step="0.01"
                                       min="0"
                                       class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button type="submit"
                                :disabled="loading"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2 disabled:opacity-50">
                            <span x-show="!loading">Agregar</span>
                            <span x-show="loading" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Agregando...
                            </span>
                        </button>
                        <button type="button"
                                @click="showAddMemberModal = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function projectDetails() {
    return {
        activeTab: 'overview',
        showAddMemberModal: false,
        loading: false,
        newMember: {
            user_id: '',
            role: 'developer',
            hourly_rate: 0
        },

        init() {
            this.initChart();
            this.loadAvailableUsers();
        },

        initChart() {
            const ctx = document.getElementById('milestoneChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completados', 'En Progreso', 'Pendientes'],
                        datasets: [{
                            data: [
                                {{ $stats['completed_milestones'] }},
                                {{ $project->milestones->where('status', 'in_progress')->count() }},
                                {{ $project->milestones->where('status', 'pending')->count() }}
                            ],
                            backgroundColor: [
                                '#10B981',
                                '#3B82F6',
                                '#6B7280'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
        },

        async loadAvailableUsers() {
            try {
                const response = await fetch('/api/users/available-for-project/{{ $project->id }}');
                const users = await response.json();

                const select = document.getElementById('user_id');
                select.innerHTML = '<option value="">Seleccionar usuario...</option>';

                users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.first_name} ${user.last_name} (${user.role})`;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading users:', error);
            }
        },

        async addMember() {
            if (!this.newMember.user_id || !this.newMember.role) {
                return;
            }

            this.loading = true;

            try {
                const formData = new FormData();
                formData.append('user_id', this.newMember.user_id);
                formData.append('role', this.newMember.role);
                formData.append('hourly_rate', this.newMember.hourly_rate || 0);

                const response = await fetch('{{ route("projects.members.add", $project) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al agregar miembro');
                }
            } catch (error) {
                console.error('Error adding member:', error);
                alert('Error al agregar miembro');
            } finally {
                this.loading = false;
            }
        },

        async removeMember(userId) {
            if (!confirm('¿Estás seguro de que quieres remover este miembro del proyecto?')) {
                return;
            }

            try {
                const response = await fetch(`{{ route("projects.members.remove", [$project, "USER_ID"]) }}`.replace('USER_ID', userId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al remover miembro');
                }
            } catch (error) {
                console.error('Error removing member:', error);
                alert('Error al remover miembro');
            }
        }
    }
}
</script>
@endpush
@endsection