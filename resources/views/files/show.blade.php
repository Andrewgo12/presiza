@extends('layouts.app')

@section('title', $file->original_name)

@section('content')
<div class="space-y-6" x-data="fileViewer()">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <nav class="flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="{{ route('files.index') }}" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 18H3.75c-.621 0-1.125-.504-1.125-1.125V1.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v4.5c0 .621.504 1.125 1.125 1.125h4.125c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125H16.5a1.125 1.125 0 01-1.125-1.125v-1.5a1.125 1.125 0 00-1.125-1.125H12" />
                                </svg>
                                <span class="sr-only">Archivos</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500 truncate">{{ $file->original_name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                {{ $file->original_name }}
            </h1>
        </div>
        <div class="mt-4 flex space-x-3 md:ml-4 md:mt-0">
            <button @click="toggleFavorite()" 
                    :class="isFavorite ? 'bg-yellow-50 text-yellow-600 ring-yellow-200' : 'bg-white text-gray-700 ring-gray-300'"
                    class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset hover:bg-gray-50">
                <svg class="h-5 w-5 mr-2" :class="isFavorite ? 'fill-current' : 'fill-none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.563.563 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                <span x-text="isFavorite ? 'Favorito' : 'Agregar a Favoritos'"></span>
            </button>
            <a href="{{ route('files.download', $file) }}" 
               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Descargar
            </a>
            @can('update', $file)
                <a href="{{ route('files.edit', $file) }}" 
                   class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    Editar
                </a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Previsualización del archivo -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Previsualización</h3>
                </div>
                <div class="p-6">
                    @if($file->can_preview)
                        @if(str_starts_with($file->mime_type, 'image/'))
                            <!-- Previsualización de imagen -->
                            <div class="text-center">
                                <img src="{{ $file->preview_url }}" 
                                     alt="{{ $file->original_name }}"
                                     class="max-w-full h-auto rounded-lg shadow-lg mx-auto"
                                     style="max-height: 500px;">
                            </div>
                        @elseif($file->mime_type === 'application/pdf')
                            <!-- Previsualización de PDF -->
                            <div class="w-full h-96">
                                <iframe src="{{ $file->preview_url }}" 
                                        class="w-full h-full border rounded-lg"
                                        frameborder="0">
                                    <p>Tu navegador no soporta la previsualización de PDF. 
                                       <a href="{{ route('files.download', $file) }}" class="text-indigo-600 hover:text-indigo-500">Descargar archivo</a>
                                    </p>
                                </iframe>
                            </div>
                        @elseif(str_starts_with($file->mime_type, 'text/'))
                            <!-- Previsualización de texto -->
                            <div class="bg-gray-50 rounded-lg p-4 overflow-auto max-h-96">
                                <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ Storage::get($file->path) }}</pre>
                            </div>
                        @endif
                    @else
                        <!-- No se puede previsualizar -->
                        <div class="text-center py-12">
                            <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                                @include('components.file-icon', ['mimeType' => $file->mime_type, 'size' => 'h-24 w-24'])
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $file->original_name }}</h3>
                            <p class="text-sm text-gray-500 mb-4">Este tipo de archivo no se puede previsualizar</p>
                            <a href="{{ route('files.download', $file) }}" 
                               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Descargar para ver
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Información del archivo -->
        <div class="space-y-6">
            <!-- Detalles básicos -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Detalles del Archivo</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre original</dt>
                        <dd class="mt-1 text-sm text-gray-900 break-all">{{ $file->original_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tamaño</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $file->size_formatted }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $file->mime_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 capitalize">
                                {{ $file->category }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nivel de acceso</dt>
                        <dd class="mt-1">
                            @php
                                $accessColors = [
                                    'public' => 'bg-green-100 text-green-800',
                                    'internal' => 'bg-blue-100 text-blue-800',
                                    'restricted' => 'bg-yellow-100 text-yellow-800',
                                    'confidential' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $accessColors[$file->access_level] ?? 'bg-gray-100 text-gray-800' }} capitalize">
                                {{ $file->access_level }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Subido por</dt>
                        <dd class="mt-1 flex items-center text-sm text-gray-900">
                            <img class="h-6 w-6 rounded-full mr-2" 
                                 src="{{ $file->uploader->avatar ? Storage::url($file->uploader->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($file->uploader->first_name . ' ' . $file->uploader->last_name) . '&color=7F9CF5&background=EBF4FF' }}" 
                                 alt="{{ $file->uploader->first_name }} {{ $file->uploader->last_name }}">
                            {{ $file->uploader->first_name }} {{ $file->uploader->last_name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de subida</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $file->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($file->expires_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Expira</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $file->expires_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Estadísticas</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Visualizaciones</dt>
                        <dd class="text-sm text-gray-900">{{ $file->view_count }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Descargas</dt>
                        <dd class="text-sm text-gray-900">{{ $file->download_count }}</dd>
                    </div>
                </div>
            </div>

            <!-- Etiquetas -->
            @if($file->tags && count($file->tags) > 0)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Etiquetas</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex flex-wrap gap-2">
                            @foreach($file->tags as $tag)
                                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Descripción -->
            @if($file->description)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Descripción</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-sm text-gray-700">{{ $file->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Acciones -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Acciones</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <button @click="shareFile()" 
                            class="w-full inline-flex justify-center items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.935-2.186 2.25 2.25 0 00-3.935 2.186z" />
                        </svg>
                        Compartir
                    </button>
                    <button @click="copyLink()" 
                            class="w-full inline-flex justify-center items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                        </svg>
                        Copiar enlace
                    </button>
                    @can('delete', $file)
                        <button @click="deleteFile()" 
                                class="w-full inline-flex justify-center items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Eliminar
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fileViewer() {
    return {
        isFavorite: false,
        
        init() {
            // Cargar estado de favorito desde localStorage o API
            this.isFavorite = localStorage.getItem('favorite_{{ $file->id }}') === 'true';
        },
        
        toggleFavorite() {
            this.isFavorite = !this.isFavorite;
            localStorage.setItem('favorite_{{ $file->id }}', this.isFavorite);
            
            // Aquí podrías hacer una llamada AJAX para guardar en la base de datos
            fetch(`/api/files/{{ $file->id }}/toggle-favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });
        },
        
        shareFile() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $file->original_name }}',
                    text: 'Compartir archivo: {{ $file->original_name }}',
                    url: window.location.href
                });
            } else {
                this.copyLink();
            }
        },
        
        copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Enlace copiado al portapapeles');
            });
        },
        
        deleteFile() {
            if (confirm('¿Estás seguro de que quieres eliminar este archivo? Esta acción no se puede deshacer.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("files.destroy", $file) }}';
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                form.appendChild(methodInput);
                form.appendChild(tokenInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}
</script>
@endsection
