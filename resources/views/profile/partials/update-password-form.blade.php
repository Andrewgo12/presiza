<form method="post" action="{{ route('password.update') }}" class="space-y-6">
    @csrf
    @method('put')

    <div>
        <label for="update_password_current_password" class="block text-sm font-medium text-gray-700">
            Contraseña actual
        </label>
        <div class="mt-1">
            <input id="update_password_current_password" 
                   name="current_password" 
                   type="password" 
                   autocomplete="current-password"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('current_password', 'updatePassword') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
        </div>
        @error('current_password', 'updatePassword')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="update_password_password" class="block text-sm font-medium text-gray-700">
            Nueva contraseña
        </label>
        <div class="mt-1">
            <input id="update_password_password" 
                   name="password" 
                   type="password" 
                   autocomplete="new-password"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('password', 'updatePassword') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
        </div>
        @error('password', 'updatePassword')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700">
            Confirmar nueva contraseña
        </label>
        <div class="mt-1">
            <input id="update_password_password_confirmation" 
                   name="password_confirmation" 
                   type="password" 
                   autocomplete="new-password"
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('password_confirmation', 'updatePassword') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
        </div>
        @error('password_confirmation', 'updatePassword')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Actualizar contraseña
        </button>

        @if (session('status') === 'password-updated')
            <p x-data="{ show: true }" 
               x-show="show" 
               x-transition 
               x-init="setTimeout(() => show = false, 2000)" 
               class="text-sm text-gray-600">
                Contraseña actualizada.
            </p>
        @endif
    </div>
</form>
