<?php

namespace Database\Seeders;

use App\Models\TimeLog;
use App\Models\Project;
use App\Models\User;
use App\Models\Milestone;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TimeLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::where('role', '!=', 'admin')->get();
        $milestones = Milestone::all();

        // Generate time logs for the last 30 days
        $startDate = now()->subDays(30);
        $endDate = now();

        foreach ($projects as $project) {
            // Get project members
            $projectMembers = $project->users()->get();
            
            if ($projectMembers->isEmpty()) {
                $projectMembers = $users->random(rand(2, 4));
            }

            foreach ($projectMembers as $user) {
                // Generate 10-20 time logs per user per project
                $logCount = rand(10, 20);
                
                for ($i = 0; $i < $logCount; $i++) {
                    $logDate = Carbon::createFromTimestamp(
                        rand($startDate->timestamp, $endDate->timestamp)
                    );
                    
                    // Skip weekends for most logs
                    if ($logDate->isWeekend() && rand(0, 3) > 0) {
                        continue;
                    }

                    $startTime = $logDate->copy()->setTime(rand(8, 10), rand(0, 59));
                    $duration = rand(30, 480); // 30 minutes to 8 hours
                    $endTime = $startTime->copy()->addMinutes($duration);

                    // Ensure end time doesn't go past reasonable work hours
                    if ($endTime->hour > 18) {
                        $endTime = $startTime->copy()->setTime(18, 0);
                        $duration = $startTime->diffInMinutes($endTime);
                    }

                    $milestone = $milestones->where('project_id', $project->id)->random();

                    TimeLog::create([
                        'user_id' => $user->id,
                        'project_id' => $project->id,
                        'milestone_id' => rand(0, 1) ? $milestone->id : null,
                        'task_name' => $this->getRandomTaskName(),
                        'description' => $this->getRandomTaskDescription(),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'duration_minutes' => $duration,
                        'is_billable' => rand(0, 1),
                        'hourly_rate' => $this->getHourlyRate($user->role),
                        'status' => $this->getTimeLogStatus(),
                        'approved_by' => $this->getApprover($project),
                        'approved_at' => $this->getApprovalDate($logDate),
                        'tags' => $this->getRandomTags(),
                        'location' => $this->getRandomLocation(),
                        'created_at' => $logDate,
                        'updated_at' => $logDate,
                    ]);
                }
            }
        }

        $this->command->info('Time logs creados exitosamente.');
    }

    private function getRandomTaskName(): string
    {
        $tasks = [
            'Desarrollo de funcionalidad',
            'Revisión de código',
            'Testing y QA',
            'Documentación',
            'Reunión de equipo',
            'Análisis de requisitos',
            'Corrección de bugs',
            'Implementación de API',
            'Configuración de entorno',
            'Investigación técnica',
            'Optimización de rendimiento',
            'Integración de sistemas',
            'Capacitación',
            'Planificación de sprint',
            'Revisión de arquitectura',
        ];

        return $tasks[array_rand($tasks)];
    }

    private function getRandomTaskDescription(): string
    {
        $descriptions = [
            'Trabajo en el desarrollo de nuevas características del sistema.',
            'Revisión y mejora del código existente.',
            'Ejecución de pruebas y verificación de calidad.',
            'Creación y actualización de documentación técnica.',
            'Participación en reuniones de coordinación del equipo.',
            'Análisis detallado de los requisitos del cliente.',
            'Identificación y corrección de errores en el sistema.',
            'Desarrollo e implementación de nuevas APIs.',
            'Configuración y mantenimiento del entorno de desarrollo.',
            'Investigación de nuevas tecnologías y metodologías.',
            'Optimización del rendimiento del sistema.',
            'Integración con sistemas externos.',
            'Capacitación en nuevas herramientas y tecnologías.',
            'Planificación y organización de actividades del sprint.',
            'Revisión y mejora de la arquitectura del sistema.',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function getHourlyRate($role): float
    {
        $rates = [
            'admin' => 80.00,
            'analyst' => 65.00,
            'investigator' => 70.00,
            'user' => 45.00,
        ];

        return $rates[$role] ?? 50.00;
    }

    private function getTimeLogStatus(): string
    {
        $statuses = ['pending', 'approved', 'rejected'];
        $weights = [20, 70, 10]; // 70% approved, 20% pending, 10% rejected
        
        $random = rand(1, 100);
        if ($random <= 20) return 'pending';
        if ($random <= 90) return 'approved';
        return 'rejected';
    }

    private function getApprover($project): ?int
    {
        // 80% chance of having an approver
        if (rand(0, 9) < 2) return null;
        
        return $project->created_by;
    }

    private function getApprovalDate($logDate): ?Carbon
    {
        // If status is approved, set approval date 1-3 days after log date
        if (rand(0, 9) < 7) { // 70% approved
            return $logDate->copy()->addDays(rand(1, 3));
        }
        
        return null;
    }

    private function getRandomTags(): array
    {
        $allTags = [
            'frontend', 'backend', 'database', 'api', 'testing', 
            'documentation', 'meeting', 'research', 'bugfix', 
            'feature', 'optimization', 'security', 'deployment'
        ];

        $tagCount = rand(1, 3);
        return array_slice($allTags, 0, $tagCount);
    }

    private function getRandomLocation(): string
    {
        $locations = [
            'Oficina Principal',
            'Trabajo Remoto',
            'Oficina Cliente',
            'Coworking',
            'Sucursal Norte',
            'Sucursal Sur',
        ];

        return $locations[array_rand($locations)];
    }
}
