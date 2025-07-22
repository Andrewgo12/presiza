@extends('layouts.app')

@section('title', 'Subir Archivo')

@section('content')
<div class="space-y-6" x-data="fileUploader()">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Subir Archivo
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Sube y organiza tus archivos y documentos
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('files.index') }}" 
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Volver a Archivos
            </a>
        </div>
    </div>

    <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- Zona de subida -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Seleccionar Archivos</h3>
            
            <!-- Drag & Drop Zone -->
            <div class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10"
                 x-ref="dropzone"
                 @dragover.prevent="dragover = true"
                 @dragleave.prevent="dragover = false"
                 @drop.prevent="handleDrop($event)"
                 :class="{ 'border-indigo-500 bg-indigo-50': dragover }">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0119.5 6v6a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 12V6zM3 16.06V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                    </svg>
                    <div class="mt-4 flex text-sm leading-6 text-gray-600">
                        <label for="files" class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                            <span>Subir archivos</span>
                            <input id="files" 
                                   name="files[]" 
                                   type="file" 
                                   class="sr-only" 
                                   multiple
                                   @change="handleFileSelect($event)"
                                   accept="{{ config('filesystems.allowed_types') }}">
                        </label>
                        <p class="pl-1">o arrastra y suelta aquí</p>
                    </div>
                    <p class="text-xs leading-5 text-gray-600">PNG, JPG, PDF, DOC hasta {{ config('filesystems.max_file_size_mb') }}MB cada uno</p>
                </div>
            </div>

            <!-- Lista de archivos seleccionados -->
            <div x-show="selectedFiles.length > 0" class="mt-6">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Archivos seleccionados</h4>
                <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                    <template x-for="(file, index) in selectedFiles" :key="index">
                        <li class="flex items-center justify-between py-3 pl-3 pr-4 text-sm">
                            <div class="flex w-0 flex-1 items-center">
                                <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-2 flex-1 w-0 truncate" x-text="file.name"></span>
                                <span class="ml-2 text-gray-500" x-text="formatFileSize(file.size)"></span>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <button type="button" 
                                        @click="removeFile(index)"
                                        class="font-medium text-red-600 hover:text-red-500">
                                    Eliminar
                                </button>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <!-- Información del archivo -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Archivo</h3>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Categoría -->
                <div>
                    <label for="category" class="block text-sm font-medium leading-6 text-gray-900">
                        Categoría
                    </label>
                    <select id="category" 
                            name="category" 
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="document">Documento</option>
                        <option value="image">Imagen</option>
                        <option value="video">Video</option>
                        <option value="audio">Audio</option>
                        <option value="archive">Archivo comprimido</option>
                        <option value="other">Otro</option>
                    </select>
                </div>

                <!-- Nivel de acceso -->
                <div>
                    <label for="access_level" class="block text-sm font-medium leading-6 text-gray-900">
                        Nivel de acceso
                    </label>
                    <select id="access_level" 
                            name="access_level" 
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="internal">Interno</option>
                        <option value="public">Público</option>
                        <option value="restricted">Restringido</option>
                        <option value="confidential">Confidencial</option>
                    </select>
                </div>
            </div>

            <!-- Descripción -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium leading-6 text-gray-900">
                    Descripción
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3" 
                          class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                          placeholder="Describe el contenido del archivo..."></textarea>
            </div>

            <!-- Etiquetas -->
            <div class="mt-6">
                <label for="tags" class="block text-sm font-medium leading-6 text-gray-900">
                    Etiquetas
                </label>
                <div class="mt-2" x-data="tagManager()">
                    <div class="flex flex-wrap gap-2 mb-2" x-show="tags.length > 0">
                        <template x-for="(tag, index) in tags" :key="index">
                            <span class="inline-flex items-center gap-x-0.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                <span x-text="tag"></span>
                                <button type="button" @click="removeTag(index)" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-gray-500/20">
                                    <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-gray-600/50 group-hover:stroke-gray-600/75">
                                        <path d="m4 4 6 6m0-6-6 6" />
                                    </svg>
                                </button>
                            </span>
                        </template>
                    </div>
                    <input type="text" 
                           x-model="newTag"
                           @keydown.enter.prevent="addTag()"
                           @keydown.comma.prevent="addTag()"
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                           placeholder="Escribe una etiqueta y presiona Enter...">
                    <input type="hidden" name="tags" :value="JSON.stringify(tags)">
                    <p class="mt-1 text-sm text-gray-500">Presiona Enter o coma para agregar etiquetas</p>
                </div>
            </div>
        </div>

        <!-- Configuraciones adicionales -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuraciones</h3>
            
            <div class="space-y-4">
                <!-- Archivo público -->
                <div class="flex items-center">
                    <input id="is_public" 
                           name="is_public" 
                           type="checkbox" 
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                    <label for="is_public" class="ml-3 block text-sm leading-6 text-gray-900">
                        Hacer público (visible para todos los usuarios)
                    </label>
                </div>

                <!-- Fecha de expiración -->
                <div>
                    <label for="expires_at" class="block text-sm font-medium leading-6 text-gray-900">
                        Fecha de expiración (opcional)
                    </label>
                    <input type="datetime-local" 
                           id="expires_at" 
                           name="expires_at" 
                           class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <p class="mt-1 text-sm text-gray-500">El archivo se eliminará automáticamente en esta fecha</p>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex items-center justify-end gap-x-6">
            <a href="{{ route('files.index') }}" 
               class="text-sm font-semibold leading-6 text-gray-900">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="selectedFiles.length === 0 || uploading"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!uploading">Subir Archivos</span>
                <span x-show="uploading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Subiendo...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
function fileUploader() {
    return {
        selectedFiles: [],
        dragover: false,
        uploading: false,
        
        handleFileSelect(event) {
            this.addFiles(Array.from(event.target.files));
        },
        
        handleDrop(event) {
            this.dragover = false;
            this.addFiles(Array.from(event.dataTransfer.files));
        },
        
        addFiles(files) {
            files.forEach(file => {
                if (this.validateFile(file)) {
                    this.selectedFiles.push(file);
                }
            });
        },
        
        validateFile(file) {
            const maxSize = {{ config('filesystems.max_file_size', 2048) }} * 1024 * 1024; // MB to bytes
            const allowedTypes = {!! json_encode(explode(',', config('filesystems.allowed_types', 'jpg,jpeg,png,gif,pdf,doc,docx'))) !!};
            
            if (file.size > maxSize) {
                alert(`El archivo ${file.name} es demasiado grande. Máximo {{ config('filesystems.max_file_size', 2) }}MB.`);
                return false;
            }
            
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(extension)) {
                alert(`Tipo de archivo no permitido: ${extension}`);
                return false;
            }
            
            return true;
        },
        
        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
}

function tagManager() {
    return {
        tags: [],
        newTag: '',
        
        addTag() {
            const tag = this.newTag.trim().toLowerCase();
            if (tag && !this.tags.includes(tag)) {
                this.tags.push(tag);
                this.newTag = '';
            }
        },
        
        removeTag(index) {
            this.tags.splice(index, 1);
        }
    }
}
</script>
@endsection
