@extends('layouts.admin')

@section('title', 'Analytics del Sistema')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                    Analytics del Sistema
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Análisis detallado del rendimiento y uso del sistema
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <button type="button" 
                        onclick="window.print()"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.081M6.75 18h10.5" />
                    </svg>
                    Exportar Reporte
                </button>
            </div>
        </div>

        <!-- User Activity Analytics -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        Actividad de Usuarios (Últimos 7 días)
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Chart placeholder -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <canvas id="userActivityChart" width="400" height="200"></canvas>
                        </div>
                        
                        <!-- Stats -->
                        <div class="space-y-4">
                            @foreach($analytics['user_activity'] as $day)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $day['label'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $day['date'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">{{ $day['logins'] }} accesos</p>
                                        <p class="text-xs text-gray-500">{{ $day['registrations'] }} registros</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Progress Analytics -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        Progreso de Proyectos
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- By Status -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">Por Estado</h4>
                            <div class="space-y-3">
                                @foreach($analytics['project_progress']['by_status'] as $status)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-3
                                                {{ $status['status'] === 'completed' ? 'bg-green-500' : 
                                                   ($status['status'] === 'in_progress' ? 'bg-blue-500' : 
                                                   ($status['status'] === 'planning' ? 'bg-yellow-500' : 'bg-gray-500')) }}">
                                            </div>
                                            <span class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $status['status'])) }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $status['count'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- By Priority -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">Por Prioridad</h4>
                            <div class="space-y-3">
                                @foreach($analytics['project_progress']['by_priority'] as $priority)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-3
                                                {{ $priority['priority'] === 'high' ? 'bg-red-500' : 
                                                   ($priority['priority'] === 'medium' ? 'bg-yellow-500' : 'bg-green-500') }}">
                                            </div>
                                            <span class="text-sm text-gray-900">{{ ucfirst($priority['priority']) }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $priority['count'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evidence Trends -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        Tendencias de Evidencias (Últimos 6 meses)
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mes
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Enviadas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aprobadas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tasa de Aprobación
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($analytics['evidence_trends'] as $trend)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $trend['label'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $trend['submitted'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $trend['approved'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($trend['submitted'] > 0)
                                                {{ round(($trend['approved'] / $trend['submitted']) * 100, 1) }}%
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Performance -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        Rendimiento del Sistema
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-gray-50 overflow-hidden rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Base de Datos</dt>
                                            <dd class="text-lg font-medium text-gray-900">{{ $analytics['system_performance']['database_size'] }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 overflow-hidden rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026C6.154 9.75 8.25 11.846 8.25 14.25c0 2.404-2.096 4.5-4.156 4.5-.117 0-.232-.009-.344-.026m4.156-8.474a2.25 2.25 0 00-4.078 0m4.078 0c.929-.929 2.707-.929 3.636 0m-3.636 0a2.25 2.25 0 013.636 0M16.5 12a3 3 0 11-6 0 3 3 0 016 0zm0 0v1.5a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V12a2.25 2.25 0 012.25-2.25h8.25A2.25 2.25 0 0116.5 12z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Almacenamiento</dt>
                                            <dd class="text-lg font-medium text-gray-900">{{ $analytics['system_performance']['file_storage'] }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 overflow-hidden rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Sesiones Activas</dt>
                                            <dd class="text-lg font-medium text-gray-900">{{ $analytics['system_performance']['active_sessions'] }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 overflow-hidden rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Tiempo de Respuesta</dt>
                                            <dd class="text-lg font-medium text-gray-900">{{ $analytics['system_performance']['response_time'] }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Activity Chart
    const ctx = document.getElementById('userActivityChart').getContext('2d');
    const userActivityData = @json($analytics['user_activity']);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: userActivityData.map(day => day.label),
            datasets: [{
                label: 'Accesos',
                data: userActivityData.map(day => day.logins),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }, {
                label: 'Registros',
                data: userActivityData.map(day => day.registrations),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Actividad de Usuarios'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
@endsection
