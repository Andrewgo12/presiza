<?php

namespace Database\Seeders;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar que existen los directorios
        Storage::disk('public')->makeDirectory('files');
        Storage::disk('public')->makeDirectory('thumbnails');

        // Obtener usuarios existentes
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios disponibles. Ejecuta UserSeeder primero.');
            return;
        }

        // Crear archivos de ejemplo para cada usuario
        foreach ($users as $user) {
            // Crear 3-8 archivos por usuario
            $fileCount = rand(3, 8);
            
            File::factory($fileCount)
                ->state(['uploaded_by' => $user->id])
                ->create();
        }

        // Crear algunos archivos específicos de ejemplo
        $adminUser = User::where('role', 'admin')->first();
        if ($adminUser) {
            // Archivos de documentación del sistema
            File::factory()->document()->create([
                'uploaded_by' => $adminUser->id,
                'original_name' => 'Manual_Usuario_Sistema.pdf',
                'description' => 'Manual completo de usuario del sistema de gestión de evidencias',
                'category' => 'document',
                'access_level' => 'internal',
                'tags' => ['manual', 'documentación', 'sistema'],
                'is_public' => true,
            ]);

            File::factory()->document()->create([
                'uploaded_by' => $adminUser->id,
                'original_name' => 'Politicas_Seguridad.docx',
                'description' => 'Políticas de seguridad de la información corporativa',
                'category' => 'document',
                'access_level' => 'confidential',
                'tags' => ['políticas', 'seguridad', 'confidencial'],
                'is_public' => false,
            ]);
        }

        // Crear archivos populares
        File::factory(5)->popular()->create([
            'uploaded_by' => $users->random()->id,
            'is_public' => true,
            'access_level' => 'public',
        ]);

        // Crear archivos que expiran pronto
        File::factory(3)->expiringSoon()->create([
            'uploaded_by' => $users->random()->id,
            'tags' => ['temporal', 'expira'],
        ]);

        // Crear archivos de imágenes
        File::factory(10)->image()->create([
            'uploaded_by' => $users->random()->id,
            'tags' => ['imagen', 'evidencia'],
        ]);

        // Crear archivos confidenciales
        File::factory(5)->confidential()->create([
            'uploaded_by' => $users->where('role', '!=', 'user')->random()->id,
        ]);

        $this->command->info('Archivos de ejemplo creados exitosamente.');
        $this->command->info('Total de archivos: ' . File::count());
        
        // Mostrar estadísticas
        $this->command->info('Estadísticas de archivos:');
        $this->command->info('- Documentos: ' . File::where('category', 'document')->count());
        $this->command->info('- Imágenes: ' . File::where('category', 'image')->count());
        $this->command->info('- Videos: ' . File::where('category', 'video')->count());
        $this->command->info('- Archivos: ' . File::where('category', 'archive')->count());
        $this->command->info('- Públicos: ' . File::where('is_public', true)->count());
        $this->command->info('- Confidenciales: ' . File::where('access_level', 'confidential')->count());
    }
}
