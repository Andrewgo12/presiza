@extends('layouts.app')

@section('title', 'Registrar Tiempo')

@push('styles')
<style>
    .time-input-group {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 0.5rem;
        align-items: center;
    }
    .duration-display {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 1.25rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="min-h-full" x-data="timeLogForm()">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center">
                        <div>
                            <div class="flex items-center">
                                <a href="{{ route('time-logs.index') }}" class="text-gray-500 hover:text-gray-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                </a>
                                <h1 class="ml-3 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                                    Registrar Tiempo
                                </h1>
                            </div>
                            <dl class="mt-2 flex flex-col sm:ml-8 sm:mt-1 sm:flex-row sm:flex-wrap">
                                <dt class="sr-only">Fecha</dt>
                                <dd class="text-sm text-gray-500">{{ now()->format('l, d \d\e F \d\e Y') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Actions -->
            <div class="mb-8 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones Rápidas</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <button @click="startTimer()" 
                            :disabled="timerRunning"
                            :class="timerRunning ? 'bg-gray-300 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white shadow-sm transition-colors">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                        </svg>
                        <span x-text="timerRunning ? 'Timer Activo' : 'Iniciar Timer'"></span>
                    </button>

                    <button @click="stopTimer()" 
                            :disabled="!timerRunning"
                            :class="!timerRunning ? 'bg-gray-300 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700'"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white shadow-sm transition-colors">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z" />
                        </svg>
                        Detener Timer
                    </button>

                    <div class="flex items-center justify-center bg-white rounded-md border border-gray-300 px-4 py-2">
                        <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="duration-display text-gray-900" x-text="formatDuration(elapsedTime)"></span>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white shadow-xl rounded-lg">
                <form @submit.prevent="submitForm" class="space-y-8 divide-y divide-gray-200">
                    @csrf
                    
                    <!-- Project and Task Information -->
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Información del Trabajo</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Selecciona el proyecto y describe la tarea realizada.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <!-- Project Selection -->
                                <div class="sm:col-span-3">
                                    <label for="project_id" class="block text-sm font-medium text-gray-700">
                                        Proyecto <span class="text-red-500">*</span>
                                    </label>
                                    <select name="project_id" 
                                            id="project_id" 
                                            x-model="form.project_id"
                                            @change="loadMilestones()"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Seleccionar proyecto...</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div x-show="errors.project_id" class="mt-2 text-sm text-red-600" x-text="errors.project_id"></div>
                                </div>

                                <!-- Milestone Selection -->
                                <div class="sm:col-span-3">
                                    <label for="milestone_id" class="block text-sm font-medium text-gray-700">
                                        Milestone (Opcional)
                                    </label>
                                    <select name="milestone_id" 
                                            id="milestone_id" 
                                            x-model="form.milestone_id"
                                            :disabled="!form.project_id || milestones.length === 0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                        <option value="">Sin milestone específico</option>
                                        <template x-for="milestone in milestones" :key="milestone.id">
                                            <option :value="milestone.id" x-text="milestone.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Task Description -->
                                <div class="sm:col-span-6">
                                    <label for="task_description" class="block text-sm font-medium text-gray-700">
                                        Descripción de la Tarea <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1">
                                        <textarea name="task_description" 
                                                  id="task_description" 
                                                  rows="3" 
                                                  x-model="form.task_description"
                                                  required
                                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                  placeholder="Describe detalladamente la tarea realizada..."></textarea>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        Sé específico sobre lo que trabajaste para facilitar el seguimiento y facturación.
                                    </p>
                                    <div x-show="errors.task_description" class="mt-2 text-sm text-red-600" x-text="errors.task_description"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Information -->
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Información de Tiempo</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Registra el tiempo trabajado usando horas directas o horarios específicos.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <!-- Date -->
                                <div class="sm:col-span-2">
                                    <label for="date" class="block text-sm font-medium text-gray-700">
                                        Fecha <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" 
                                           name="date" 
                                           id="date" 
                                           x-model="form.date"
                                           :max="today"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <!-- Time Input Method Toggle -->
                                <div class="sm:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Método de Registro</label>
                                    <div class="flex space-x-4">
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   x-model="timeInputMethod" 
                                                   value="hours" 
                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                            <span class="ml-2 text-sm text-gray-700">Horas Directas</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   x-model="timeInputMethod" 
                                                   value="times" 
                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                            <span class="ml-2 text-sm text-gray-700">Hora Inicio/Fin</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Hours Input -->
                                <div x-show="timeInputMethod === 'hours'" class="sm:col-span-3">
                                    <label for="hours" class="block text-sm font-medium text-gray-700">
                                        Horas Trabajadas <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <input type="number" 
                                               name="hours" 
                                               id="hours" 
                                               x-model="form.hours"
                                               step="0.25"
                                               min="0.25"
                                               max="24"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                               placeholder="8.5">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">horas</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Mínimo 0.25 horas (15 minutos)</p>
                                </div>

                                <!-- Time Range Input -->
                                <div x-show="timeInputMethod === 'times'" class="sm:col-span-6">
                                    <div class="time-input-group">
                                        <div>
                                            <label for="start_time" class="block text-sm font-medium text-gray-700">
                                                Hora de Inicio <span class="text-red-500">*</span>
                                            </label>
                                            <input type="time" 
                                                   name="start_time" 
                                                   id="start_time" 
                                                   x-model="form.start_time"
                                                   @change="calculateHours()"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        
                                        <div class="text-center">
                                            <svg class="h-5 w-5 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </div>
                                        
                                        <div>
                                            <label for="end_time" class="block text-sm font-medium text-gray-700">
                                                Hora de Fin <span class="text-red-500">*</span>
                                            </label>
                                            <input type="time" 
                                                   name="end_time" 
                                                   id="end_time" 
                                                   x-model="form.end_time"
                                                   @change="calculateHours()"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                    
                                    <div x-show="calculatedHours > 0" class="mt-3 p-3 bg-blue-50 rounded-md">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-sm text-blue-800">
                                                Duración calculada: <span class="font-medium" x-text="formatHours(calculatedHours)"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Billable Toggle -->
                                <div class="sm:col-span-3">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="is_billable" 
                                                   name="is_billable" 
                                                   type="checkbox" 
                                                   x-model="form.is_billable"
                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="is_billable" class="font-medium text-gray-700">
                                                Tiempo facturable
                                            </label>
                                            <p class="text-gray-500">Este tiempo puede ser facturado al cliente</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="sm:col-span-6">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">
                                        Notas Adicionales
                                    </label>
                                    <div class="mt-1">
                                        <textarea name="notes" 
                                                  id="notes" 
                                                  rows="3" 
                                                  x-model="form.notes"
                                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                  placeholder="Notas adicionales, obstáculos encontrados, próximos pasos..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-4 py-4 sm:px-6 bg-gray-50 rounded-b-lg">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('time-logs.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancelar
                            </a>

                            <button type="submit" 
                                    :disabled="loading || !isFormValid()"
                                    class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="!loading">
                                    <span>Registrar Tiempo</span>
                                </template>
                                <template x-if="loading">
                                    <span class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Registrando...
                                    </span>
                                </template>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function timeLogForm() {
    return {
        loading: false,
        errors: {},
        timeInputMethod: 'hours',
        calculatedHours: 0,
        milestones: [],
        timerRunning: false,
        timerStart: null,
        elapsedTime: 0,
        timerInterval: null,
        today: new Date().toISOString().split('T')[0],
        form: {
            project_id: '{{ request("project_id") }}',
            milestone_id: '',
            task_description: '',
            date: new Date().toISOString().split('T')[0],
            hours: '',
            start_time: '',
            end_time: '',
            is_billable: true,
            notes: ''
        },

        init() {
            if (this.form.project_id) {
                this.loadMilestones();
            }
            this.loadTimerState();
        },

        async loadMilestones() {
            if (!this.form.project_id) {
                this.milestones = [];
                return;
            }

            try {
                const response = await fetch(`/api/projects/${this.form.project_id}/milestones`);
                this.milestones = await response.json();
            } catch (error) {
                console.error('Error loading milestones:', error);
                this.milestones = [];
            }
        },

        calculateHours() {
            if (!this.form.start_time || !this.form.end_time) {
                this.calculatedHours = 0;
                return;
            }

            const start = new Date(`2000-01-01T${this.form.start_time}:00`);
            const end = new Date(`2000-01-01T${this.form.end_time}:00`);
            
            if (end <= start) {
                this.calculatedHours = 0;
                return;
            }

            const diffMs = end - start;
            this.calculatedHours = diffMs / (1000 * 60 * 60);
        },

        formatHours(hours) {
            const h = Math.floor(hours);
            const m = Math.round((hours - h) * 60);
            return `${h}h ${m}m`;
        },

        formatDuration(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        startTimer() {
            this.timerRunning = true;
            this.timerStart = Date.now();
            this.elapsedTime = 0;
            
            this.timerInterval = setInterval(() => {
                this.elapsedTime = Math.floor((Date.now() - this.timerStart) / 1000);
            }, 1000);

            this.saveTimerState();
        },

        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
            
            if (this.timerRunning && this.elapsedTime > 0) {
                const hours = this.elapsedTime / 3600;
                this.form.hours = Math.round(hours * 4) / 4; // Round to nearest 0.25
                this.timeInputMethod = 'hours';
            }
            
            this.timerRunning = false;
            this.clearTimerState();
        },

        saveTimerState() {
            localStorage.setItem('timeLogTimer', JSON.stringify({
                running: this.timerRunning,
                start: this.timerStart,
                projectId: this.form.project_id
            }));
        },

        loadTimerState() {
            const saved = localStorage.getItem('timeLogTimer');
            if (saved) {
                const state = JSON.parse(saved);
                if (state.running && state.start) {
                    this.timerRunning = true;
                    this.timerStart = state.start;
                    this.elapsedTime = Math.floor((Date.now() - state.start) / 1000);
                    
                    this.timerInterval = setInterval(() => {
                        this.elapsedTime = Math.floor((Date.now() - this.timerStart) / 1000);
                    }, 1000);

                    if (state.projectId) {
                        this.form.project_id = state.projectId;
                        this.loadMilestones();
                    }
                }
            }
        },

        clearTimerState() {
            localStorage.removeItem('timeLogTimer');
        },

        isFormValid() {
            return this.form.project_id && 
                   this.form.task_description && 
                   this.form.date && 
                   ((this.timeInputMethod === 'hours' && this.form.hours) || 
                    (this.timeInputMethod === 'times' && this.form.start_time && this.form.end_time));
        },

        async submitForm() {
            if (!this.isFormValid()) {
                return;
            }

            this.loading = true;
            this.errors = {};
            
            try {
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                // If using time range, calculate and set hours
                if (this.timeInputMethod === 'times' && this.calculatedHours > 0) {
                    formData.set('hours', this.calculatedHours);
                }

                const response = await fetch('{{ route("time-logs.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.clearTimerState();
                    const result = await response.json();
                    window.location.href = result.redirect || '{{ route("time-logs.index") }}';
                } else {
                    const result = await response.json();
                    if (result.errors) {
                        this.errors = result.errors;
                    } else {
                        alert(result.message || 'Error al registrar el tiempo');
                    }
                }
            } catch (error) {
                console.error('Error creating time log:', error);
                alert('Error al registrar el tiempo');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection
