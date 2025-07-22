<?php

namespace Database\Seeders;

use App\Models\Evidence;
use App\Models\EvidenceEvaluation;
use App\Models\EvidenceHistory;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Seeder;

class EvidenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios existentes
        $users = User::all();
        $files = File::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios disponibles. Ejecuta UserSeeder primero.');
            return;
        }

        // Crear evidencias de ejemplo
        $evidences = collect();

        // Evidencias críticas pendientes
        for ($i = 0; $i < 5; $i++) {
            $evidence = Evidence::factory()->critical()->pending()->create([
                'submitted_by' => $users->random()->id,
            ]);
            $evidences->push($evidence);
        }

        // Evidencias en revisión
        for ($i = 0; $i < 8; $i++) {
            $evidence = Evidence::factory()->underReview()->create([
                'submitted_by' => $users->random()->id,
                'assigned_to' => $users->where('role', '!=', 'user')->random()->id,
            ]);
            $evidences->push($evidence);
        }

        // Evidencias aprobadas
        for ($i = 0; $i < 12; $i++) {
            $evidence = Evidence::factory()->approved()->create([
                'submitted_by' => $users->random()->id,
                'assigned_to' => $users->where('role', '!=', 'user')->random()->id,
            ]);
            $evidences->push($evidence);
        }

        // Evidencias de seguridad
        for ($i = 0; $i < 6; $i++) {
            $evidence = Evidence::factory()->security()->create([
                'submitted_by' => $users->random()->id,
                'assigned_to' => $users->where('role', 'investigator')->random()?->id,
            ]);
            $evidences->push($evidence);
        }

        // Evidencias de investigación
        for ($i = 0; $i < 4; $i++) {
            $evidence = Evidence::factory()->investigation()->create([
                'submitted_by' => $users->random()->id,
                'assigned_to' => $users->where('role', 'analyst')->random()?->id,
            ]);
            $evidences->push($evidence);
        }

        // Evidencias vencidas
        for ($i = 0; $i < 3; $i++) {
            $evidence = Evidence::factory()->overdue()->create([
                'submitted_by' => $users->random()->id,
            ]);
            $evidences->push($evidence);
        }

        // Evidencias recientes
        for ($i = 0; $i < 10; $i++) {
            $evidence = Evidence::factory()->recent()->create([
                'submitted_by' => $users->random()->id,
                'assigned_to' => $users->where('role', '!=', 'user')->random()?->id,
            ]);
            $evidences->push($evidence);
        }

        // Asociar archivos a evidencias
        if ($files->isNotEmpty()) {
            foreach ($evidences as $evidence) {
                // Asociar 1-5 archivos aleatorios a cada evidencia
                $fileCount = rand(1, min(5, $files->count()));
                $selectedFiles = $files->random($fileCount);
                
                foreach ($selectedFiles as $index => $file) {
                    $evidence->files()->attach($file->id, ['order' => $index + 1]);
                }
            }
        }

        // Crear evaluaciones para evidencias aprobadas y rechazadas
        $evaluatedEvidences = $evidences->whereIn('status', ['approved', 'rejected']);
        
        foreach ($evaluatedEvidences as $evidence) {
            // Crear 1-3 evaluaciones por evidencia
            $evaluationCount = rand(1, 3);
            
            for ($i = 0; $i < $evaluationCount; $i++) {
                $evaluator = $users->where('role', '!=', 'user')->random();
                
                EvidenceEvaluation::create([
                    'evidence_id' => $evidence->id,
                    'evaluator_id' => $evaluator->id,
                    'rating' => rand(1, 5),
                    'comment' => fake()->paragraph(),
                    'recommendation' => $evidence->status === 'approved' ? 'approve' : 'reject',
                ]);
            }
        }

        // Crear historial adicional para algunas evidencias
        foreach ($evidences->random(20) as $evidence) {
            // Crear entradas de historial adicionales
            $historyCount = rand(2, 5);
            
            for ($i = 0; $i < $historyCount; $i++) {
                $actions = ['updated', 'status_changed', 'assigned', 'evaluated'];
                $action = fake()->randomElement($actions);
                $user = $users->random();
                
                EvidenceHistory::create([
                    'evidence_id' => $evidence->id,
                    'user_id' => $user->id,
                    'action' => $action,
                    'old_values' => $this->generateOldValues($action),
                    'new_values' => $this->generateNewValues($action),
                    'notes' => fake()->optional(0.7)->sentence(),
                    'created_at' => fake()->dateTimeBetween($evidence->created_at, 'now'),
                ]);
            }
        }

        $this->command->info('Evidencias de ejemplo creadas exitosamente.');
        $this->command->info('Total de evidencias: ' . Evidence::count());
        
        // Mostrar estadísticas
        $this->command->info('Estadísticas de evidencias:');
        $this->command->info('- Pendientes: ' . Evidence::where('status', 'pending')->count());
        $this->command->info('- En revisión: ' . Evidence::where('status', 'under_review')->count());
        $this->command->info('- Aprobadas: ' . Evidence::where('status', 'approved')->count());
        $this->command->info('- Rechazadas: ' . Evidence::where('status', 'rejected')->count());
        $this->command->info('- Críticas: ' . Evidence::where('priority', 'critical')->count());
        $this->command->info('- Seguridad: ' . Evidence::where('category', 'security')->count());
        $this->command->info('- Con archivos: ' . Evidence::has('files')->count());
        $this->command->info('- Con evaluaciones: ' . Evidence::has('evaluations')->count());
    }

    /**
     * Generate old values for history entry.
     */
    private function generateOldValues(string $action): array
    {
        return match ($action) {
            'status_changed' => ['status' => fake()->randomElement(['pending', 'under_review'])],
            'assigned' => ['assigned_to' => null],
            'updated' => [
                'priority' => fake()->randomElement(['low', 'medium']),
                'description' => fake()->sentence(),
            ],
            'evaluated' => [],
            default => [],
        };
    }

    /**
     * Generate new values for history entry.
     */
    private function generateNewValues(string $action): array
    {
        return match ($action) {
            'status_changed' => ['status' => fake()->randomElement(['under_review', 'approved', 'rejected'])],
            'assigned' => ['assigned_to' => User::where('role', '!=', 'user')->inRandomOrder()->first()?->id],
            'updated' => [
                'priority' => fake()->randomElement(['medium', 'high', 'critical']),
                'description' => fake()->paragraph(),
            ],
            'evaluated' => [
                'rating' => rand(1, 5),
                'recommendation' => fake()->randomElement(['approve', 'reject', 'needs_revision']),
            ],
            default => [],
        };
    }
}
