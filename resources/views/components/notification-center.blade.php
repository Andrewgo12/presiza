@props([
    'position' => 'top-right'
])

<div class="fixed inset-0 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end z-50"
     x-data="notificationCenter()" 
     x-init="init()">
    
    <!-- Notification Container -->
    <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
        <!-- Success Notifications -->
        <template x-for="notification in notifications.filter(n => n.type === 'success')" :key="notification.id">
            <div x-show="notification.show"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="removeNotification(notification.id)" 
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Error Notifications -->
        <template x-for="notification in notifications.filter(n => n.type === 'error')" :key="notification.id">
            <div x-show="notification.show"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="removeNotification(notification.id)" 
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Warning Notifications -->
        <template x-for="notification in notifications.filter(n => n.type === 'warning')" :key="notification.id">
            <div x-show="notification.show"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="removeNotification(notification.id)" 
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Info Notifications -->
        <template x-for="notification in notifications.filter(n => n.type === 'info')" :key="notification.id">
            <div x-show="notification.show"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="removeNotification(notification.id)" 
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Action Notifications (with buttons) -->
        <template x-for="notification in notifications.filter(n => n.type === 'action')" :key="notification.id">
            <div x-show="notification.show"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
                            <div class="mt-4 flex space-x-2" x-show="notification.actions">
                                <template x-for="action in notification.actions" :key="action.label">
                                    <button @click="handleAction(action, notification)" 
                                            :class="action.primary ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <span x-text="action.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="removeNotification(notification.id)" 
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function notificationCenter() {
    return {
        notifications: [],
        nextId: 1,

        init() {
            // Listen for custom notification events
            window.addEventListener('notify', (event) => {
                this.addNotification(event.detail);
            });

            // Listen for server-sent events (if using real-time notifications)
            if (typeof EventSource !== 'undefined') {
                this.initServerSentEvents();
            }
        },

        addNotification(notification) {
            const id = this.nextId++;
            const newNotification = {
                id: id,
                type: notification.type || 'info',
                title: notification.title || 'Notificación',
                message: notification.message || '',
                actions: notification.actions || null,
                duration: notification.duration || (notification.type === 'error' ? 0 : 5000),
                show: false
            };

            this.notifications.push(newNotification);

            // Trigger show animation
            setTimeout(() => {
                const notif = this.notifications.find(n => n.id === id);
                if (notif) notif.show = true;
            }, 100);

            // Auto-remove after duration (if not persistent)
            if (newNotification.duration > 0) {
                setTimeout(() => {
                    this.removeNotification(id);
                }, newNotification.duration);
            }
        },

        removeNotification(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                notification.show = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        },

        handleAction(action, notification) {
            if (action.url) {
                window.location.href = action.url;
            } else if (action.callback) {
                action.callback();
            }
            
            if (action.dismiss !== false) {
                this.removeNotification(notification.id);
            }
        },

        initServerSentEvents() {
            const eventSource = new EventSource('/notifications/stream');
            
            eventSource.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.addNotification(data);
            };

            eventSource.onerror = (error) => {
                console.error('SSE Error:', error);
                // Optionally reconnect after a delay
                setTimeout(() => {
                    this.initServerSentEvents();
                }, 5000);
            };
        }
    }
}

// Global notification helper functions
window.notify = {
    success: (title, message, duration = 5000) => {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'success', title, message, duration }
        }));
    },
    
    error: (title, message, duration = 0) => {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'error', title, message, duration }
        }));
    },
    
    warning: (title, message, duration = 7000) => {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'warning', title, message, duration }
        }));
    },
    
    info: (title, message, duration = 5000) => {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'info', title, message, duration }
        }));
    },
    
    action: (title, message, actions, duration = 0) => {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'action', title, message, actions, duration }
        }));
    }
};
</script>
@endpush
