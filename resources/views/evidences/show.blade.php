@extends('layouts.app')

@section('title', 'Evidencia: ' . $evidence->title)

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
                                <span class="ml-4 text-sm font-medium text-gray-500">{{ $evidence->title }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                    {{ $evidence->title }}
                </h2>
                
                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Enviado por {{ $evidence->submittedBy->full_name ?? 'Usuario eliminado' }}
                    </div>
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 005.25 9h13.5a2.25 2.25 0 002.25 2.25v7.5" />
                        </svg>
                        {{ $evidence->created_at->format('d/m/Y H:i') }}
                    </div>
                    @if($evidence->case_number)
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5l-3.9 19.5m-2.1-19.5l-3.9 19.5" />
                            </svg>
                            Caso: {{ $evidence->case_number }}
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-4 flex md:mt-0 md:ml-4">
                @can('update', $evidence)
                    <a href="{{ route('evidences.edit', $evidence) }}" 
                       class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        Editar
                    </a>
                @endcan
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main content -->
            <div class="lg:col-span-2">
                <!-- Evidence details -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $evidence->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                           ($evidence->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($evidence->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($evidence->status) }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Prioridad</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $evidence->priority === 'high' ? 'bg-red-100 text-red-800' : 
                                           ($evidence->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                        {{ ucfirst($evidence->priority) }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($evidence->category) }}</dd>
                            </div>
                            
                            @if($evidence->assignedTo)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Asignado a</dt>
                                    <dd class="mt-1 flex items-center">
                                        <img class="h-6 w-6 rounded-full mr-2" 
                                             src="{{ $evidence->assignedTo->avatar_url }}" 
                                             alt="{{ $evidence->assignedTo->full_name }}">
                                        <span class="text-sm text-gray-900">{{ $evidence->assignedTo->full_name }}</span>
                                    </dd>
                                </div>
                            @endif
                            
                            @if($evidence->incident_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fecha del incidente</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $evidence->incident_date->format('d/m/Y') }}</dd>
                                </div>
                            @endif
                            
                            @if($evidence->location)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Ubicación</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $evidence->location }}</dd>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-6">
                            <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $evidence->description }}</dd>
                        </div>
                        
                        @if($evidence->notes)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500">Notas</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $evidence->notes }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Files -->
                @if($evidence->files->count() > 0)
                    <div class="mt-6 bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Archivos adjuntos ({{ $evidence->files->count() }})
                            </h3>
                            
                            <ul class="divide-y divide-gray-200">
                                @foreach($evidence->files as $file)
                                    <li class="py-3 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <x-file-icon :type="$file->mime_type" class="h-8 w-8 mr-3" />
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $file->original_name }}</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ $file->file_size_human }} • {{ $file->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ route('files.show', $file) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Ver
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Actions -->
                @can('updateStatus', $evidence)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Acciones</h3>
                            
                            <form method="POST" action="{{ route('evidences.status', $evidence) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')
                                
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Cambiar estado</label>
                                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="pending" {{ $evidence->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="under_review" {{ $evidence->status === 'under_review' ? 'selected' : '' }}>En revisión</option>
                                        <option value="approved" {{ $evidence->status === 'approved' ? 'selected' : '' }}>Aprobado</option>
                                        <option value="rejected" {{ $evidence->status === 'rejected' ? 'selected' : '' }}>Rechazado</option>
                                        <option value="archived" {{ $evidence->status === 'archived' ? 'selected' : '' }}>Archivado</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notas</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                              placeholder="Comentarios sobre el cambio de estado..."></textarea>
                                </div>
                                
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    Actualizar Estado
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan

                <!-- History -->
                @if($evidence->history->count() > 0)
                    <div class="mt-6 bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Historial</h3>
                            
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($evidence->history->take(5) as $history)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">
                                                                <span class="font-medium text-gray-900">{{ $history->user->full_name ?? 'Sistema' }}</span>
                                                                {{ $history->notes }}
                                                            </p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            {{ $history->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
