@php
    $settings = $user->privacy_settings ?? [];
@endphp

<form method="post" action="{{ route('profile.privacy') }}" class="space-y-6">
    @csrf
    @method('patch')

    <div>
        <label for="profile_visibility" class="block text-sm font-medium text-gray-700">
            Visibilidad del perfil
        </label>
        <div class="mt-1">
            <select id="profile_visibility" 
                    name="profile_visibility" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="public" {{ ($settings['profile_visibility'] ?? 'public') === 'public' ? 'selected' : '' }}>
                    Público - Visible para todos los usuarios
                </option>
                <option value="team" {{ ($settings['profile_visibility'] ?? 'public') === 'team' ? 'selected' : '' }}>
                    Equipo - Visible solo para miembros de tus proyectos
                </option>
                <option value="private" {{ ($settings['profile_visibility'] ?? 'public') === 'private' ? 'selected' : '' }}>
                    Privado - Visible solo para administradores
                </option>
            </select>
        </div>
        <p class="mt-2 text-sm text-gray-500">
            Controla quién puede ver tu información de perfil
        </p>
    </div>

    <div class="space-y-4">
        <h4 class="text-sm font-medium text-gray-700">Información visible en el perfil</h4>
        
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <label for="show_email" class="text-sm text-gray-700">
                        Mostrar correo electrónico
                    </label>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" 
                            x-data="{ enabled: {{ json_encode($settings['show_email'] ?? false) }} }"
                            @click="enabled = !enabled; $refs.emailInput.checked = enabled"
                            :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                    <input type="hidden" 
                           name="show_email" 
                           x-ref="emailInput"
                           :value="enabled ? '1' : '0'"
                           x-init="$el.checked = {{ json_encode($settings['show_email'] ?? false) }}">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <label for="show_department" class="text-sm text-gray-700">
                        Mostrar departamento
                    </label>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" 
                            x-data="{ enabled: {{ json_encode($settings['show_department'] ?? true) }} }"
                            @click="enabled = !enabled; $refs.departmentInput.checked = enabled"
                            :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                    <input type="hidden" 
                           name="show_department" 
                           x-ref="departmentInput"
                           :value="enabled ? '1' : '0'"
                           x-init="$el.checked = {{ json_encode($settings['show_department'] ?? true) }}">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <label for="show_position" class="text-sm text-gray-700">
                        Mostrar cargo
                    </label>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" 
                            x-data="{ enabled: {{ json_encode($settings['show_position'] ?? true) }} }"
                            @click="enabled = !enabled; $refs.positionInput.checked = enabled"
                            :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                    <input type="hidden" 
                           name="show_position" 
                           x-ref="positionInput"
                           :value="enabled ? '1' : '0'"
                           x-init="$el.checked = {{ json_encode($settings['show_position'] ?? true) }}">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <label for="allow_messages" class="text-sm text-gray-700">
                        Permitir mensajes directos
                    </label>
                    <p class="text-xs text-gray-500">
                        Otros usuarios pueden enviarte mensajes privados
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" 
                            x-data="{ enabled: {{ json_encode($settings['allow_messages'] ?? true) }} }"
                            @click="enabled = !enabled; $refs.messagesInput.checked = enabled"
                            :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                    <input type="hidden" 
                           name="allow_messages" 
                           x-ref="messagesInput"
                           :value="enabled ? '1' : '0'"
                           x-init="$el.checked = {{ json_encode($settings['allow_messages'] ?? true) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Guardar configuración
        </button>

        @if (session('success'))
            <p x-data="{ show: true }" 
               x-show="show" 
               x-transition 
               x-init="setTimeout(() => show = false, 2000)" 
               class="text-sm text-gray-600">
                Configuración guardada.
            </p>
        @endif
    </div>
</form>
