<form method="post" action="{{ route('profile.update') }}" class="space-y-6">
    @csrf
    @method('patch')

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">
                Nombre
            </label>
            <div class="mt-1">
                <input id="first_name" 
                       name="first_name" 
                       type="text" 
                       autocomplete="given-name" 
                       required
                       value="{{ old('first_name', $user->first_name) }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('first_name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
            </div>
            @error('first_name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">
                Apellido
            </label>
            <div class="mt-1">
                <input id="last_name" 
                       name="last_name" 
                       type="text" 
                       autocomplete="family-name" 
                       required
                       value="{{ old('last_name', $user->last_name) }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('last_name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
            </div>
            @error('last_name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
            Correo electrónico
        </label>
        <div class="mt-1">
            <input id="email" 
                   name="email" 
                   type="email" 
                   autocomplete="username" 
                   required
                   value="{{ old('email', $user->email) }}"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
        </div>
        @error('email')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-sm text-gray-800">
                    Tu dirección de correo electrónico no está verificada.

                    <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Haz clic aquí para reenviar el correo de verificación.
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-sm text-green-600">
                        Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.
                    </p>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="department" class="block text-sm font-medium text-gray-700">
                Departamento
            </label>
            <div class="mt-1">
                <input id="department" 
                       name="department" 
                       type="text"
                       value="{{ old('department', $user->department) }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-gray-700">
                Cargo
            </label>
            <div class="mt-1">
                <input id="position" 
                       name="position" 
                       type="text"
                       value="{{ old('position', $user->position) }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Guardar cambios
        </button>

        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" 
               x-show="show" 
               x-transition 
               x-init="setTimeout(() => show = false, 2000)" 
               class="text-sm text-gray-600">
                Guardado.
            </p>
        @endif
    </div>
</form>

@if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>
@endif
