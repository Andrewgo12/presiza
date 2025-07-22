<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectMilestone>
 */
class ProjectMilestoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $milestoneNames = [
            'Análisis de Requerimientos',
            'Diseño de Arquitectura',
            'Desarrollo del Backend',
            'Desarrollo del Frontend',
            'Integración de APIs',
            'Implementación de Base de Datos',
            'Desarrollo de Autenticación',
            'Módulo de Reportes',
            'Sistema de Notificaciones',
            'Panel de Administración',
            'Pruebas Unitarias',
            'Pruebas de Integración',
            'Pruebas de Usuario',
            'Optimización de Performance',
            'Documentación Técnica',
            'Capacitación de Usuarios',
            'Despliegue en Producción',
            'Monitoreo y Soporte',
        ];

        $descriptions = [
            'Análisis detallado de los requerimientos funcionales y no funcionales del sistema.',
            'Diseño de la arquitectura técnica y definición de componentes del sistema.',
            'Desarrollo de la lógica de negocio y servicios del lado del servidor.',
            'Implementación de la interfaz de usuario y experiencia del usuario.',
            'Integración con servicios externos y APIs de terceros.',
            'Diseño e implementación del modelo de datos y base de datos.',
            'Desarrollo del sistema de autenticación y autorización de usuarios.',
            'Implementación del módulo de generación de reportes y analytics.',
            'Desarrollo del sistema de notificaciones push y por email.',
            'Creación del panel administrativo para gestión del sistema.',
            'Desarrollo y ejecución de pruebas unitarias automatizadas.',
            'Pruebas de integración entre componentes del sistema.',
            'Pruebas de aceptación con usuarios finales y stakeholders.',
            'Optimización del rendimiento y tiempos de respuesta del sistema.',
            'Elaboración de documentación técnica y manuales de usuario.',
            'Capacitación del equipo de soporte y usuarios finales.',
            'Despliegue del sistema en el ambiente de producción.',
            'Implementación de monitoreo y soporte post-lanzamiento.',
        ];

        return [
            'project_id' => Project::factory(),
            'name' => fake()->randomElement($milestoneNames),
            'description' => fake()->randomElement($descriptions),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
            'completed_at' => fake()->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'assigned_to' => fake()->optional(0.8)->randomElement(User::pluck('id')->toArray()),
            'progress_percentage' => fake()->numberBetween(0, 100),
            'estimated_hours' => fake()->randomFloat(1, 8, 120),
            'actual_hours' => fake()->optional(0.6)->randomFloat(1, 5, 150),
            'order' => fake()->numberBetween(1, 20),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function ($milestone) {
            // Adjust progress and completion based on status
            if ($milestone->status === 'completed') {
                $milestone->progress_percentage = 100;
                $milestone->completed_at = fake()->dateTimeBetween('-1 month', 'now');
            } elseif ($milestone->status === 'pending') {
                $milestone->progress_percentage = 0;
                $milestone->completed_at = null;
            } elseif ($milestone->status === 'in_progress') {
                $milestone->progress_percentage = fake()->numberBetween(1, 99);
                $milestone->completed_at = null;
            }
        });
    }

    /**
     * Indicate that the milestone is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'progress_percentage' => 0,
            'completed_at' => null,
            'due_date' => fake()->dateTimeBetween('+1 week', '+2 months'),
        ]);
    }

    /**
     * Indicate that the milestone is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'progress_percentage' => fake()->numberBetween(10, 90),
            'completed_at' => null,
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Indicate that the milestone is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'actual_hours' => fake()->randomFloat(1, 10, 200),
        ]);
    }

    /**
     * Indicate that the milestone is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => fake()->randomElement(['pending', 'in_progress']),
            'due_date' => fake()->dateTimeBetween('-2 months', '-1 day'),
            'progress_percentage' => fake()->numberBetween(0, 70),
        ]);
    }

    /**
     * Indicate that the milestone is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Indicate that the milestone is critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
            'due_date' => fake()->dateTimeBetween('now', '+2 weeks'),
            'estimated_hours' => fake()->randomFloat(1, 40, 200),
        ]);
    }

    /**
     * Indicate that the milestone is for development phase.
     */
    public function development(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Desarrollo del Módulo Principal',
                'Implementación de Funcionalidades Core',
                'Desarrollo de APIs REST',
                'Integración de Base de Datos',
                'Desarrollo de Interfaz de Usuario',
            ]),
            'estimated_hours' => fake()->randomFloat(1, 60, 300),
            'priority' => fake()->randomElement(['medium', 'high']),
        ]);
    }

    /**
     * Indicate that the milestone is for testing phase.
     */
    public function testing(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Pruebas Unitarias',
                'Pruebas de Integración',
                'Pruebas de Rendimiento',
                'Pruebas de Seguridad',
                'Pruebas de Usuario Final',
            ]),
            'estimated_hours' => fake()->randomFloat(1, 20, 80),
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the milestone is for deployment phase.
     */
    public function deployment(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Despliegue en Staging',
                'Despliegue en Producción',
                'Configuración de Servidores',
                'Migración de Datos',
                'Go-Live y Monitoreo',
            ]),
            'estimated_hours' => fake()->randomFloat(1, 15, 60),
            'priority' => 'critical',
        ]);
    }
}
