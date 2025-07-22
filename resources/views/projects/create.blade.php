@extends('layouts.app')

@section('title', 'Crear Proyecto')

@push('styles')
<style>
    .form-step {
        display: none;
    }
    .form-step.active {
        display: block;
    }
    .step-indicator {
        @apply flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium;
    }
    .step-indicator.completed {
        @apply bg-green-500 text-white;
    }
    .step-indicator.active {
        @apply bg-indigo-600 text-white;
    }
    .step-indicator.pending {
        @apply bg-gray-200 text-gray-600;
    }
</style>
@endpush

@section('content')
<div class="min-h-full" x-data="projectForm()">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center">
                        <div>
                            <div class="flex items-center">
                                <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                </a>
                                <h1 class="ml-3 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                                    Crear Nuevo Proyecto
                                </h1>
                            </div>
                            <dl class="mt-2 flex flex-col sm:ml-8 sm:mt-1 sm:flex-row sm:flex-wrap">
                                <dt class="sr-only">Paso</dt>
                                <dd class="text-sm text-gray-500">
                                    Paso <span x-text="currentStep"></span> de 3 - <span x-text="stepTitles[currentStep - 1]"></span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Progress Steps -->
            <nav aria-label="Progress" class="mb-8">
                <ol role="list" class="flex items-center">
                    <template x-for="(step, index) in stepTitles" :key="index">
                        <li class="relative" :class="index < stepTitles.length - 1 ? 'pr-8 sm:pr-20' : ''">
                            <!-- Step Connector -->
                            <template x-if="index < stepTitles.length - 1">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="h-0.5 w-full" :class="index + 1 < currentStep ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                                </div>
                            </template>
                            
                            <!-- Step Circle -->
                            <div class="relative flex h-8 w-8 items-center justify-center rounded-full" 
                                 :class="getStepClass(index + 1)">
                                <template x-if="index + 1 < currentStep">
                                    <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template x-if="index + 1 >= currentStep">
                                    <span class="text-sm font-medium" x-text="index + 1"></span>
                                </template>
                            </div>
                            
                            <!-- Step Label -->
                            <div class="absolute top-10 left-1/2 transform -translate-x-1/2">
                                <span class="text-xs font-medium text-gray-500" x-text="step"></span>
                            </div>
                        </li>
                    </template>
                </ol>
            </nav>

            <!-- Form Card -->
            <div class="bg-white shadow-xl rounded-lg">
                <form @submit.prevent="submitForm" class="space-y-8 divide-y divide-gray-200">
                    @csrf
                    
                    <!-- Step 1: Información Básica -->
                    <div class="form-step" :class="{ 'active': currentStep === 1 }">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Información Básica del Proyecto</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Proporciona los detalles fundamentales del proyecto que vas a crear.
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
                                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="Ej: Sistema de Gestión de Inventarios">
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Nombre descriptivo y único para identificar el proyecto.
                                        </p>
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
                                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                      placeholder="Describe los objetivos, alcance y características principales del proyecto..."></textarea>
                                        </div>
                                    </div>

                                    <!-- Cliente -->
                                    <div class="sm:col-span-3">
                                        <label for="client_name" class="block text-sm font-medium text-gray-700">
                                            Cliente/Organización
                                        </label>
                                        <div class="mt-1">
                                            <input type="text" 
                                                   name="client_name" 
                                                   id="client_name" 
                                                   x-model="form.client_name"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="Nombre del cliente o empresa">
                                        </div>
                                    </div>

                                    <!-- Presupuesto -->
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
                                                   class="block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="0.00">
                                        </div>
                                    </div>

                                    <!-- Estado y Prioridad -->
                                    <div class="sm:col-span-3">
                                        <label for="status" class="block text-sm font-medium text-gray-700">
                                            Estado Inicial <span class="text-red-500">*</span>
                                        </label>
                                        <select name="status" 
                                                id="status" 
                                                x-model="form.status"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="planning">Planificación</option>
                                            <option value="in_progress">En Progreso</option>
                                            <option value="on_hold">En Pausa</option>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Fechas y Gestión -->
                    <div class="form-step" :class="{ 'active': currentStep === 2 }">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Cronograma y Gestión</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Define las fechas importantes y asigna el equipo de gestión del proyecto.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Recursos y Configuración -->
                    <div class="form-step" :class="{ 'active': currentStep === 3 }">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Recursos y Configuración</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Configura los recursos técnicos y herramientas del proyecto.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <!-- URLs de Recursos -->
                                    <div class="sm:col-span-3">
                                        <label for="repository_url" class="block text-sm font-medium text-gray-700">
                                            URL del Repositorio
                                        </label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                            <input type="url" 
                                                   name="repository_url" 
                                                   id="repository_url" 
                                                   x-model="form.repository_url"
                                                   class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="https://github.com/empresa/proyecto">
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label for="documentation_url" class="block text-sm font-medium text-gray-700">
                                            URL de Documentación
                                        </label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            </span>
                                            <input type="url" 
                                                   name="documentation_url" 
                                                   id="documentation_url" 
                                                   x-model="form.documentation_url"
                                                   class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                   placeholder="https://docs.empresa.com/proyecto">
                                        </div>
                                    </div>

                                    <!-- Configuraciones Adicionales -->
                                    <div class="sm:col-span-6">
                                        <fieldset>
                                            <legend class="text-sm font-medium text-gray-700">Configuraciones del Proyecto</legend>
                                            <div class="mt-4 space-y-4">
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5">
                                                        <input id="require_time_tracking" 
                                                               name="require_time_tracking" 
                                                               type="checkbox" 
                                                               x-model="form.require_time_tracking"
                                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="require_time_tracking" class="font-medium text-gray-700">
                                                            Requerir registro de tiempo
                                                        </label>
                                                        <p class="text-gray-500">Los miembros deben registrar el tiempo trabajado en este proyecto.</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5">
                                                        <input id="notifications_enabled" 
                                                               name="notifications_enabled" 
                                                               type="checkbox" 
                                                               x-model="form.notifications_enabled"
                                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="notifications_enabled" class="font-medium text-gray-700">
                                                            Notificaciones automáticas
                                                        </label>
                                                        <p class="text-gray-500">Enviar notificaciones sobre deadlines, actualizaciones y cambios importantes.</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5">
                                                        <input id="auto_assign_tasks" 
                                                               name="auto_assign_tasks" 
                                                               type="checkbox" 
                                                               x-model="form.auto_assign_tasks"
                                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="auto_assign_tasks" class="font-medium text-gray-700">
                                                            Asignación automática de tareas
                                                        </label>
                                                        <p class="text-gray-500">Distribuir automáticamente las tareas entre los miembros del equipo.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-4 py-4 sm:px-6 bg-gray-50 rounded-b-lg">
                        <div class="flex justify-between">
                            <button type="button" 
                                    @click="previousStep()" 
                                    x-show="currentStep > 1"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                </svg>
                                Anterior
                            </button>

                            <div class="flex space-x-3">
                                <a href="{{ route('projects.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancelar
                                </a>

                                <button type="button" 
                                        @click="nextStep()" 
                                        x-show="currentStep < 3"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Siguiente
                                    <svg class="ml-2 -mr-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </button>

                                <button type="submit" 
                                        x-show="currentStep === 3"
                                        :disabled="loading"
                                        class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <template x-if="!loading">
                                        <span>Crear Proyecto</span>
                                    </template>
                                    <template x-if="loading">
                                        <span class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Creando...
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
function projectForm() {
    return {
        currentStep: 1,
        loading: false,
        stepTitles: ['Información Básica', 'Cronograma y Gestión', 'Recursos y Configuración'],
        form: {
            name: '',
            description: '',
            client_name: '',
            budget: '',
            status: 'planning',
            priority: 'medium',
            start_date: '',
            end_date: '',
            deadline: '',
            project_manager_id: '',
            group_id: '',
            repository_url: '',
            documentation_url: '',
            require_time_tracking: true,
            notifications_enabled: true,
            auto_assign_tasks: false
        },

        nextStep() {
            if (this.validateCurrentStep()) {
                this.currentStep++;
            }
        },

        previousStep() {
            this.currentStep--;
        },

        validateCurrentStep() {
            if (this.currentStep === 1) {
                return this.form.name && this.form.status && this.form.priority;
            }
            if (this.currentStep === 2) {
                return this.form.project_manager_id;
            }
            return true;
        },

        getStepClass(step) {
            if (step < this.currentStep) {
                return 'bg-indigo-600 text-white';
            } else if (step === this.currentStep) {
                return 'border-2 border-indigo-600 text-indigo-600 bg-white';
            } else {
                return 'border-2 border-gray-300 text-gray-500 bg-white';
            }
        },

        async submitForm() {
            if (!this.validateCurrentStep()) {
                return;
            }

            this.loading = true;
            
            try {
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                const response = await fetch('{{ route("projects.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    window.location.href = result.redirect || '{{ route("projects.index") }}';
                } else {
                    const errors = await response.json();
                    console.error('Validation errors:', errors);
                    // Handle validation errors
                }
            } catch (error) {
                console.error('Error creating project:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection
