@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Bienvenido de vuelta, {{ auth()->user()->first_name }}. Aquí tienes un resumen de tu actividad.
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('files.create') }}" 
               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Subir Archivo
            </a>
        </div>
    </div>

    <!-- Métricas principales -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-6">
        <!-- Total de archivos -->
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total de Archivos</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['total_files'] ?? 0 }}</dd>
            <div class="mt-2 flex items-center text-sm">
                @if(($stats['files_change'] ?? 0) >= 0)
                    <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                    </svg>
                    <span class="text-green-600 ml-1">+{{ $stats['files_change'] ?? 0 }}%</span>
                @else
                    <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.511l-5.511-3.182" />
                    </svg>
                    <span class="text-red-600 ml-1">{{ $stats['files_change'] ?? 0 }}%</span>
                @endif
                <span class="text-gray-500 ml-1">vs mes anterior</span>
            </div>
        </div>

        <!-- Evidencias pendientes -->
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Evidencias Pendientes</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['pending_evidences'] ?? 0 }}</dd>
            <div class="mt-2 flex items-center text-sm">
                <svg class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-500 ml-1">Requieren revisión</span>
            </div>
        </div>

        <!-- Grupos activos -->
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Grupos Activos</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['active_groups'] ?? 0 }}</dd>
            <div class="mt-2 flex items-center text-sm">
                <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                <span class="text-gray-500 ml-1">Participando</span>
            </div>
        </div>

        <!-- Mensajes no leídos -->
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Mensajes No Leídos</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['unread_messages'] ?? 0 }}</dd>
            <div class="mt-2 flex items-center text-sm">
                <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
                <span class="text-gray-500 ml-1">Nuevos</span>
            </div>
        </div>

        <!-- Mis Proyectos -->
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Mis Proyectos</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['my_projects'] ?? 0 }}</dd>
            <div class="mt-2 flex items-center text-sm">
                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span class="text-gray-500 ml-1">Activos</span>
            </div>
        </div>

        <!-- Milestones Asignados -->
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Milestones Asignados</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $stats['my_milestones'] ?? 0 }}</dd>
            <div class="mt-2 flex items-center text-sm">
                <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v8.25c0 .621.504 1.125 1.125 1.125h2.25m0 0V21h4.125c.621 0 1.125-.504 1.125-1.125v-2.5c0-.621-.504-1.125-1.125-1.125H9.375c-.621 0-1.125.504-1.125 1.125v2.5z" />
                </svg>
                <span class="text-gray-500 ml-1">Pendientes</span>
            </div>
        </div>

        <!-- Horas Esta Semana -->
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Horas Esta Semana</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['hours_this_week'] ?? 0, 1) }}</dd>
            <div class="mt-2 flex items-center text-sm">
                <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-500 ml-1">Registradas</span>
            </div>
        </div>
    </div>

    <!-- Gráficos y actividad reciente -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Project Progress Chart -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Progreso de Proyectos</h3>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="projectProgressChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Time Tracking Chart -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Horas Trabajadas (Últimos 7 días)</h3>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="timeTrackingChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Analytics -->
    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Ingresos por Proyecto</h3>
                    <div class="flex space-x-2">
                        <button class="text-sm text-gray-500 hover:text-gray-700" onclick="updateRevenueChart('week')">7D</button>
                        <button class="text-sm text-gray-500 hover:text-gray-700" onclick="updateRevenueChart('month')">30D</button>
                        <button class="text-sm text-indigo-600 font-medium" onclick="updateRevenueChart('quarter')">3M</button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Projects -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Proyectos Más Activos</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($topProjects ?? [] as $project)
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $project->name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $project->hours_this_week ?? 0 }}h esta semana
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full"
                                         style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                                </div>
                                <span class="text-sm text-gray-500">{{ $project->progress_percentage ?? 0 }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Rendimiento del Equipo</h3>
                <a href="{{ route('analytics.team') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                    Ver detalles →
                </a>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Team Productivity Chart -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Productividad por Miembro</h4>
                    <div class="h-64">
                        <canvas id="teamProductivityChart"></canvas>
                    </div>
                </div>

                <!-- Project Distribution -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Distribución de Proyectos</h4>
                    <div class="h-64">
                        <canvas id="projectDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Gráfico de actividad -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Actividad de Archivos (Últimos 7 días)</h3>
                <div class="h-64">
                    <canvas id="filesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Actividad reciente -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Actividad Reciente</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @forelse($recent_activities ?? [] as $activity)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full {{ $activity['color'] ?? 'bg-gray-400' }} flex items-center justify-center ring-8 ring-white">
                                            {!! $activity['icon'] ?? '<svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/></svg>' !!}
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                {{ $activity['description'] ?? 'Actividad sin descripción' }}
                                            </p>
                                        </div>
                                        <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                            <time datetime="{{ $activity['datetime'] ?? now() }}">
                                                {{ $activity['time'] ?? 'Hace un momento' }}
                                            </time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h4.125M8.25 8.25V6.108" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">Sin actividad reciente</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza subiendo archivos o creando evidencias.</p>
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Archivos recientes -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Archivos Recientes</h3>
                <a href="{{ route('files.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Ver todos
                </a>
            </div>
        </div>
        <div class="px-6 py-4">
            @if(isset($recent_files) && count($recent_files) > 0)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($recent_files as $file)
                    <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        <div class="flex-shrink-0">
                            @if(str_starts_with($file['mime_type'] ?? '', 'image/'))
                                <img class="h-10 w-10 rounded object-cover" src="{{ $file['thumbnail_url'] ?? $file['url'] }}" alt="{{ $file['original_name'] }}">
                            @else
                                <div class="h-10 w-10 rounded bg-gray-100 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('files.show', $file['id']) }}" class="focus:outline-none">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $file['original_name'] }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ $file['size_formatted'] ?? '0 KB' }}</p>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 18H3.75c-.621 0-1.125-.504-1.125-1.125V1.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v4.5c0 .621.504 1.125 1.125 1.125h4.125c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125H16.5a1.125 1.125 0 01-1.125-1.125v-1.5a1.125 1.125 0 00-1.125-1.125H12m6.75-7.5H12" />
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
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Gráfico de actividad de archivos
const ctx = document.getElementById('filesChart').getContext('2d');
const filesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chart_data['labels'] ?? ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']),
        datasets: [{
            label: 'Archivos subidos',
            data: @json($chart_data['data'] ?? [12, 19, 3, 5, 2, 3, 9]),
            borderColor: 'rgb(79, 70, 229)',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
@endpush
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Project Progress Chart
    const projectProgressCtx = document.getElementById('projectProgressChart');
    if (projectProgressCtx) {
        new Chart(projectProgressCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completados', 'En Progreso', 'En Pausa', 'Planificación'],
                datasets: [{
                    data: [
                        {{ $stats['completed_projects'] ?? 0 }},
                        {{ $stats['in_progress_projects'] ?? 0 }},
                        {{ $stats['on_hold_projects'] ?? 0 }},
                        {{ $stats['planning_projects'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#10B981',
                        '#3B82F6',
                        '#F59E0B',
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

    // Time Tracking Chart
    const timeTrackingCtx = document.getElementById('timeTrackingChart');
    if (timeTrackingCtx) {
        new Chart(timeTrackingCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['timeTracking']['labels'] ?? []) !!},
                datasets: [{
                    label: 'Horas Trabajadas',
                    data: {!! json_encode($chartData['timeTracking']['data'] ?? []) !!},
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + 'h';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        window.revenueChart = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartData['revenue']['labels'] ?? []) !!},
                datasets: [{
                    label: 'Ingresos',
                    data: {!! json_encode($chartData['revenue']['data'] ?? []) !!},
                    backgroundColor: '#10B981',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Team Productivity Chart
    const teamProductivityCtx = document.getElementById('teamProductivityChart');
    if (teamProductivityCtx) {
        new Chart(teamProductivityCtx, {
            type: 'horizontalBar',
            data: {
                labels: {!! json_encode($chartData['teamProductivity']['labels'] ?? []) !!},
                datasets: [{
                    label: 'Horas',
                    data: {!! json_encode($chartData['teamProductivity']['data'] ?? []) !!},
                    backgroundColor: '#6366F1',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + 'h';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Project Distribution Chart
    const projectDistributionCtx = document.getElementById('projectDistributionChart');
    if (projectDistributionCtx) {
        new Chart(projectDistributionCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($chartData['projectDistribution']['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($chartData['projectDistribution']['data'] ?? []) !!},
                    backgroundColor: [
                        '#EF4444',
                        '#F59E0B',
                        '#10B981',
                        '#3B82F6',
                        '#8B5CF6',
                        '#F97316'
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
                        labels: {
                            boxWidth: 12,
                            padding: 10
                        }
                    }
                }
            }
        });
    }
});

// Function to update revenue chart
function updateRevenueChart(period) {
    fetch(`/dashboard/revenue-data?period=${period}`)
        .then(response => response.json())
        .then(data => {
            window.revenueChart.data.labels = data.labels;
            window.revenueChart.data.datasets[0].data = data.data;
            window.revenueChart.update();
        })
        .catch(error => {
            console.error('Error updating revenue chart:', error);
        });
}

// Real-time updates
setInterval(() => {
    fetch('/dashboard/live-stats')
        .then(response => response.json())
        .then(data => {
            // Update live statistics
            document.querySelectorAll('[data-stat]').forEach(element => {
                const stat = element.dataset.stat;
                if (data[stat] !== undefined) {
                    element.textContent = data[stat];
                }
            });
        })
        .catch(error => {
            console.error('Error fetching live stats:', error);
        });
}, 30000); // Update every 30 seconds
</script>
@endpush
