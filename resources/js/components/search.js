// Global Search Component
window.SearchManager = {
    // Configuration
    config: {
        apiUrl: '/api/search',
        minQueryLength: 2,
        debounceDelay: 300,
        maxResults: 10
    },

    // State
    state: {
        isSearching: false,
        results: {},
        currentQuery: '',
        selectedIndex: -1
    },

    // Initialize search functionality
    init() {
        this.bindEvents();
        this.createSearchModal();
        console.log('SearchManager initialized');
    },

    // Bind event listeners
    bindEvents() {
        // Global search shortcut (Ctrl/Cmd + K)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.openSearchModal();
            }

            // Escape to close search
            if (e.key === 'Escape') {
                this.closeSearchModal();
            }
        });

        // Search button click
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-open-search]')) {
                e.preventDefault();
                this.openSearchModal();
            }
        });
    },

    // Create search modal
    createSearchModal() {
        const modal = document.createElement('div');
        modal.id = 'search-modal';
        modal.className = 'fixed inset-0 z-50 overflow-y-auto hidden';
        modal.innerHTML = `
            <div class="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="SearchManager.closeSearchModal()"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center mb-4">
                            <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" 
                                   id="search-input"
                                   placeholder="Buscar usuarios, proyectos, evidencias..."
                                   class="flex-1 border-0 focus:ring-0 text-lg placeholder-gray-400">
                        </div>
                        
                        <div id="search-results" class="max-h-96 overflow-y-auto">
                            <div id="search-loading" class="hidden text-center py-8">
                                <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="mt-2 text-gray-500">Buscando...</p>
                            </div>
                            
                            <div id="search-empty" class="hidden text-center py-8">
                                <svg class="h-12 w-12 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <p class="mt-2 text-gray-500">No se encontraron resultados</p>
                            </div>
                            
                            <div id="search-content"></div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 text-xs text-gray-500">
                        <div class="flex justify-between">
                            <span>Presiona <kbd class="px-1 py-0.5 bg-gray-200 rounded">↑↓</kbd> para navegar</span>
                            <span>Presiona <kbd class="px-1 py-0.5 bg-gray-200 rounded">Enter</kbd> para seleccionar</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Bind search input events
        const searchInput = document.getElementById('search-input');
        let debounceTimer;

        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.performSearch(e.target.value);
            }, this.config.debounceDelay);
        });

        // Keyboard navigation
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.navigateResults(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.navigateResults(-1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                this.selectResult();
            }
        });
    },

    // Open search modal
    openSearchModal() {
        const modal = document.getElementById('search-modal');
        const input = document.getElementById('search-input');
        
        modal.classList.remove('hidden');
        input.focus();
        input.value = '';
        this.clearResults();
    },

    // Close search modal
    closeSearchModal() {
        const modal = document.getElementById('search-modal');
        modal.classList.add('hidden');
        this.clearResults();
    },

    // Perform search
    async performSearch(query) {
        this.state.currentQuery = query;
        this.state.selectedIndex = -1;

        if (query.length < this.config.minQueryLength) {
            this.clearResults();
            return;
        }

        this.showLoading();

        try {
            const response = await fetch(`${this.config.apiUrl}?q=${encodeURIComponent(query)}&limit=${this.config.maxResults}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.displayResults(data.results);
            } else {
                this.showError('Error en la búsqueda');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Error de conexión');
        }
    },

    // Display search results
    displayResults(results) {
        const content = document.getElementById('search-content');
        const loading = document.getElementById('search-loading');
        const empty = document.getElementById('search-empty');

        loading.classList.add('hidden');

        if (!results || Object.keys(results).length === 0) {
            empty.classList.remove('hidden');
            content.innerHTML = '';
            return;
        }

        empty.classList.add('hidden');
        content.innerHTML = '';

        Object.entries(results).forEach(([type, items]) => {
            if (items.length > 0) {
                const section = document.createElement('div');
                section.className = 'mb-4';
                
                const header = document.createElement('h3');
                header.className = 'text-sm font-medium text-gray-900 mb-2 px-3';
                header.textContent = this.getTypeLabel(type);
                section.appendChild(header);

                items.forEach((item, index) => {
                    const resultItem = this.createResultItem(item, type, index);
                    section.appendChild(resultItem);
                });

                content.appendChild(section);
            }
        });
    },

    // Create result item element
    createResultItem(item, type, index) {
        const div = document.createElement('div');
        div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer search-result-item';
        div.dataset.url = item.url;
        div.dataset.index = index;

        div.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0 mr-3">
                    ${this.getTypeIcon(type)}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${item.title}</p>
                    <p class="text-sm text-gray-500 truncate">${item.subtitle || ''}</p>
                </div>
                ${item.status ? `<span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${item.status}</span>` : ''}
            </div>
        `;

        div.addEventListener('click', () => {
            window.location.href = item.url;
        });

        return div;
    },

    // Navigate through results with keyboard
    navigateResults(direction) {
        const items = document.querySelectorAll('.search-result-item');
        if (items.length === 0) return;

        // Remove previous selection
        items[this.state.selectedIndex]?.classList.remove('bg-gray-100');

        // Update selected index
        this.state.selectedIndex += direction;
        if (this.state.selectedIndex < 0) this.state.selectedIndex = items.length - 1;
        if (this.state.selectedIndex >= items.length) this.state.selectedIndex = 0;

        // Add selection to new item
        items[this.state.selectedIndex].classList.add('bg-gray-100');
        items[this.state.selectedIndex].scrollIntoView({ block: 'nearest' });
    },

    // Select current result
    selectResult() {
        const items = document.querySelectorAll('.search-result-item');
        if (this.state.selectedIndex >= 0 && items[this.state.selectedIndex]) {
            const url = items[this.state.selectedIndex].dataset.url;
            window.location.href = url;
        }
    },

    // Show loading state
    showLoading() {
        document.getElementById('search-loading').classList.remove('hidden');
        document.getElementById('search-empty').classList.add('hidden');
        document.getElementById('search-content').innerHTML = '';
    },

    // Show error message
    showError(message) {
        const content = document.getElementById('search-content');
        content.innerHTML = `<div class="text-center py-8 text-red-500">${message}</div>`;
        document.getElementById('search-loading').classList.add('hidden');
    },

    // Clear results
    clearResults() {
        document.getElementById('search-content').innerHTML = '';
        document.getElementById('search-loading').classList.add('hidden');
        document.getElementById('search-empty').classList.add('hidden');
        this.state.selectedIndex = -1;
    },

    // Get type label
    getTypeLabel(type) {
        const labels = {
            users: 'Usuarios',
            projects: 'Proyectos',
            evidences: 'Evidencias',
            groups: 'Grupos'
        };
        return labels[type] || type;
    },

    // Get type icon
    getTypeIcon(type) {
        const icons = {
            users: '<svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
            projects: '<svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>',
            evidences: '<svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            groups: '<svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>'
        };
        return icons[type] || icons.projects;
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    SearchManager.init();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SearchManager;
}
