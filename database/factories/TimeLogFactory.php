<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeLog>
 */
class TimeLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $taskDescriptions = [
            'Desarrollo de funcionalidad de autenticación de usuarios',
            'Implementación de API REST para gestión de datos',
            'Diseño y desarrollo de interfaz de usuario',
            'Corrección de bugs reportados en testing',
            'Optimización de consultas de base de datos',
            'Integración con servicios externos',
            'Desarrollo de módulo de reportes',
            'Implementación de sistema de notificaciones',
            'Refactoring de código legacy',
            'Documentación técnica del módulo',
            'Configuración de ambiente de desarrollo',
            'Pruebas unitarias y de integración',
            'Análisis de requerimientos del cliente',
            'Revisión de código y code review',
            'Investigación de nuevas tecnologías',
            'Reunión de planificación de sprint',
            'Soporte técnico y resolución de incidencias',
            'Migración de datos entre sistemas',
            'Configuración de servidores y deployment',
            'Capacitación en nuevas herramientas',
        ];

        $date = fake()->dateTimeBetween('-3 months', 'now');
        $hours = fake()->randomFloat(2, 0.5, 8);
        $hourlyRate = fake()->randomFloat(2, 15, 80);

        // Generar horarios de trabajo realistas
        $startHour = fake()->numberBetween(8, 16);
        $startTime = $date->copy()->setTime($startHour, fake()->numberBetween(0, 59));
        $endTime = $startTime->copy()->addHours(floor($hours))->addMinutes(($hours - floor($hours)) * 60);

        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'milestone_id' => fake()->optional(0.7)->randomElement(ProjectMilestone::pluck('id')->toArray()),
            'task_description' => fake()->randomElement($taskDescriptions),
            'hours' => $hours,
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_billable' => fake()->boolean(85), // 85% son facturables
            'hourly_rate' => $hourlyRate,
            'notes' => fake()->optional(0.4)->paragraph(),
            'approved_by' => fake()->optional(0.6)->randomElement(User::pluck('id')->toArray()),
            'approved_at' => fake()->optional(0.6)->dateTimeBetween($date, 'now'),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function ($timeLog) {
            // Ensure milestone belongs to the project
            if ($timeLog->milestone_id && $timeLog->project_id) {
                $milestone = ProjectMilestone::find($timeLog->milestone_id);
                if (!$milestone || $milestone->project_id !== $timeLog->project_id) {
                    $timeLog->milestone_id = null;
                }
            }

            // Set approved_at to null if no approver
            if (!$timeLog->approved_by) {
                $timeLog->approved_at = null;
            }
        });
    }

    /**
     * Indicate that the time log is for today.
     */
    public function today(): static
    {
        $hours = fake()->randomFloat(2, 1, 8);
        $startHour = fake()->numberBetween(8, 16);
        $startTime = now()->setTime($startHour, fake()->numberBetween(0, 59));
        $endTime = $startTime->copy()->addHours(floor($hours))->addMinutes(($hours - floor($hours)) * 60);

        return $this->state(fn (array $attributes) => [
            'date' => now()->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours' => $hours,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the time log is from this week.
     */
    public function thisWeek(): static
    {
        $date = fake()->dateTimeBetween(now()->startOfWeek(), now());
        $hours = fake()->randomFloat(2, 0.5, 8);
        $startHour = fake()->numberBetween(8, 17);
        $startTime = $date->copy()->setTime($startHour, fake()->numberBetween(0, 59));
        $endTime = $startTime->copy()->addHours(floor($hours))->addMinutes(($hours - floor($hours)) * 60);

        return $this->state(fn (array $attributes) => [
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours' => $hours,
        ]);
    }

    /**
     * Indicate that the time log is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'approved_at' => fake()->dateTimeBetween($attributes['date'] ?? '-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the time log is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the time log is billable.
     */
    public function billable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => true,
            'hourly_rate' => fake()->randomFloat(2, 25, 100),
        ]);
    }

    /**
     * Indicate that the time log is non-billable.
     */
    public function nonBillable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_billable' => false,
            'hourly_rate' => 0,
            'task_description' => fake()->randomElement([
                'Reunión interna de equipo',
                'Capacitación y aprendizaje',
                'Configuración de ambiente de desarrollo',
                'Investigación y análisis técnico',
                'Documentación interna',
                'Code review y revisión de código',
                'Reunión con stakeholders',
                'Planificación de sprint',
            ]),
        ]);
    }

    /**
     * Indicate that the time log is for development work.
     */
    public function development(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_description' => fake()->randomElement([
                'Desarrollo de nueva funcionalidad del módulo principal',
                'Implementación de API REST para gestión de datos',
                'Desarrollo de componentes de interfaz de usuario',
                'Integración con servicios externos y APIs',
                'Desarrollo de lógica de negocio del sistema',
                'Implementación de validaciones y reglas de negocio',
                'Desarrollo de módulo de autenticación y autorización',
                'Creación de endpoints para comunicación frontend-backend',
            ]),
            'hours' => fake()->randomFloat(2, 2, 8),
            'is_billable' => true,
            'hourly_rate' => fake()->randomFloat(2, 30, 80),
        ]);
    }

    /**
     * Indicate that the time log is for testing work.
     */
    public function testing(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_description' => fake()->randomElement([
                'Desarrollo y ejecución de pruebas unitarias',
                'Pruebas de integración entre componentes',
                'Testing manual de funcionalidades desarrolladas',
                'Corrección de bugs encontrados en testing',
                'Pruebas de rendimiento y optimización',
                'Validación de casos de uso con datos reales',
                'Testing de compatibilidad cross-browser',
                'Pruebas de seguridad y vulnerabilidades',
            ]),
            'hours' => fake()->randomFloat(2, 1, 6),
            'is_billable' => true,
            'hourly_rate' => fake()->randomFloat(2, 25, 60),
        ]);
    }

    /**
     * Indicate that the time log is for meeting/communication.
     */
    public function meeting(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_description' => fake()->randomElement([
                'Reunión de planificación de sprint con el equipo',
                'Daily standup y sincronización del equipo',
                'Reunión con cliente para revisión de avances',
                'Sesión de retrospectiva del sprint',
                'Reunión de análisis de requerimientos',
                'Presentación de demo al cliente',
                'Reunión de arquitectura técnica',
                'Sesión de brainstorming para soluciones',
            ]),
            'hours' => fake()->randomFloat(2, 0.5, 3),
            'is_billable' => fake()->boolean(60),
            'hourly_rate' => fake()->randomFloat(2, 20, 50),
        ]);
    }

    /**
     * Indicate that the time log is for documentation work.
     */
    public function documentation(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_description' => fake()->randomElement([
                'Documentación técnica de APIs desarrolladas',
                'Creación de manual de usuario del sistema',
                'Documentación de arquitectura del proyecto',
                'Elaboración de guías de instalación y configuración',
                'Documentación de procesos y procedimientos',
                'Actualización de documentación existente',
                'Creación de diagramas técnicos y de flujo',
                'Documentación de casos de prueba',
            ]),
            'hours' => fake()->randomFloat(2, 1, 4),
            'is_billable' => fake()->boolean(70),
            'hourly_rate' => fake()->randomFloat(2, 20, 45),
        ]);
    }

    /**
     * Indicate that the time log is overtime work.
     */
    public function overtime(): static
    {
        return $this->state(fn (array $attributes) => [
            'hours' => fake()->randomFloat(2, 8.5, 12),
            'start_time' => fake()->dateTimeBetween('08:00', '09:00'),
            'end_time' => fake()->dateTimeBetween('18:00', '21:00'),
            'is_billable' => true,
            'hourly_rate' => fake()->randomFloat(2, 40, 120), // Higher rate for overtime
            'notes' => 'Trabajo de horas extra para cumplir con deadline del proyecto.',
        ]);
    }
}
