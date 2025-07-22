@extends('layouts.app')

@section('title', 'Registro de Tiempo')

@push('styles')
<style>
    .time-log-row:hover {
        background-color: #f9fafb;
    }
    .bulk-actions {
        transform: translateY(-100%);
        transition: transform 0.2s ease-in-out;
    }
    .bulk-actions.show {
        transform: translateY(0);
    }
</style>
@endpush

@section('content')
<div class="min-h-full" x-data="timeLogsManager()">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center">
                        <div>
                            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                                Registro de Tiempo
                            </h1>
                            <dl class="mt-2 flex flex-col sm:mt-1 sm:flex-row sm:flex-wrap">
                                <dt class="sr-only">Total de horas</dt>
                                <dd class="text-sm text-gray-500">
                                    Total: {{ number_format($stats['total_hours'], 1) }} horas | 
                                    Facturables: {{ number_format($stats['billable_hours'], 1) }} horas |
                                    Monto: ${{ number_format($stats['total_amount'], 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex space-x-3 md:ml-4 md:mt-0">
                    <a href="{{ route('time-logs.export') }}" 
                       class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Exportar
                    </a>
                    <a href="{{ route('time-logs.create') }}" 
                       class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Registrar Tiempo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Horas</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_hours'], 1) }}h</dd>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Facturables</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['billable_hours'], 1) }}h</dd>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H4.5m-1.5 0v.375c0 .621-.504 1.125-1.125 1.125m1.5 0h.375c.621 0 1.125.504 1.125 1.125v.75c0 .621-.504 1.125-1.125 1.125H3.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Monto Total</dt>
                                        <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['total_amount'], 2) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3-6h.008v.008H15.75V12zm0 3h.008v.008H15.75V15zm0 3h.008v.008H15.75V18zM6.75 6.75h.008v.008H6.75V6.75zm0 3h.008v.008H6.75V9.75zm0 3h.008v.008H6.75V12.75zm0 3h.008v.008H6.75V15.75zm0 3h.008v.008H6.75V18.75zM3.75 6.75h.008v.008H3.75V6.75zm0 3h.008v.008H3.75V9.75zm0 3h.008v.008H3.75V12.75zm0 3h.008v.008H3.75V15.75zm0 3h.008v.008H3.75V18.75z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pendientes</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ $stats['pending_approval'] }}</dd>
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
                <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <!-- Project Filter -->
                    <div class="sm:col-span-2">
                        <label for="project_id" class="block text-sm font-medium text-gray-700">Proyecto</label>
                        <select name="project_id" 
                                id="project_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos los proyectos</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div class="sm:col-span-2">
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Usuario</label>
                        <select name="user_id" 
                                id="user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos los usuarios</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Desde</label>
                        <input type="date" 
                               name="date_from" 
                               id="date_from" 
                               value="{{ request('date_from') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Hasta</label>
                        <input type="date" 
                               name="date_to" 
                               id="date_to" 
                               value="{{ request('date_to') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Filtrar
                        </button>
                        @if(request()->hasAny(['project_id', 'user_id', 'date_from', 'date_to', 'approved', 'billable']))
                            <a href="{{ route('time-logs.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Additional Filters -->
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-gray-700">Filtros rápidos:</span>
                    <a href="{{ request()->fullUrlWithQuery(['approved' => 'no']) }}" 
                       class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ request('approved') === 'no' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                        Pendientes de aprobación
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['billable' => 'yes']) }}" 
                       class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ request('billable') === 'yes' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                        Solo facturables
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['date_from' => now()->startOfWeek()->format('Y-m-d'), 'date_to' => now()->endOfWeek()->format('Y-m-d')]) }}" 
                       class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                        Esta semana
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['date_from' => now()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->endOfMonth()->format('Y-m-d')]) }}" 
                       class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                        Este mes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div x-show="selectedLogs.length > 0" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-indigo-600 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="py-3 flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-sm font-medium text-white">
                        <span x-text="selectedLogs.length"></span> registros seleccionados
                    </span>
                </div>
                <div class="flex items-center space-x-3">
                    @can('approve', \App\Models\TimeLog::class)
                        <button @click="bulkApprove()" 
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Aprobar Seleccionados
                        </button>
                    @endcan
                    <button @click="clearSelection()" 
                            class="inline-flex items-center px-3 py-1 border border-white text-sm font-medium rounded-md text-white hover:bg-indigo-700">
                        Limpiar Selección
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Logs Table -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($timeLogs->count() > 0)
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="min-w-full divide-y divide-gray-200">
                        <div class="bg-gray-50 px-6 py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           @change="toggleAll($event.target.checked)"
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label class="ml-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registros de Tiempo ({{ $timeLogs->total() }})
                                    </label>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Mostrando {{ $timeLogs->firstItem() }}-{{ $timeLogs->lastItem() }} de {{ $timeLogs->total() }}
                                </div>
                            </div>
                        </div>
                        <div class="bg-white divide-y divide-gray-200">
                            @foreach($timeLogs as $timeLog)
                                <div class="time-log-row px-6 py-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <input type="checkbox" 
                                                   value="{{ $timeLog->id }}"
                                                   x-model="selectedLogs"
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-3 mb-1">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ $timeLog->task_description }}
                                                    </p>
                                                    @if(!$timeLog->is_approved)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                            Pendiente
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Aprobado
                                                        </span>
                                                    @endif
                                                    @if($timeLog->is_billable)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            Facturable
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                                    <span class="flex items-center">
                                                        <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                        </svg>
                                                        {{ $timeLog->user->full_name }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                                        </svg>
                                                        {{ $timeLog->project->name }}
                                                    </span>
                                                    @if($timeLog->milestone)
                                                        <span class="flex items-center">
                                                            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v8.25c0 .621.504 1.125 1.125 1.125h2.25m0 0V21h4.125c.621 0 1.125-.504 1.125-1.125v-2.5c0-.621-.504-1.125-1.125-1.125H9.375c-.621 0-1.125.504-1.125 1.125v2.5z" />
                                                            </svg>
                                                            {{ $timeLog->milestone->name }}
                                                        </span>
                                                    @endif
                                                    <span class="flex items-center">
                                                        <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5m-18 0h18" />
                                                        </svg>
                                                        {{ $timeLog->date->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <div class="text-right">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $timeLog->formatted_duration }}
                                                </div>
                                                @if($timeLog->is_billable && $timeLog->hourly_rate > 0)
                                                    <div class="text-sm text-gray-500">
                                                        ${{ number_format($timeLog->total_amount, 2) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center space-x-2">
                                                @can('approve', $timeLog)
                                                    @if(!$timeLog->is_approved)
                                                        <button @click="approveTimeLog({{ $timeLog->id }})" 
                                                                class="text-green-600 hover:text-green-900">
                                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                @endcan

                                                @can('update', $timeLog)
                                                    <a href="{{ route('time-logs.edit', $timeLog) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                        </svg>
                                                    </a>
                                                @endcan

                                                <a href="{{ route('time-logs.show', $timeLog) }}" 
                                                   class="text-gray-400 hover:text-gray-600">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    @if($timeLog->notes)
                                        <div class="mt-2 ml-8">
                                            <p class="text-sm text-gray-600 italic">{{ $timeLog->notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $timeLogs->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay registros de tiempo</h3>
                    <p class="mt-1 text-sm text-gray-500">No se encontraron registros con los filtros aplicados.</p>
                    <div class="mt-6">
                        <a href="{{ route('time-logs.create') }}" 
                           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Registrar Primer Tiempo
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function timeLogsManager() {
    return {
        selectedLogs: [],

        toggleAll(checked) {
            if (checked) {
                this.selectedLogs = Array.from(document.querySelectorAll('input[type="checkbox"][value]')).map(cb => cb.value);
            } else {
                this.selectedLogs = [];
            }
        },

        clearSelection() {
            this.selectedLogs = [];
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        },

        async approveTimeLog(timeLogId) {
            if (!confirm('¿Aprobar este registro de tiempo?')) {
                return;
            }

            try {
                const response = await fetch(`{{ route("time-logs.approve", "TIME_LOG_ID") }}`.replace('TIME_LOG_ID', timeLogId), {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al aprobar el registro');
                }
            } catch (error) {
                console.error('Error approving time log:', error);
                alert('Error al aprobar el registro');
            }
        },

        async bulkApprove() {
            if (this.selectedLogs.length === 0) {
                alert('Selecciona al menos un registro para aprobar');
                return;
            }

            if (!confirm(`¿Aprobar ${this.selectedLogs.length} registros seleccionados?`)) {
                return;
            }

            try {
                const response = await fetch('{{ route("time-logs.bulk-approve") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        time_log_ids: this.selectedLogs
                    })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al aprobar los registros');
                }
            } catch (error) {
                console.error('Error bulk approving:', error);
                alert('Error al aprobar los registros');
            }
        }
    }
}
</script>
@endpush
@endsection
