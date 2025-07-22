@extends('layouts.app')

@section('title', 'Nueva Evidencia')

@section('content')
<div class="space-y-6" x-data="evidenceCreator()">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Nueva Evidencia
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Crea una nueva evidencia para investigación o auditoría
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('evidences.index') }}" 
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Volver a Evidencias
            </a>
        </div>
    </div>

    <form action="{{ route('evidences.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Información básica -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Información Básica</h3>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Título -->
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-medium leading-6 text-gray-900">
                        Título de la Evidencia *
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           required
                           value="{{ old('title') }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('title') ring-red-500 focus:ring-red-500 @enderror"
                           placeholder="Ej: Incidente de seguridad en servidor principal">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Categoría -->
                <div>
                    <label for="category" class="block text-sm font-medium leading-6 text-gray-900">
                        Categoría *
                    </label>
                    <select id="category" 
                            name="category" 
                            required
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('category') ring-red-500 focus:ring-red-500 @enderror">
                        <option value="">Seleccionar categoría</option>
                        <option value="security" {{ old('category') === 'security' ? 'selected' : '' }}>Seguridad</option>
                        <option value="investigation" {{ old('category') === 'investigation' ? 'selected' : '' }}>Investigación</option>
                        <option value="compliance" {{ old('category') === 'compliance' ? 'selected' : '' }}>Cumplimiento</option>
                        <option value="audit" {{ old('category') === 'audit' ? 'selected' : '' }}>Auditoría</option>
                        <option value="incident" {{ old('category') === 'incident' ? 'selected' : '' }}>Incidente</option>
                        <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('category')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prioridad -->
                <div>
                    <label for="priority" class="block text-sm font-medium leading-6 text-gray-900">
                        Prioridad *
                    </label>
                    <select id="priority" 
                            name="priority" 
                            required
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('priority') ring-red-500 focus:ring-red-500 @enderror">
                        <option value="">Seleccionar prioridad</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Baja</option>
                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Media</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                        <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Crítica</option>
                    </select>
                    @error('priority')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha del incidente -->
                <div>
                    <label for="incident_date" class="block text-sm font-medium leading-6 text-gray-900">
                        Fecha del Incidente
                    </label>
                    <input type="datetime-local" 
                           name="incident_date" 
                           id="incident_date"
                           value="{{ old('incident_date') }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('incident_date') ring-red-500 focus:ring-red-500 @enderror">
                    @error('incident_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación -->
                <div>
                    <label for="location" class="block text-sm font-medium leading-6 text-gray-900">
                        Ubicación
                    </label>
                    <input type="text" 
                           name="location" 
                           id="location"
                           value="{{ old('location') }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('location') ring-red-500 focus:ring-red-500 @enderror"
                           placeholder="Ej: Oficina principal, Servidor sala 2, etc.">
                    @error('location')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Descripción -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium leading-6 text-gray-900">
                    Descripción Detallada *
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          required
                          class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 @error('description') ring-red-500 focus:ring-red-500 @enderror"
                          placeholder="Describe detalladamente la evidencia, el contexto, los hechos relevantes y cualquier información importante...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Archivos adjuntos -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Archivos de Evidencia</h3>
            
            <!-- Selector de archivos existentes -->
            <div class="mb-6">
                <label class="block text-sm font-medium leading-6 text-gray-900 mb-3">
                    Seleccionar archivos existentes
                </label>
                <div class="border border-gray-300 rounded-lg p-4 max-h-60 overflow-y-auto">
                    <div class="space-y-2" x-data="{ selectedFiles: [] }">
                        @forelse($available_files as $file)
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                <input type="checkbox" 
                                       name="files[]" 
                                       value="{{ $file->id }}"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded">
                                <div class="ml-3 flex items-center flex-1">
                                    <div class="flex-shrink-0">
                                        @include('components.file-icon', ['mimeType' => $file->mime_type, 'size' => 'h-5 w-5'])
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $file->original_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $file->size_formatted }} • {{ $file->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">
                                No tienes archivos disponibles. 
                                <a href="{{ route('files.create') }}" class="text-indigo-600 hover:text-indigo-500">Sube algunos archivos primero</a>.
                            </p>
                        @endforelse
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Selecciona los archivos que forman parte de esta evidencia. Puedes seleccionar múltiples archivos.
                </p>
            </div>

            <!-- Enlace para subir nuevos archivos -->
            <div class="text-center">
                <a href="{{ route('files.create') }}" 
                   target="_blank"
                   class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Subir nuevos archivos
                </a>
                <p class="mt-2 text-sm text-gray-500">
                    Se abrirá en una nueva pestaña. Después de subir, recarga esta página para ver los nuevos archivos.
                </p>
            </div>
        </div>

        <!-- Asignación y configuración -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Asignación y Configuración</h3>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Asignar a -->
                <div>
                    <label for="assigned_to" class="block text-sm font-medium leading-6 text-gray-900">
                        Asignar a
                    </label>
                    <select id="assigned_to" 
                            name="assigned_to"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="">Sin asignar</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->role }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">
                        Opcional: Asigna esta evidencia a un usuario específico para su revisión.
                    </p>
                </div>

                <!-- Estado inicial -->
                <div>
                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">
                        Estado Inicial
                    </label>
                    <select id="status" 
                            name="status"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="under_review" {{ old('status') === 'under_review' ? 'selected' : '' }}>En Revisión</option>
                        @if(auth()->user()->role === 'admin')
                            <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Aprobado</option>
                        @endif
                    </select>
                </div>
            </div>

            <!-- Notas adicionales -->
            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">
                    Notas Adicionales
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3"
                          class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                          placeholder="Cualquier información adicional, instrucciones especiales, o contexto relevante...">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Metadatos adicionales -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Metadatos Adicionales</h3>
            
            <div class="space-y-4" x-data="metadataManager()">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-900">Campos personalizados</label>
                    <button type="button" 
                            @click="addMetadataField()"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-2 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg class="-ml-0.5 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Agregar campo
                    </button>
                </div>
                
                <div class="space-y-3">
                    <template x-for="(field, index) in metadataFields" :key="index">
                        <div class="flex gap-3 items-start">
                            <div class="flex-1">
                                <input type="text" 
                                       :name="`metadata_keys[${index}]`"
                                       x-model="field.key"
                                       placeholder="Nombre del campo"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <div class="flex-1">
                                <input type="text" 
                                       :name="`metadata_values[${index}]`"
                                       x-model="field.value"
                                       placeholder="Valor"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                            <button type="button" 
                                    @click="removeMetadataField(index)"
                                    class="inline-flex items-center rounded-md bg-red-600 px-2 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                
                <p class="text-sm text-gray-500">
                    Agrega campos personalizados para almacenar información específica de esta evidencia.
                </p>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex items-center justify-end gap-x-6">
            <a href="{{ route('evidences.index') }}" 
               class="text-sm font-semibold leading-6 text-gray-900">
                Cancelar
            </a>
            <button type="submit" 
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Crear Evidencia
            </button>
        </div>
    </form>
</div>

<script>
function evidenceCreator() {
    return {
        // Funciones principales del componente
    }
}

function metadataManager() {
    return {
        metadataFields: [],
        
        addMetadataField() {
            this.metadataFields.push({ key: '', value: '' });
        },
        
        removeMetadataField(index) {
            this.metadataFields.splice(index, 1);
        }
    }
}
</script>
@endsection
