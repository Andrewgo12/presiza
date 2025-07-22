<?php

namespace Database\Seeders;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class MilestoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::all();

        foreach ($projects as $project) {
            // Create 3-5 milestones per project
            $milestoneCount = rand(3, 5);
            
            for ($i = 1; $i <= $milestoneCount; $i++) {
                $startDate = now()->addDays(($i - 1) * 30);
                $endDate = $startDate->copy()->addDays(rand(15, 45));
                
                $milestone = Milestone::create([
                    'project_id' => $project->id,
                    'name' => $this->getMilestoneTitle($i, $project->name),
                    'description' => $this->getMilestoneDescription($i),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $this->getMilestoneStatus($i, $milestoneCount),
                    'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                    'progress_percentage' => $this->getMilestoneProgress($i, $milestoneCount),
                    'assigned_to' => $users->random()->id,
                    'created_by' => $project->created_by,
                    'estimated_hours' => rand(40, 200),
                    'actual_hours' => $this->getMilestoneProgress($i, $milestoneCount) > 0 ? rand(30, 180) : 0,
                ]);

                // Add some milestone updates/comments
                if (rand(0, 1)) {
                    $milestone->update([
                        'notes' => 'Milestone actualizado: ' . fake()->sentence(),
                        'updated_at' => now()->subDays(rand(1, 10)),
                    ]);
                }
            }
        }

        $this->command->info('Milestones creados exitosamente.');
    }

    private function getMilestoneTitle($index, $projectName): string
    {
        $titles = [
            1 => 'Fase de Planificación',
            2 => 'Desarrollo Inicial',
            3 => 'Implementación Core',
            4 => 'Testing y QA',
            5 => 'Despliegue y Lanzamiento',
        ];

        return ($titles[$index] ?? "Milestone $index") . " - $projectName";
    }

    private function getMilestoneDescription($index): string
    {
        $descriptions = [
            1 => 'Definición de requisitos, arquitectura y planificación detallada del proyecto.',
            2 => 'Desarrollo de las funcionalidades básicas y configuración del entorno.',
            3 => 'Implementación de las características principales del sistema.',
            4 => 'Pruebas exhaustivas, corrección de errores y optimización.',
            5 => 'Preparación para producción, despliegue y puesta en marcha.',
        ];

        return $descriptions[$index] ?? 'Descripción del milestone ' . $index;
    }

    private function getMilestoneStatus($index, $total): string
    {
        if ($index <= $total - 2) {
            return 'completed';
        } elseif ($index == $total - 1) {
            return 'in_progress';
        } else {
            return 'pending';
        }
    }

    private function getMilestoneProgress($index, $total): int
    {
        if ($index <= $total - 2) {
            return 100;
        } elseif ($index == $total - 1) {
            return rand(30, 80);
        } else {
            return 0;
        }
    }

    private function getMilestoneDeliverables($index): array
    {
        $deliverables = [
            1 => [
                'Documento de requisitos',
                'Arquitectura del sistema',
                'Plan de proyecto',
                'Cronograma detallado'
            ],
            2 => [
                'Configuración del entorno',
                'Estructura base del código',
                'Documentación técnica inicial',
                'Prototipo funcional'
            ],
            3 => [
                'Módulos principales implementados',
                'APIs desarrolladas',
                'Base de datos configurada',
                'Integración de componentes'
            ],
            4 => [
                'Suite de pruebas completa',
                'Reporte de bugs corregidos',
                'Documentación de usuario',
                'Manual de instalación'
            ],
            5 => [
                'Sistema en producción',
                'Monitoreo configurado',
                'Capacitación completada',
                'Documentación final'
            ],
        ];

        return $deliverables[$index] ?? ['Entregable ' . $index];
    }
}
