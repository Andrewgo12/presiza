@extends('layouts.admin')

@section('title', 'Configuración del Sistema')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                    Configuración del Sistema
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Administra la configuración general del sistema
                </p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- System Information -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Información del Sistema
                        </h3>
                        
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Aplicación</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $settings['app_name'] }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Entorno</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $settings['app_env'] === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($settings['app_env']) }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Debug</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $settings['app_debug'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $settings['app_debug'] ? 'Activado' : 'Desactivado' }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Base de datos</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $settings['database_connection'] }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Cache</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $settings['cache_driver'] }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sesiones</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $settings['session_driver'] }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Correo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $settings['mail_driver'] }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Almacenamiento</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $settings['filesystem_driver'] }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Mantenimiento</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $settings['maintenance_mode'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $settings['maintenance_mode'] ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-6 bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Acciones Rápidas
                        </h3>
                        
                        <div class="space-y-3">
                            <form method="POST" action="{{ route('admin.maintenance.cache-clear') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                                    <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Limpiar Cache
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('admin.maintenance.optimize') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                                    <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                    Optimizar Sistema
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('admin.settings.backup.create') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500">
                                    <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                    </svg>
                                    Crear Respaldo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                            Configuración General
                        </h3>
                        
                        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PATCH')
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="app_name" class="block text-sm font-medium text-gray-700">
                                        Nombre de la aplicación
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" 
                                               name="app_name" 
                                               id="app_name"
                                               value="{{ old('app_name', $settings['app_name']) }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label for="app_timezone" class="block text-sm font-medium text-gray-700">
                                        Zona horaria
                                    </label>
                                    <div class="mt-1">
                                        <select name="app_timezone" 
                                                id="app_timezone"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="America/Bogota" {{ old('app_timezone', config('app.timezone')) === 'America/Bogota' ? 'selected' : '' }}>América/Bogotá</option>
                                            <option value="America/New_York" {{ old('app_timezone', config('app.timezone')) === 'America/New_York' ? 'selected' : '' }}>América/Nueva York</option>
                                            <option value="Europe/Madrid" {{ old('app_timezone', config('app.timezone')) === 'Europe/Madrid' ? 'selected' : '' }}>Europa/Madrid</option>
                                            <option value="UTC" {{ old('app_timezone', config('app.timezone')) === 'UTC' ? 'selected' : '' }}>UTC</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="mail_from_address" class="block text-sm font-medium text-gray-700">
                                        Email del sistema
                                    </label>
                                    <div class="mt-1">
                                        <input type="email" 
                                               name="mail_from_address" 
                                               id="mail_from_address"
                                               value="{{ old('mail_from_address', config('mail.from.address')) }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label for="mail_from_name" class="block text-sm font-medium text-gray-700">
                                        Nombre del remitente
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" 
                                               name="mail_from_name" 
                                               id="mail_from_name"
                                               value="{{ old('mail_from_name', config('mail.from.name')) }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="max_file_size" class="block text-sm font-medium text-gray-700">
                                        Tamaño máximo de archivo (MB)
                                    </label>
                                    <div class="mt-1">
                                        <input type="number" 
                                               name="max_file_size" 
                                               id="max_file_size"
                                               value="{{ old('max_file_size', 50) }}"
                                               min="1"
                                               max="100"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label for="session_lifetime" class="block text-sm font-medium text-gray-700">
                                        Duración de sesión (minutos)
                                    </label>
                                    <div class="mt-1">
                                        <input type="number" 
                                               name="session_lifetime" 
                                               id="session_lifetime"
                                               value="{{ old('session_lifetime', config('session.lifetime')) }}"
                                               min="1"
                                               max="1440"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="allowed_file_types" class="block text-sm font-medium text-gray-700">
                                    Tipos de archivo permitidos
                                </label>
                                <div class="mt-1">
                                    <input type="text" 
                                           name="allowed_file_types" 
                                           id="allowed_file_types"
                                           value="{{ old('allowed_file_types', 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip,rar') }}"
                                           placeholder="pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip,rar"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Separar con comas</p>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="maintenance_mode" 
                                       id="maintenance_mode"
                                       value="1"
                                       {{ old('maintenance_mode', $settings['maintenance_mode']) ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="maintenance_mode" class="ml-2 block text-sm text-gray-900">
                                    Activar modo de mantenimiento
                                </label>
                            </div>

                            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                                <button type="submit" 
                                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
