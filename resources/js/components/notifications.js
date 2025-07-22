// Notification Management Component
window.NotificationManager = {
    // Configuration
    config: {
        apiUrl: '/api/notifications',
        refreshInterval: 30000, // 30 seconds
        maxNotifications: 50,
        autoMarkAsRead: true
    },

    // State
    state: {
        notifications: [],
        unreadCount: 0,
        isLoading: false,
        lastFetch: null
    },

    // Initialize the notification manager
    init() {
        this.loadNotifications();
        this.startPeriodicRefresh();
        this.bindEvents();
        console.log('NotificationManager initialized');
    },

    // Load notifications from API
    async loadNotifications() {
        this.state.isLoading = true;
        
        try {
            const response = await fetch(`${this.config.apiUrl}/unread-count`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateUnreadCount(data.count);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        } finally {
            this.state.isLoading = false;
            this.state.lastFetch = new Date();
        }
    },

    // Update unread count in UI
    updateUnreadCount(count) {
        this.state.unreadCount = count;
        
        // Update badge in navigation
        const badge = document.querySelector('[data-notification-badge]');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Update dropdown indicator
        const indicator = document.querySelector('[data-notification-indicator]');
        if (indicator) {
            if (count > 0) {
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        }

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('notificationCountUpdated', {
            detail: { count }
        }));
    },

    // Mark notification as read
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                this.loadNotifications(); // Refresh count
                return true;
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
        return false;
    },

    // Mark all notifications as read
    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/read-all', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                this.updateUnreadCount(0);
                this.showToast('Todas las notificaciones marcadas como leídas', 'success');
                return true;
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            this.showToast('Error al marcar notificaciones como leídas', 'error');
        }
        return false;
    },

    // Start periodic refresh
    startPeriodicRefresh() {
        setInterval(() => {
            if (!document.hidden) {
                this.loadNotifications();
            }
        }, this.config.refreshInterval);

        // Refresh when page becomes visible
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.loadNotifications();
            }
        });
    },

    // Bind event listeners
    bindEvents() {
        // Mark as read when notification is clicked
        document.addEventListener('click', (e) => {
            const notificationItem = e.target.closest('[data-notification-id]');
            if (notificationItem && this.config.autoMarkAsRead) {
                const id = notificationItem.dataset.notificationId;
                this.markAsRead(id);
            }
        });

        // Mark all as read button
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-mark-all-read]')) {
                e.preventDefault();
                this.markAllAsRead();
            }
        });
    },

    // Show toast notification
    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white transition-all duration-300 transform translate-x-full ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
        }`;
        toast.textContent = message;

        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    },

    // Get notification count
    getUnreadCount() {
        return this.state.unreadCount;
    },

    // Check if notifications are loading
    isLoading() {
        return this.state.isLoading;
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('meta[name="csrf-token"]')) {
        NotificationManager.init();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}
