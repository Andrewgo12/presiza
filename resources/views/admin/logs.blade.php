@extends('layouts.admin')

@section('title', 'Logs del Sistema')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                    Logs del Sistema
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Monitoreo de eventos y errores del sistema
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <button type="button" 
                        onclick="refreshLogs()"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Actualizar
                </button>
                <form method="POST" action="{{ route('admin.settings.logs.clear') }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            onclick="return confirm('¿Estás seguro de que quieres limpiar todos los logs?')"
                            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Limpiar Logs
                    </button>
                </form>
            </div>
        </div>

        <!-- Log Filters -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <label for="level_filter" class="block text-sm font-medium text-gray-700">Nivel</label>
                        <select id="level_filter" 
                                onchange="filterLogs()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos los niveles</option>
                            <option value="emergency">Emergency</option>
                            <option value="alert">Alert</option>
                            <option value="critical">Critical</option>
                            <option value="error">Error</option>
                            <option value="warning">Warning</option>
                            <option value="notice">Notice</option>
                            <option value="info">Info</option>
                            <option value="debug">Debug</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="channel_filter" class="block text-sm font-medium text-gray-700">Canal</label>
                        <select id="channel_filter" 
                                onchange="filterLogs()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos los canales</option>
                            <option value="local">Local</option>
                            <option value="production">Production</option>
                            <option value="security">Security</option>
                            <option value="database">Database</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search_filter" class="block text-sm font-medium text-gray-700">Buscar</label>
                        <input type="text" 
                               id="search_filter"
                               onkeyup="filterLogs()"
                               placeholder="Buscar en mensajes..."
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="button" 
                                onclick="clearFilters()"
                                class="w-full inline-flex justify-center items-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="logs-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Timestamp
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nivel
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Canal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mensaje
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="log-row hover:bg-gray-50" 
                                data-level="{{ $log['level'] }}" 
                                data-channel="{{ $log['channel'] }}" 
                                data-message="{{ strtolower($log['message']) }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log['timestamp'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $log['level'] === 'error' || $log['level'] === 'critical' || $log['level'] === 'emergency' ? 'bg-red-100 text-red-800' : 
                                           ($log['level'] === 'warning' || $log['level'] === 'alert' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($log['level'] === 'info' || $log['level'] === 'notice' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($log['level']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ ucfirst($log['channel']) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs truncate" title="{{ $log['message'] }}">
                                        {{ $log['message'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button type="button" 
                                            onclick="showLogDetails('{{ addslashes($log['message']) }}', '{{ $log['timestamp'] }}', '{{ $log['level'] }}', '{{ $log['channel'] }}')"
                                            class="text-indigo-600 hover:text-indigo-900">
                                        Ver detalles
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay logs disponibles</h3>
                                    <p class="mt-1 text-sm text-gray-500">Los logs del sistema aparecerán aquí cuando se generen eventos.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div id="logModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeLogModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Detalles del Log
                        </h3>
                        <div class="mt-4 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Timestamp:</label>
                                <p id="modal-timestamp" class="text-sm text-gray-900"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nivel:</label>
                                <p id="modal-level" class="text-sm text-gray-900"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Canal:</label>
                                <p id="modal-channel" class="text-sm text-gray-900"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mensaje:</label>
                                <p id="modal-message" class="text-sm text-gray-900 break-words"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        onclick="closeLogModal()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterLogs() {
    const levelFilter = document.getElementById('level_filter').value.toLowerCase();
    const channelFilter = document.getElementById('channel_filter').value.toLowerCase();
    const searchFilter = document.getElementById('search_filter').value.toLowerCase();
    const rows = document.querySelectorAll('.log-row');
    
    rows.forEach(row => {
        const level = row.dataset.level.toLowerCase();
        const channel = row.dataset.channel.toLowerCase();
        const message = row.dataset.message.toLowerCase();
        
        const levelMatch = !levelFilter || level.includes(levelFilter);
        const channelMatch = !channelFilter || channel.includes(channelFilter);
        const searchMatch = !searchFilter || message.includes(searchFilter);
        
        if (levelMatch && channelMatch && searchMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function clearFilters() {
    document.getElementById('level_filter').value = '';
    document.getElementById('channel_filter').value = '';
    document.getElementById('search_filter').value = '';
    filterLogs();
}

function showLogDetails(message, timestamp, level, channel) {
    document.getElementById('modal-message').textContent = message;
    document.getElementById('modal-timestamp').textContent = timestamp;
    document.getElementById('modal-level').textContent = level;
    document.getElementById('modal-channel').textContent = channel;
    document.getElementById('logModal').classList.remove('hidden');
}

function closeLogModal() {
    document.getElementById('logModal').classList.add('hidden');
}

function refreshLogs() {
    window.location.reload();
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLogModal();
    }
});
</script>
@endpush
@endsection
