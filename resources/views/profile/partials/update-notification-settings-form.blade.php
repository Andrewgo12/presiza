@php
    $settings = $user->notification_settings ?? [];
@endphp

<form method="post" action="{{ route('profile.notifications') }}" class="space-y-6">
    @csrf
    @method('patch')

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <label for="email_notifications" class="text-sm font-medium text-gray-700">
                    Notificaciones por email
                </label>
                <p class="text-sm text-gray-500">
                    Recibe notificaciones importantes por correo electrónico
                </p>
            </div>
            <div class="flex-shrink-0">
                <button type="button" 
                        x-data="{ enabled: {{ json_encode($settings['email_notifications'] ?? true) }} }"
                        @click="enabled = !enabled; $refs.emailInput.checked = enabled"
                        :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
                <input type="hidden" 
                       name="email_notifications" 
                       x-ref="emailInput"
                       :value="enabled ? '1' : '0'"
                       x-init="$el.checked = {{ json_encode($settings['email_notifications'] ?? true) }}">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex-1">
                <label for="evidence_notifications" class="text-sm font-medium text-gray-700">
                    Notificaciones de evidencias
                </label>
                <p class="text-sm text-gray-500">
                    Recibe notificaciones cuando se te asignen evidencias o cambien de estado
                </p>
            </div>
            <div class="flex-shrink-0">
                <button type="button" 
                        x-data="{ enabled: {{ json_encode($settings['evidence_notifications'] ?? true) }} }"
                        @click="enabled = !enabled; $refs.evidenceInput.checked = enabled"
                        :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
                <input type="hidden" 
                       name="evidence_notifications" 
                       x-ref="evidenceInput"
                       :value="enabled ? '1' : '0'"
                       x-init="$el.checked = {{ json_encode($settings['evidence_notifications'] ?? true) }}">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex-1">
                <label for="message_notifications" class="text-sm font-medium text-gray-700">
                    Notificaciones de mensajes
                </label>
                <p class="text-sm text-gray-500">
                    Recibe notificaciones cuando recibas nuevos mensajes
                </p>
            </div>
            <div class="flex-shrink-0">
                <button type="button" 
                        x-data="{ enabled: {{ json_encode($settings['message_notifications'] ?? true) }} }"
                        @click="enabled = !enabled; $refs.messageInput.checked = enabled"
                        :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
                <input type="hidden" 
                       name="message_notifications" 
                       x-ref="messageInput"
                       :value="enabled ? '1' : '0'"
                       x-init="$el.checked = {{ json_encode($settings['message_notifications'] ?? true) }}">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex-1">
                <label for="project_notifications" class="text-sm font-medium text-gray-700">
                    Notificaciones de proyectos
                </label>
                <p class="text-sm text-gray-500">
                    Recibe notificaciones sobre actualizaciones de proyectos y milestones
                </p>
            </div>
            <div class="flex-shrink-0">
                <button type="button" 
                        x-data="{ enabled: {{ json_encode($settings['project_notifications'] ?? true) }} }"
                        @click="enabled = !enabled; $refs.projectInput.checked = enabled"
                        :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
                <input type="hidden" 
                       name="project_notifications" 
                       x-ref="projectInput"
                       :value="enabled ? '1' : '0'"
                       x-init="$el.checked = {{ json_encode($settings['project_notifications'] ?? true) }}">
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
