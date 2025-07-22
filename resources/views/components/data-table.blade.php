@props([
    'headers' => [],
    'rows' => [],
    'searchable' => true,
    'sortable' => true,
    'filterable' => false,
    'exportable' => false,
    'selectable' => false,
    'actions' => [],
    'emptyMessage' => 'No hay datos disponibles',
    'emptyIcon' => 'table-cells',
    'pagination' => null,
    'bulkActions' => []
])

<div class="bg-white shadow rounded-lg overflow-hidden" x-data="dataTable()">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ $title ?? 'Datos' }}
                    @if($pagination)
                        <span class="ml-2 text-sm text-gray-500">({{ $pagination->total() }} registros)</span>
                    @endif
                </h3>
                
                @if($selectable && count($bulkActions) > 0)
                    <div x-show="selectedItems.length > 0" 
                         x-transition
                         class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">
                            <span x-text="selectedItems.length"></span> seleccionados
                        </span>
                        <div class="flex space-x-1">
                            @foreach($bulkActions as $action)
                                <button @click="{{ $action['action'] }}(selectedItems)" 
                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white {{ $action['class'] ?? 'bg-indigo-600 hover:bg-indigo-700' }}">
                                    @if(isset($action['icon']))
                                        <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            {!! $action['icon'] !!}
                                        </svg>
                                    @endif
                                    {{ $action['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center space-x-3">
                @if($searchable)
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0110.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text" 
                               x-model="searchTerm"
                               @input.debounce.300ms="filterData()"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="Buscar...">
                    </div>
                @endif

                @if($filterable)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                            </svg>
                            Filtros
                        </button>
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <div class="py-1">
                                {{ $filters ?? '' }}
                            </div>
                        </div>
                    </div>
                @endif

                @if($exportable)
                    <button @click="exportData()" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Exportar
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Table Content -->
    @if(count($rows) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if($selectable)
                            <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <input type="checkbox" 
                                       @change="toggleAll($event.target.checked)"
                                       class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 sm:left-6">
                            </th>
                        @endif
                        
                        @foreach($headers as $header)
                            <th scope="col" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider {{ $header['class'] ?? '' }}">
                                @if($sortable && isset($header['sortable']) && $header['sortable'])
                                    <button @click="sortBy('{{ $header['key'] }}')" 
                                            class="group inline-flex items-center hover:text-gray-700">
                                        {{ $header['label'] }}
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                            </svg>
                                        </span>
                                    </button>
                                @else
                                    {{ $header['label'] }}
                                @endif
                            </th>
                        @endforeach
                        
                        @if(count($actions) > 0)
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acciones</span>
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rows as $index => $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            @if($selectable)
                                <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                    <input type="checkbox" 
                                           value="{{ $row['id'] ?? $index }}"
                                           x-model="selectedItems"
                                           class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 sm:left-6">
                                </td>
                            @endif
                            
                            @foreach($headers as $header)
                                <td class="px-6 py-4 whitespace-nowrap {{ $header['cellClass'] ?? '' }}">
                                    @if(isset($header['component']))
                                        @include($header['component'], ['value' => $row[$header['key']] ?? '', 'row' => $row])
                                    @else
                                        <div class="{{ $header['textClass'] ?? 'text-sm text-gray-900' }}">
                                            {!! $row[$header['key']] ?? '' !!}
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            
                            @if(count($actions) > 0)
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        @foreach($actions as $action)
                                            @if(isset($action['condition']) && !$action['condition']($row))
                                                @continue
                                            @endif
                                            
                                            @if($action['type'] === 'link')
                                                <a href="{{ $action['url']($row) }}" 
                                                   class="text-{{ $action['color'] ?? 'indigo' }}-600 hover:text-{{ $action['color'] ?? 'indigo' }}-900"
                                                   title="{{ $action['title'] ?? $action['label'] }}">
                                                    @if(isset($action['icon']))
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            {!! $action['icon'] !!}
                                                        </svg>
                                                    @else
                                                        {{ $action['label'] }}
                                                    @endif
                                                </a>
                                            @elseif($action['type'] === 'button')
                                                <button @click="{{ $action['action'] }}({{ json_encode($row) }})" 
                                                        class="text-{{ $action['color'] ?? 'indigo' }}-600 hover:text-{{ $action['color'] ?? 'indigo' }}-900"
                                                        title="{{ $action['title'] ?? $action['label'] }}">
                                                    @if(isset($action['icon']))
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            {!! $action['icon'] !!}
                                                        </svg>
                                                    @else
                                                        {{ $action['label'] }}
                                                    @endif
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pagination)
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $pagination->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                @if($emptyIcon === 'table-cells')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75A1.125 1.125 0 012.25 18.375m0-12.75C2.25 4.629 2.871 4 3.375 4h1.875c.621 0 1.125.504 1.125 1.125L6.375 9.75m0 0L9 7.875l2.625 1.875M15.75 9.75l-2.625-1.875L15.75 6.75m0 3L18.375 7.875 21 9.75m-2.625 0L21 12.375l-2.625 1.875m0 0L15.75 15.75 13.125 14.625m0 0L9 15.75l-4.125-1.125m0 0L2.25 12.375 4.875 10.5m0 0L9 7.875l2.625 1.875" />
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                @endif
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ $emptyMessage }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $emptyDescription ?? 'No se encontraron registros que coincidan con los criterios de búsqueda.' }}</p>
            @if(isset($emptyAction))
                <div class="mt-6">
                    {!! $emptyAction !!}
                </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
function dataTable() {
    return {
        selectedItems: [],
        searchTerm: '',
        sortColumn: '',
        sortDirection: 'asc',

        toggleAll(checked) {
            if (checked) {
                this.selectedItems = Array.from(document.querySelectorAll('tbody input[type="checkbox"]')).map(cb => cb.value);
            } else {
                this.selectedItems = [];
            }
        },

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            // Implement sorting logic or trigger server-side sort
            this.applySorting();
        },

        filterData() {
            // Implement filtering logic or trigger server-side filter
            console.log('Filtering by:', this.searchTerm);
        },

        applySorting() {
            // Implement sorting logic or trigger server-side sort
            console.log('Sorting by:', this.sortColumn, this.sortDirection);
        },

        exportData() {
            // Implement export functionality
            console.log('Exporting data...');
        }
    }
}
</script>
@endpush
