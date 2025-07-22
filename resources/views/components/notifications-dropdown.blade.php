<div class="max-h-96 overflow-y-auto">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
        <h3 class="text-sm font-medium text-gray-900">Notificaciones</h3>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-500">
                    Marcar todas como leídas
                </button>
            </form>
        @endif
    </div>

    <!-- Notifications List -->
    <div class="divide-y divide-gray-200">
        @forelse(auth()->user()->notifications->take(5) as $notification)
            <div class="px-4 py-3 hover:bg-gray-50 transition-colors duration-150 {{ $notification->read_at ? 'opacity-75' : 'bg-blue-50' }}">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        @switch($notification->type)
                            @case('App\Notifications\EvidenceAssigned')
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                @break
                            @case('App\Notifications\MessageReceived')
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                    <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75z" />
                                    </svg>
                                </div>
                                @break
                            @case('App\Notifications\ProjectInvitation')
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100">
                                    <svg class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                                    </svg>
                                </div>
                                @break
                            @default
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
                                    <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                    </svg>
                                </div>
                        @endswitch
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $notification->data['title'] ?? 'Notificación' }}
                        </p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ $notification->data['message'] ?? 'Nueva notificación disponible' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>

                    @if(!$notification->read_at)
                        <div class="flex-shrink-0">
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-500">
                                    Marcar como leída
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay notificaciones</h3>
                <p class="mt-1 text-sm text-gray-500">Cuando tengas nuevas notificaciones aparecerán aquí.</p>
            </div>
        @endforelse
    </div>

    <!-- Footer -->
    @if(auth()->user()->notifications->count() > 5)
        <div class="border-t border-gray-200 px-4 py-3">
            <a href="{{ route('notifications.index') }}" 
               class="block text-center text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                Ver todas las notificaciones ({{ auth()->user()->notifications->count() }})
            </a>
        </div>
    @endif
</div>
