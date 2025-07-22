<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $projectNames = [
            'Sistema de Gestión de Inventario',
            'Plataforma E-commerce Corporativa',
            'App Móvil de Delivery',
            'Portal de Recursos Humanos',
            'Sistema de Facturación Electrónica',
            'Dashboard de Analytics',
            'API de Integración Bancaria',
            'Sistema de Gestión Documental',
            'Plataforma de E-learning',
            'Sistema de Control de Acceso',
            'App de Gestión de Proyectos',
            'Portal de Atención al Cliente',
            'Sistema de Monitoreo IoT',
            'Plataforma de Marketing Digital',
            'Sistema de Gestión de Calidad',
        ];

        $descriptions = [
            'Desarrollo de sistema integral para la gestión y control de inventarios con funcionalidades avanzadas de reportería y análisis.',
            'Creación de plataforma e-commerce robusta con integración de pagos, gestión de productos y panel administrativo completo.',
            'Desarrollo de aplicación móvil nativa para servicios de delivery con geolocalización y seguimiento en tiempo real.',
            'Implementación de portal web para gestión de recursos humanos incluyendo nómina, evaluaciones y capacitaciones.',
            'Sistema completo de facturación electrónica con integración a SUNAT y generación automática de comprobantes.',
            'Dashboard interactivo para visualización de métricas de negocio con gráficos dinámicos y reportes personalizables.',
            'Desarrollo de API REST para integración con servicios bancarios y procesamiento de transacciones financieras.',
            'Sistema de gestión documental con control de versiones, flujos de aprobación y búsqueda avanzada.',
            'Plataforma de aprendizaje en línea con cursos interactivos, evaluaciones y seguimiento de progreso.',
            'Sistema biométrico de control de acceso con integración a bases de datos de personal y reportes de asistencia.',
        ];

        $clientNames = [
            'Corporación ABC S.A.',
            'Empresa XYZ Ltda.',
            'Grupo Empresarial DEF',
            'Compañía GHI S.A.C.',
            'Organización JKL',
            'Institución MNO',
            'Empresa PQR Corp.',
            'Grupo STU Holdings',
            'Corporación VWX',
            'Compañía YZ Internacional',
        ];

        $startDate = fake()->dateTimeBetween('-6 months', '-1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');

        return [
            'name' => fake()->randomElement($projectNames),
            'description' => fake()->randomElement($descriptions),
            'status' => fake()->randomElement(['planning', 'in_progress', 'on_hold', 'completed', 'cancelled']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'deadline' => fake()->dateTimeBetween($startDate, $endDate),
            'progress_percentage' => fake()->numberBetween(0, 100),
            'budget' => fake()->randomFloat(2, 5000, 100000),
            'client_name' => fake()->randomElement($clientNames),
            'project_manager_id' => User::factory(),
            'group_id' => fake()->optional(0.7)->randomElement(Group::pluck('id')->toArray()),
            'repository_url' => fake()->optional(0.6)->url(),
            'documentation_url' => fake()->optional(0.4)->url(),
            'is_active' => fake()->boolean(90),
            'settings' => fake()->optional(0.3)->randomElements([
                'notifications_enabled' => fake()->boolean(),
                'auto_assign_tasks' => fake()->boolean(),
                'require_time_tracking' => fake()->boolean(),
                'allow_overtime' => fake()->boolean(),
            ]),
        ];
    }

    /**
     * Indicate that the project is in planning phase.
     */
    public function planning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planning',
            'progress_percentage' => fake()->numberBetween(0, 15),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Indicate that the project is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'progress_percentage' => fake()->numberBetween(16, 85),
            'start_date' => fake()->dateTimeBetween('-3 months', '-1 week'),
        ]);
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress_percentage' => 100,
            'start_date' => fake()->dateTimeBetween('-6 months', '-2 months'),
            'end_date' => fake()->dateTimeBetween('-2 months', '-1 week'),
        ]);
    }

    /**
     * Indicate that the project is on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
            'progress_percentage' => fake()->numberBetween(20, 60),
        ]);
    }

    /**
     * Indicate that the project is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'budget' => fake()->randomFloat(2, 50000, 200000),
        ]);
    }

    /**
     * Indicate that the project is critical priority.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
            'budget' => fake()->randomFloat(2, 100000, 500000),
            'deadline' => fake()->dateTimeBetween('now', '+2 months'),
        ]);
    }

    /**
     * Indicate that the project is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'deadline' => fake()->dateTimeBetween('-2 months', '-1 week'),
            'progress_percentage' => fake()->numberBetween(30, 80),
        ]);
    }

    /**
     * Indicate that the project is a web development project.
     */
    public function webDevelopment(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Portal Web Corporativo',
                'Sistema Web de Gestión',
                'Plataforma Web E-commerce',
                'Dashboard Web Analytics',
                'Portal Web de Servicios',
            ]),
            'repository_url' => 'https://github.com/company/' . fake()->slug(),
            'documentation_url' => 'https://docs.company.com/' . fake()->slug(),
        ]);
    }

    /**
     * Indicate that the project is a mobile app project.
     */
    public function mobileApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'App Móvil de Servicios',
                'Aplicación Mobile Banking',
                'App de Delivery y Logística',
                'Aplicación Móvil Corporativa',
                'App de Gestión Personal',
            ]),
            'settings' => [
                'platforms' => fake()->randomElements(['iOS', 'Android'], 2),
                'min_version' => fake()->randomElement(['iOS 12+', 'Android 8+']),
                'push_notifications' => true,
            ],
        ]);
    }
}
