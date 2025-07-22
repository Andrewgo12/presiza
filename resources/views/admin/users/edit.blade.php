@extends('layouts.admin')

@section('title', 'Editar Usuario')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                    Editar Usuario: {{ $user->full_name }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Modifica la información del usuario
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                <a href="{{ route('admin.users.show', $user) }}" 
                   class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Ver
                </a>
                <a href="{{ route('admin.users.index') }}" 
                   class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6 p-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">
                                Nombre *
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       name="first_name" 
                                       id="first_name"
                                       value="{{ old('first_name', $user->first_name) }}"
                                       required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('first_name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                            </div>
                            @error('first_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">
                                Apellido *
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       name="last_name" 
                                       id="last_name"
                                       value="{{ old('last_name', $user->last_name) }}"
                                       required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('last_name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                            </div>
                            @error('last_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Correo electrónico *
                        </label>
                        <div class="mt-1">
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Cambiar Contraseña</h3>
                        <p class="text-sm text-gray-500 mb-4">Deja estos campos vacíos si no deseas cambiar la contraseña.</p>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Nueva contraseña
                                </label>
                                <div class="mt-1">
                                    <input type="password" 
                                           name="password" 
                                           id="password"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('password') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                </div>
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                    Confirmar nueva contraseña
                                </label>
                                <div class="mt-1">
                                    <input type="password" 
                                           name="password_confirmation" 
                                           id="password_confirmation"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Perfil</h3>
                        
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">
                                Rol *
                            </label>
                            <div class="mt-1">
                                <select name="role" 
                                        id="role"
                                        required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('role') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                                    <option value="">Seleccionar rol</option>
                                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Usuario</option>
                                    <option value="analyst" {{ old('role', $user->role) === 'analyst' ? 'selected' : '' }}>Analista</option>
                                    <option value="investigator" {{ old('role', $user->role) === 'investigator' ? 'selected' : '' }}>Investigador</option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                                </select>
                            </div>
                            @error('role')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700">
                                    Departamento
                                </label>
                                <div class="mt-1">
                                    <input type="text" 
                                           name="department" 
                                           id="department"
                                           value="{{ old('department', $user->department) }}"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700">
                                    Cargo
                                </label>
                                <div class="mt-1">
                                    <input type="text" 
                                           name="position" 
                                           id="position"
                                           value="{{ old('position', $user->position) }}"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Usuario activo
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.users.show', $user) }}" 
                           class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
