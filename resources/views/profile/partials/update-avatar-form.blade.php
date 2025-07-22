<div class="flex items-center space-x-6">
    <div class="shrink-0">
        <img class="h-20 w-20 object-cover rounded-full" 
             src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) . '&color=7F9CF5&background=EBF4FF' }}" 
             alt="{{ $user->full_name }}">
    </div>
    
    <div class="flex-1">
        <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" x-data="avatarUpload()">
            @csrf
            
            <div>
                <label for="avatar" class="block text-sm font-medium text-gray-700">
                    Cambiar foto de perfil
                </label>
                <div class="mt-1 flex items-center space-x-4">
                    <input id="avatar" 
                           name="avatar" 
                           type="file" 
                           accept="image/*"
                           @change="previewImage($event)"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    
                    <button type="submit" 
                            :disabled="!selectedFile"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        Subir
                    </button>
                </div>
                
                @error('avatar')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <p class="mt-2 text-sm text-gray-500">
                    JPG, PNG o GIF hasta 2MB. Se redimensionará automáticamente a 200x200px.
                </p>
            </div>
            
            <!-- Preview -->
            <div x-show="previewUrl" class="mt-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Vista previa:</p>
                <img :src="previewUrl" class="h-20 w-20 object-cover rounded-full border-2 border-gray-200">
            </div>
        </form>
        
        @if($user->avatar)
            <form method="post" action="{{ route('profile.avatar') }}" class="mt-4">
                @csrf
                @method('delete')
                <button type="submit" 
                        onclick="return confirm('¿Estás seguro de que quieres eliminar tu foto de perfil?')"
                        class="text-sm text-red-600 hover:text-red-500">
                    Eliminar foto actual
                </button>
            </form>
        @endif
    </div>
</div>

<script>
    function avatarUpload() {
        return {
            selectedFile: null,
            previewUrl: null,
            
            previewImage(event) {
                const file = event.target.files[0];
                this.selectedFile = file;
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previewUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.previewUrl = null;
                }
            }
        }
    }
</script>
