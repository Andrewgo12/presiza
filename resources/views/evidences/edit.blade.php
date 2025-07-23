@extends('layouts.app')

@section('title', 'Editar Evidencia')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <div>
                                <a href="{{ route('evidences.index') }}" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                    </svg>
                                    <span class="sr-only">Evidencias</span>
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <a href="{{ route('evidences.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Evidencias</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <a href="{{ route('evidences.show', $evidence) }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ $evidence->title }}</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-4 text-sm font-medium text-gray-500">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                    Editar Evidencia
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Modifica los detalles de la evidencia
                </p>
            </div>
        </div>

        <!-- Form -->
        <div class="mt-8">
            <form method="POST" action="{{ route('evidences.update', $evidence) }}" class="space-y-6">
                @csrf
                @method('PATCH')
                
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Title -->
                            <div class="sm:col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-700">Título *</label>
                                <input type="text" 
                                       name="title" 
                                       id="title" 
                                       value="{{ old('title', $evidence->title) }}"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('title') border-red-300 @enderror">
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Categoría *</label>
                                <select name="category" 
                                        id="category" 
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('category') border-red-300 @enderror">
                                    <option value="">Seleccionar categoría</option>
                                    <option value="security" {{ old('category', $evidence->category) === 'security' ? 'selected' : '' }}>Seguridad</option>
                                    <option value="investigation" {{ old('category', $evidence->category) === 'investigation' ? 'selected' : '' }}>Investigación</option>
                                    <option value="compliance" {{ old('category', $evidence->category) === 'compliance' ? 'selected' : '' }}>Cumplimiento</option>
                                    <option value="audit" {{ old('category', $evidence->category) === 'audit' ? 'selected' : '' }}>Auditoría</option>
                                    <option value="incident" {{ old('category', $evidence->category) === 'incident' ? 'selected' : '' }}>Incidente</option>
                                    <option value="other" {{ old('category', $evidence->category) === 'other' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('category')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700">Prioridad *</label>
                                <select name="priority" 
                                        id="priority" 
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('priority') border-red-300 @enderror">
                                    <option value="">Seleccionar prioridad</option>
                                    <option value="low" {{ old('priority', $evidence->priority) === 'low' ? 'selected' : '' }}>Baja</option>
                                    <option value="medium" {{ old('priority', $evidence->priority) === 'medium' ? 'selected' : '' }}>Media</option>
                                    <option value="high" {{ old('priority', $evidence->priority) === 'high' ? 'selected' : '' }}>Alta</option>
                                    <option value="critical" {{ old('priority', $evidence->priority) === 'critical' ? 'selected' : '' }}>Crítica</option>
                                </select>
                                @error('priority')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Assigned To -->
                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Asignar a</label>
                                <select name="assigned_to" 
                                        id="assigned_to"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('assigned_to') border-red-300 @enderror">
                                    <option value="">Sin asignar</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to', $evidence->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }} ({{ $user->role }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Incident Date -->
                            <div>
                                <label for="incident_date" class="block text-sm font-medium text-gray-700">Fecha del incidente</label>
                                <input type="date" 
                                       name="incident_date" 
                                       id="incident_date" 
                                       value="{{ old('incident_date', $evidence->incident_date?->format('Y-m-d')) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('incident_date') border-red-300 @enderror">
                                @error('incident_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div class="sm:col-span-2">
                                <label for="location" class="block text-sm font-medium text-gray-700">Ubicación</label>
                                <input type="text" 
                                       name="location" 
                                       id="location" 
                                       value="{{ old('location', $evidence->location) }}"
                                       placeholder="Ubicación donde ocurrió el incidente"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('location') border-red-300 @enderror">
                                @error('location')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="sm:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">Descripción *</label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="4" 
                                          required
                                          placeholder="Describe detalladamente la evidencia..."
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description', $evidence->description) }}</textarea>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="sm:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notas adicionales</label>
                                <textarea name="notes" 
                                          id="notes" 
                                          rows="3" 
                                          placeholder="Notas adicionales o comentarios..."
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('notes') border-red-300 @enderror">{{ old('notes', $evidence->notes) }}</textarea>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files Section -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Archivos adjuntos</h3>
                        
                        <!-- Current files -->
                        @if($evidence->files->count() > 0)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Archivos actuales</h4>
                                <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                                    @foreach($evidence->files as $file)
                                        <li class="py-3 px-4 flex items-center justify-between">
                                            <div class="flex items-center">
                                                <x-file-icon :type="$file->mime_type" class="h-8 w-8 mr-3" />
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $file->original_name }}</p>
                                                    <p class="text-sm text-gray-500">{{ $file->file_size_human }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <input type="checkbox" 
                                                       name="files[]" 
                                                       value="{{ $file->id }}" 
                                                       checked
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <label class="text-sm text-gray-700">Mantener</label>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Available files -->
                        @if($available_files->count() > 0)
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Archivos disponibles para adjuntar</h4>
                                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md">
                                    @foreach($available_files as $file)
                                        @if(!$evidence->files->contains($file->id))
                                            <div class="py-3 px-4 flex items-center justify-between hover:bg-gray-50">
                                                <div class="flex items-center">
                                                    <x-file-icon :type="$file->mime_type" class="h-8 w-8 mr-3" />
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $file->original_name }}</p>
                                                        <p class="text-sm text-gray-500">{{ $file->file_size_human }} • {{ $file->created_at->format('d/m/Y') }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <input type="checkbox" 
                                                           name="files[]" 
                                                           value="{{ $file->id }}"
                                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <label class="text-sm text-gray-700">Adjuntar</label>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($available_files->count() === 0 && $evidence->files->count() === 0)
                            <p class="text-sm text-gray-500">No hay archivos disponibles. <a href="{{ route('files.create') }}" class="text-indigo-600 hover:text-indigo-500">Subir archivos</a></p>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('evidences.show', $evidence) }}" 
                       class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Actualizar Evidencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
