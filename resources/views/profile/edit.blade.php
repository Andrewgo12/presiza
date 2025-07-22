@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="space-y-6" x-data="profileManager()">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:max-w-7xl lg:mx-auto lg:px-8">
            <div class="py-6 md:flex md:items-center md:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:leading-9">
                        Mi Perfil
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestiona tu información personal y configuración de cuenta
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <nav class="space-y-1" x-data="{ activeTab: 'profile' }">
                    <button @click="activeTab = 'profile'" 
                            :class="activeTab === 'profile' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900'"
                            class="group border-l-4 px-3 py-2 flex items-center text-sm font-medium w-full text-left">
                        <svg :class="activeTab === 'profile' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                             class="flex-shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Información Personal
                    </button>

                    <button @click="activeTab = 'avatar'" 
                            :class="activeTab === 'avatar' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900'"
                            class="group border-l-4 px-3 py-2 flex items-center text-sm font-medium w-full text-left">
                        <svg :class="activeTab === 'avatar' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                             class="flex-shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                        </svg>
                        Foto de Perfil
                    </button>

                    <button @click="activeTab = 'notifications'" 
                            :class="activeTab === 'notifications' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900'"
                            class="group border-l-4 px-3 py-2 flex items-center text-sm font-medium w-full text-left">
                        <svg :class="activeTab === 'notifications' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                             class="flex-shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        Notificaciones
                    </button>

                    <button @click="activeTab = 'privacy'" 
                            :class="activeTab === 'privacy' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900'"
                            class="group border-l-4 px-3 py-2 flex items-center text-sm font-medium w-full text-left">
                        <svg :class="activeTab === 'privacy' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                             class="flex-shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        Privacidad
                    </button>

                    <button @click="activeTab = 'password'" 
                            :class="activeTab === 'password' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900'"
                            class="group border-l-4 px-3 py-2 flex items-center text-sm font-medium w-full text-left">
                        <svg :class="activeTab === 'password' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'"
                             class="flex-shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        Cambiar Contraseña
                    </button>

                    <button @click="activeTab = 'delete'" 
                            :class="activeTab === 'delete' ? 'bg-red-50 border-red-500 text-red-700' : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900'"
                            class="group border-l-4 px-3 py-2 flex items-center text-sm font-medium w-full text-left">
                        <svg :class="activeTab === 'delete' ? 'text-red-500' : 'text-gray-400 group-hover:text-gray-500'"
                             class="flex-shrink-0 -ml-1 mr-3 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Eliminar Cuenta
                    </button>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Profile Information -->
                <div x-show="activeTab === 'profile'" class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Información Personal
                        </h3>
                        
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Avatar Upload -->
                <div x-show="activeTab === 'avatar'" class="bg-white shadow rounded-lg" x-cloak>
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Foto de Perfil
                        </h3>
                        
                        @include('profile.partials.update-avatar-form')
                    </div>
                </div>

                <!-- Notification Settings -->
                <div x-show="activeTab === 'notifications'" class="bg-white shadow rounded-lg" x-cloak>
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Configuración de Notificaciones
                        </h3>
                        
                        @include('profile.partials.update-notification-settings-form')
                    </div>
                </div>

                <!-- Privacy Settings -->
                <div x-show="activeTab === 'privacy'" class="bg-white shadow rounded-lg" x-cloak>
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Configuración de Privacidad
                        </h3>
                        
                        @include('profile.partials.update-privacy-settings-form')
                    </div>
                </div>

                <!-- Update Password -->
                <div x-show="activeTab === 'password'" class="bg-white shadow rounded-lg" x-cloak>
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Cambiar Contraseña
                        </h3>
                        
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Delete Account -->
                <div x-show="activeTab === 'delete'" class="bg-white shadow rounded-lg" x-cloak>
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Eliminar Cuenta
                        </h3>
                        
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function profileManager() {
        return {
            activeTab: 'profile'
        }
    }
</script>
@endsection
