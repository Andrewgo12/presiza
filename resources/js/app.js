import './bootstrap';
import Alpine from 'alpinejs';

// Configurar Alpine.js
window.Alpine = Alpine;

// Registrar componentes globales de Alpine.js
Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
}));

Alpine.data('modal', () => ({
    open: false,
    show() {
        this.open = true;
        document.body.classList.add('overflow-hidden');
    },
    hide() {
        this.open = false;
        document.body.classList.remove('overflow-hidden');
    }
}));

Alpine.data('notification', () => ({
    show: false,
    message: '',
    type: 'info',
    timeout: null,
    
    display(message, type = 'info', duration = 5000) {
        this.message = message;
        this.type = type;
        this.show = true;
        
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        
        this.timeout = setTimeout(() => {
            this.hide();
        }, duration);
    },
    
    hide() {
        this.show = false;
        this.message = '';
    }
}));

Alpine.data('fileUpload', () => ({
    files: [],
    dragover: false,
    uploading: false,
    progress: 0,
    
    handleDrop(e) {
        this.dragover = false;
        const droppedFiles = Array.from(e.dataTransfer.files);
        this.addFiles(droppedFiles);
    },
    
    handleFileSelect(e) {
        const selectedFiles = Array.from(e.target.files);
        this.addFiles(selectedFiles);
    },
    
    addFiles(newFiles) {
        newFiles.forEach(file => {
            if (this.validateFile(file)) {
                this.files.push({
                    file: file,
                    name: file.name,
                    size: this.formatFileSize(file.size),
                    type: file.type,
                    id: Date.now() + Math.random()
                });
            }
        });
    },
    
    removeFile(id) {
        this.files = this.files.filter(f => f.id !== id);
    },
    
    validateFile(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain', 'text/csv'
        ];
        
        if (file.size > maxSize) {
            this.$dispatch('notification', {
                message: `El archivo ${file.name} es demasiado grande. Máximo 10MB.`,
                type: 'error'
            });
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            this.$dispatch('notification', {
                message: `Tipo de archivo no permitido: ${file.type}`,
                type: 'error'
            });
            return false;
        }
        
        return true;
    },
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}));

Alpine.data('search', () => ({
    query: '',
    results: [],
    loading: false,
    showResults: false,
    
    async search() {
        if (this.query.length < 2) {
            this.results = [];
            this.showResults = false;
            return;
        }
        
        this.loading = true;
        
        try {
            const response = await fetch(`/api/search?q=${encodeURIComponent(this.query)}`);
            const data = await response.json();
            this.results = data.results || [];
            this.showResults = true;
        } catch (error) {
            console.error('Error en búsqueda:', error);
            this.results = [];
        } finally {
            this.loading = false;
        }
    },
    
    selectResult(result) {
        window.location.href = result.url;
    },
    
    clearSearch() {
        this.query = '';
        this.results = [];
        this.showResults = false;
    }
}));

Alpine.data('dataTable', () => ({
    sortColumn: '',
    sortDirection: 'asc',
    selectedItems: [],
    selectAll: false,
    
    sort(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }
        
        // Trigger sort event
        this.$dispatch('sort', {
            column: this.sortColumn,
            direction: this.sortDirection
        });
    },
    
    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedItems = [...this.allItems];
        } else {
            this.selectedItems = [];
        }
    },
    
    toggleSelectItem(item) {
        const index = this.selectedItems.indexOf(item);
        if (index > -1) {
            this.selectedItems.splice(index, 1);
        } else {
            this.selectedItems.push(item);
        }
        
        this.selectAll = this.selectedItems.length === this.allItems.length;
    }
}));

Alpine.data('tabs', () => ({
    activeTab: '',
    
    init() {
        // Set first tab as active if none specified
        if (!this.activeTab) {
            const firstTab = this.$el.querySelector('[x-data]');
            if (firstTab) {
                this.activeTab = firstTab.getAttribute('data-tab') || '0';
            }
        }
    },
    
    setActiveTab(tab) {
        this.activeTab = tab;
    },
    
    isActive(tab) {
        return this.activeTab === tab;
    }
}));

// Funciones globales útiles
window.utils = {
    // Formatear fecha
    formatDate(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        
        return new Date(date).toLocaleDateString('es-ES', { ...defaultOptions, ...options });
    },
    
    // Formatear fecha y hora
    formatDateTime(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        
        return new Date(date).toLocaleDateString('es-ES', { ...defaultOptions, ...options });
    },
    
    // Formatear tamaño de archivo
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    // Copiar al portapapeles
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            // Fallback para navegadores más antiguos
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                document.body.removeChild(textArea);
                return true;
            } catch (err) {
                document.body.removeChild(textArea);
                return false;
            }
        }
    },
    
    // Debounce function
    debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    },
    
    // Throttle function
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    // Generar ID único
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },
    
    // Validar email
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Truncar texto
    truncate(text, length = 100, suffix = '...') {
        if (text.length <= length) return text;
        return text.substring(0, length) + suffix;
    },
    
    // Capitalizar primera letra
    capitalize(text) {
        return text.charAt(0).toUpperCase() + text.slice(1);
    },
    
    // Slug de texto
    slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-');
    }
};

// Event listeners globales
document.addEventListener('DOMContentLoaded', function() {
    // Configurar CSRF token para requests AJAX
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
    }
    
    // Configurar tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
    
    // Configurar confirmaciones de eliminación
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-delete') || '¿Estás seguro de que quieres eliminar este elemento?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-hide alerts después de 5 segundos
    const alerts = document.querySelectorAll('.alert[data-auto-hide]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Funciones para tooltips
function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip show';
    tooltip.textContent = e.target.getAttribute('data-tooltip');
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    
    e.target._tooltip = tooltip;
}

function hideTooltip(e) {
    if (e.target._tooltip) {
        e.target._tooltip.remove();
        delete e.target._tooltip;
    }
}

// Configurar atajos de teclado globales
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K para búsqueda global
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('#global-search');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Escape para cerrar modales
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal[x-show="true"]');
        openModals.forEach(modal => {
            modal.dispatchEvent(new CustomEvent('close-modal'));
        });
    }
});

// Inicializar Alpine.js
Alpine.start();

// Exportar Alpine para uso global
window.Alpine = Alpine;
