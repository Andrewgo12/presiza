@extends('layouts.admin')

@section('title', 'Gestión de Grupos')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                    Gestión de Grupos
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Administra todos los grupos de trabajo del sistema
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" action="{{ route('admin.groups.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                        <input type="text" 
                               name="search" 
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Nombre del grupo..."
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700">Ordenar por</label>
                        <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Fecha de creación</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nombre</option>
                            <option value="members_count" {{ request('sort') === 'members_count' ? 'selected' : '' }}>Número de miembros</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Groups grid -->
        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($groups as $group)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-lg bg-indigo-500 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        {{ $group->name }}
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $group->members_count ?? 0 }} miembros
                                    </dd>
                                </dl>
                            </div>
                        </div>
                        
                        @if($group->description)
                            <div class="mt-4">
                                <p class="text-sm text-gray-600">
                                    {{ Str::limit($group->description, 100) }}
                                </p>
                            </div>
                        @endif
                        
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-500">
                                @if($group->leader)
                                    <img class="h-6 w-6 rounded-full mr-2" 
                                         src="{{ $group->leader->avatar_url }}" 
                                         alt="{{ $group->leader->full_name }}">
                                    <span>{{ $group->leader->full_name }}</span>
                                @else
                                    <span>Sin líder asignado</span>
                                @endif
                            </div>
                            
                            <div class="text-sm text-gray-500">
                                {{ $group->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <a href="{{ route('groups.show', $group) }}" 
                               class="flex-1 inline-flex justify-center items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Ver
                            </a>
                            <a href="{{ route('groups.edit', $group) }}" 
                               class="flex-1 inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Editar
                            </a>
                        </div>
                        
                        @if($group->projects_count > 0)
                            <div class="mt-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $group->projects_count }} proyecto(s) activo(s)
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay grupos</h3>
                        <p class="mt-1 text-sm text-gray-500">No se encontraron grupos con los filtros aplicados.</p>
                        <div class="mt-6">
                            <a href="{{ route('groups.create') }}" 
                               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Crear Grupo
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        
        @if($groups->hasPages())
            <div class="mt-6">
                {{ $groups->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
