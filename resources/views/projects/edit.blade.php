@extends('layouts.app')

@section('title', 'Editar Proyecto')

@section('content')
<div class="min-h-full" x-data="editProjectForm()">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center">
                        <div>
                            <div class="flex items-center">
                                <a href="{{ route('projects.show', $project) }}" class="text-gray-500 hover:text-gray-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                </a>
                                <h1 class="ml-3 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                                    Editar Proyecto
                                </h1>
                            </div>
                            <dl class="mt-2 flex flex-col sm:ml-8 sm:mt-1 sm:flex-row sm:flex-wrap">
                                <dt class="sr-only">Proyecto</dt>
                                <dd class="text-sm text-gray-500">{{ $project->name }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Form Card -->
            <div class="bg-white shadow-xl rounded-lg">
                <form @submit.prevent="submitForm" class="space-y-8 divide-y divide-gray-200">
                    @csrf
                    @method('PUT')
                    
                    <!-- Información Básica -->
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Información del Proyecto</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Actualiza los detalles del proyecto.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <!-- Nombre del Proyecto -->
                                <div class="sm:col-span-6">
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Nombre del Proyecto <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" 
                                               name="name" 
                                               id="name" 
                                               x-model="form.name"
                                               required
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div x-show="errors.name" class="mt-2 text-sm text-red-600" x-text="errors.name"></div>
                                </div>

                                <!-- Descripción -->
                                <div class="sm:col-span-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Descripción del Proyecto
                                    </label>
                                    <div class="mt-1">
                                        <textarea name="description" 
                                                  id="description" 
                                                  rows="4" 
                                                  x-model="form.description"
                                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                </div>

                                <!-- Cliente y Presupuesto -->
                                <div class="sm:col-span-3">
                                    <label for="client_name" class="block text-sm font-medium text-gray-700">
                                        Cliente/Organización
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" 
                                               name="client_name" 
                                               id="client_name" 
                                               x-model="form.client_name"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="budget" class="block text-sm font-medium text-gray-700">
                                        Presupuesto (USD)
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" 
                                               name="budget" 
                                               id="budget" 
                                               x-model="form.budget"
                                               step="0.01"
                                               min="0"
                                               class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Estado y Prioridad -->
                                <div class="sm:col-span-3">
                                    <label for="status" class="block text-sm font-medium text-gray-700">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" 
                                            id="status" 
                                            x-model="form.status"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="planning">Planificación</option>
                                        <option value="in_progress">En Progreso</option>
                                        <option value="on_hold">En Pausa</option>
                                        <option value="completed">Completado</option>
                                        <option value="cancelled">Cancelado</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="priority" class="block text-sm font-medium text-gray-700">
                                        Prioridad <span class="text-red-500">*</span>
                                    </label>
                                    <select name="priority" 
                                            id="priority" 
                                            x-model="form.priority"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="low">Baja</option>
                                        <option value="medium">Media</option>
                                        <option value="high">Alta</option>
                                        <option value="critical">Crítica</option>
                                    </select>
                                </div>

                                <!-- Fechas -->
                                <div class="sm:col-span-2">
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">
                                        Fecha de Inicio
                                    </label>
                                    <input type="date" 
                                           name="start_date" 
                                           id="start_date" 
                                           x-model="form.start_date"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">
                                        Fecha de Fin Estimada
                                    </label>
                                    <input type="date" 
                                           name="end_date" 
                                           id="end_date" 
                                           x-model="form.end_date"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="deadline" class="block text-sm font-medium text-gray-700">
                                        Deadline Crítico
                                    </label>
                                    <input type="date" 
                                           name="deadline" 
                                           id="deadline" 
                                           x-model="form.deadline"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <!-- Project Manager -->
                                <div class="sm:col-span-3">
                                    <label for="project_manager_id" class="block text-sm font-medium text-gray-700">
                                        Gerente del Proyecto <span class="text-red-500">*</span>
                                    </label>
                                    <select name="project_manager_id" 
                                            id="project_manager_id" 
                                            x-model="form.project_manager_id"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Seleccionar gerente...</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}">
                                                {{ $manager->first_name }} {{ $manager->last_name }} ({{ ucfirst($manager->role) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Grupo/Equipo -->
                                <div class="sm:col-span-3">
                                    <label for="group_id" class="block text-sm font-medium text-gray-700">
                                        Grupo/Equipo Base
                                    </label>
                                    <select name="group_id" 
                                            id="group_id" 
                                            x-model="form.group_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Sin grupo asignado</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- URLs de Recursos -->
                                <div class="sm:col-span-3">
                                    <label for="repository_url" class="block text-sm font-medium text-gray-700">
                                        URL del Repositorio
                                    </label>
                                    <input type="url" 
                                           name="repository_url" 
                                           id="repository_url" 
                                           x-model="form.repository_url"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="documentation_url" class="block text-sm font-medium text-gray-700">
                                        URL de Documentación
                                    </label>
                                    <input type="url" 
                                           name="documentation_url" 
                                           id="documentation_url" 
                                           x-model="form.documentation_url"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-4 py-4 sm:px-6 bg-gray-50 rounded-b-lg">
                        <div class="flex justify-between">
                            <div>
                                @can('delete', $project)
                                    <button type="button" 
                                            @click="deleteProject()"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        Eliminar Proyecto
                                    </button>
                                @endcan
                            </div>

                            <div class="flex space-x-3">
                                <a href="{{ route('projects.show', $project) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancelar
                                </a>

                                <button type="submit" 
                                        :disabled="loading"
                                        class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <template x-if="!loading">
                                        <span>Actualizar Proyecto</span>
                                    </template>
                                    <template x-if="loading">
                                        <span class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Actualizando...
                                        </span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editProjectForm() {
    return {
        loading: false,
        errors: {},
        form: {
            name: '{{ $project->name }}',
            description: '{{ $project->description }}',
            client_name: '{{ $project->client_name }}',
            budget: '{{ $project->budget }}',
            status: '{{ $project->status }}',
            priority: '{{ $project->priority }}',
            start_date: '{{ $project->start_date?->format('Y-m-d') }}',
            end_date: '{{ $project->end_date?->format('Y-m-d') }}',
            deadline: '{{ $project->deadline?->format('Y-m-d') }}',
            project_manager_id: '{{ $project->project_manager_id }}',
            group_id: '{{ $project->group_id }}',
            repository_url: '{{ $project->repository_url }}',
            documentation_url: '{{ $project->documentation_url }}'
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};
            
            try {
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });
                formData.append('_method', 'PUT');

                const response = await fetch('{{ route("projects.update", $project) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.href = '{{ route("projects.show", $project) }}';
                } else {
                    const result = await response.json();
                    if (result.errors) {
                        this.errors = result.errors;
                    } else {
                        alert(result.message || 'Error al actualizar el proyecto');
                    }
                }
            } catch (error) {
                console.error('Error updating project:', error);
                alert('Error al actualizar el proyecto');
            } finally {
                this.loading = false;
            }
        },

        async deleteProject() {
            if (!confirm('¿Estás seguro de que quieres eliminar este proyecto? Esta acción no se puede deshacer.')) {
                return;
            }

            if (!confirm('Esta acción eliminará permanentemente el proyecto y todos sus datos asociados. ¿Continuar?')) {
                return;
            }

            try {
                const response = await fetch('{{ route("projects.destroy", $project) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    window.location.href = '{{ route("projects.index") }}';
                } else {
                    const result = await response.json();
                    alert(result.message || 'Error al eliminar el proyecto');
                }
            } catch (error) {
                console.error('Error deleting project:', error);
                alert('Error al eliminar el proyecto');
            }
        }
    }
}
</script>
@endpush
@endsection
