@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')
<div class="min-h-full" x-data="notificationsManager()">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center">
                        <div>
                            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                                Notificaciones
                            </h1>
                            <dl class="mt-2 flex flex-col sm:mt-1 sm:flex-row sm:flex-wrap">
                                <dt class="sr-only">Estadísticas</dt>
                                <dd class="text-sm text-gray-500">
                                    Total: {{ $stats['total'] }} | 
                                    Sin leer: {{ $stats['unread'] }} | 
                                    Hoy: {{ $stats['today'] }} |
                                    Esta semana: {{ $stats['this_week'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex space-x-3 md:ml-4 md:mt-0">
                    @if($stats['unread'] > 0)
                        <button @click="markAllAsRead()" 
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Marcar todas como leídas
                        </button>
                    @endif
                    @if($stats['total'] > 0)
                        <button @click="clearAll()" 
                                class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Limpiar todas
                        </button>
                    @endif
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
                        <label for="search" class="sr-only">Buscar notificaciones</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0110.607 10.607z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   value="{{ request('search') }}"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="Buscar en notificaciones...">
                        </div>
                    </div>

                    <div>
                        <select name="read" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todas</option>
                            <option value="unread" {{ request('read') === 'unread' ? 'selected' : '' }}>Sin leer</option>
                            <option value="read" {{ request('read') === 'read' ? 'selected' : '' }}>Leídas</option>
                        </select>
                    </div>

                    <div>
                        <select name="type" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos los tipos</option>
                            <option value="project" {{ request('type') === 'project' ? 'selected' : '' }}>Proyectos</option>
                            <option value="milestone" {{ request('type') === 'milestone' ? 'selected' : '' }}>Milestones</option>
                            <option value="time_log" {{ request('type') === 'time_log' ? 'selected' : '' }}>Tiempo</option>
                            <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>Sistema</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Filtrar
                    </button>

                    @if(request()->hasAny(['search', 'read', 'type']))
                        <a href="{{ route('notifications.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($notifications->count() > 0)
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        @foreach($notifications as $notification)
                            <div class="p-6 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }} hover:bg-gray-50 transition-colors">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        @if(!$notification->read_at)
                                            <div class="h-2 w-2 bg-blue-600 rounded-full"></div>
                                        @else
                                            <div class="h-2 w-2 bg-gray-300 rounded-full"></div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $notification->data['title'] ?? 'Notificación' }}
                                            </p>
                                            <div class="flex items-center space-x-2">
                                                <time class="text-xs text-gray-500">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </time>
                                                <div class="flex items-center space-x-1">
                                                    @if(!$notification->read_at)
                                                        <button @click="markAsRead('{{ $notification->id }}')" 
                                                                class="text-indigo-600 hover:text-indigo-900 text-xs">
                                                            Marcar como leída
                                                        </button>
                                                    @endif
                                                    <button @click="deleteNotification('{{ $notification->id }}')" 
                                                            class="text-red-600 hover:text-red-900">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if(isset($notification->data['message']))
                                            <p class="mt-1 text-sm text-gray-600">
                                                {{ $notification->data['message'] }}
                                            </p>
                                        @endif
                                        
                                        @if(isset($notification->data['action_url']))
                                            <div class="mt-3">
                                                <a href="{{ $notification->data['action_url'] }}" 
                                                   class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                                                    {{ $notification->data['action_text'] ?? 'Ver detalles' }}
                                                    <svg class="ml-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ class_basename($notification->type) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay notificaciones</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if(request()->hasAny(['search', 'read', 'type']))
                            No se encontraron notificaciones que coincidan con los filtros aplicados.
                        @else
                            No tienes notificaciones en este momento.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function notificationsManager() {
    return {
        async markAsRead(notificationId) {
            try {
                const response = await fetch(`{{ route('notifications.read', '') }}/${notificationId}`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al marcar como leída');
                }
            } catch (error) {
                console.error('Error marking as read:', error);
                alert('Error al marcar como leída');
            }
        },

        async markAllAsRead() {
            if (!confirm('¿Marcar todas las notificaciones como leídas?')) {
                return;
            }

            try {
                const response = await fetch('{{ route('notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al marcar todas como leídas');
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
                alert('Error al marcar todas como leídas');
            }
        },

        async deleteNotification(notificationId) {
            if (!confirm('¿Eliminar esta notificación?')) {
                return;
            }

            try {
                const response = await fetch(`{{ route('notifications.destroy', '') }}/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al eliminar la notificación');
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
                alert('Error al eliminar la notificación');
            }
        },

        async clearAll() {
            if (!confirm('¿Eliminar todas las notificaciones? Esta acción no se puede deshacer.')) {
                return;
            }

            try {
                const response = await fetch('{{ route('notifications.clear') }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al eliminar las notificaciones');
                }
            } catch (error) {
                console.error('Error clearing notifications:', error);
                alert('Error al eliminar las notificaciones');
            }
        }
    }
}
</script>
@endpush
@endsection
