<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $groupNames = [
            'Equipo de Desarrollo',
            'Analistas de Seguridad',
            'Investigadores Forenses',
            'Auditores Internos',
            'Especialistas en Cumplimiento',
            'Gestores de Calidad',
            'Coordinadores de Proyecto',
            'Expertos en Datos',
            'Consultores Técnicos',
            'Revisores de Evidencias',
            'Administradores de Sistema',
            'Analistas de Riesgo',
            'Especialistas en Privacidad',
            'Gestores de Incidentes',
            'Evaluadores de Procesos',
        ];

        $descriptions = [
            'Grupo dedicado al análisis y revisión de evidencias digitales.',
            'Equipo especializado en investigaciones de seguridad informática.',
            'Grupo de trabajo para auditorías internas y cumplimiento normativo.',
            'Especialistas en gestión de calidad y mejora continua.',
            'Equipo de coordinación de proyectos y seguimiento de tareas.',
            'Grupo de expertos en análisis de datos y métricas.',
            'Consultores técnicos para soporte especializado.',
            'Equipo de revisión y validación de evidencias.',
            'Administradores del sistema y infraestructura.',
            'Analistas especializados en evaluación de riesgos.',
            'Especialistas en protección de datos y privacidad.',
            'Gestores de respuesta a incidentes de seguridad.',
            'Evaluadores de procesos y procedimientos organizacionales.',
        ];

        return [
            'name' => fake()->randomElement($groupNames),
            'description' => fake()->randomElement($descriptions),
            'type' => fake()->randomElement(['public', 'private', 'restricted']),
            'created_by' => User::factory(),
            'is_active' => fake()->boolean(90), // 90% activos
            'settings' => fake()->optional(0.3)->randomElements([
                'allow_file_sharing' => fake()->boolean(),
                'require_approval' => fake()->boolean(),
                'max_members' => fake()->numberBetween(10, 100),
                'auto_archive_days' => fake()->numberBetween(30, 365),
            ]),
        ];
    }

    /**
     * Indicate that the group is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'public',
            'name' => fake()->randomElement([
                'Comunicaciones Generales',
                'Anuncios Corporativos',
                'Recursos Compartidos',
                'Foro Abierto',
                'Colaboración General',
            ]),
        ]);
    }

    /**
     * Indicate that the group is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'private',
            'name' => fake()->randomElement([
                'Equipo Ejecutivo',
                'Proyecto Confidencial',
                'Revisión Interna',
                'Planificación Estratégica',
                'Desarrollo Secreto',
            ]),
        ]);
    }

    /**
     * Indicate that the group is restricted.
     */
    public function restricted(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'restricted',
            'name' => fake()->randomElement([
                'Seguridad Crítica',
                'Auditoría Especial',
                'Investigación Sensible',
                'Cumplimiento Regulatorio',
                'Análisis Forense',
            ]),
        ]);
    }

    /**
     * Indicate that the group is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'name' => 'Proyecto Archivado - ' . fake()->words(2, true),
        ]);
    }

    /**
     * Indicate that the group has many members.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'public',
            'name' => fake()->randomElement([
                'Toda la Organización',
                'Comunicaciones Masivas',
                'Eventos Corporativos',
                'Recursos Generales',
            ]),
            'settings' => [
                'max_members' => 500,
                'allow_file_sharing' => true,
                'require_approval' => false,
            ],
        ]);
    }

    /**
     * Indicate that the group is for a specific department.
     */
    public function department(string $department): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => "Equipo de {$department}",
            'description' => "Grupo de trabajo del departamento de {$department}",
            'type' => 'private',
        ]);
    }

    /**
     * Indicate that the group is for security purposes.
     */
    public function security(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Equipo de Seguridad',
                'Respuesta a Incidentes',
                'Análisis de Amenazas',
                'Monitoreo de Seguridad',
                'Investigación de Brechas',
            ]),
            'type' => 'restricted',
            'description' => 'Grupo especializado en temas de seguridad informática y análisis de amenazas.',
            'settings' => [
                'require_approval' => true,
                'allow_file_sharing' => true,
                'max_members' => 20,
                'auto_archive_days' => 90,
            ],
        ]);
    }

    /**
     * Indicate that the group is for project management.
     */
    public function project(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Proyecto ' . fake()->words(2, true),
            'type' => 'private',
            'description' => 'Grupo de trabajo para coordinación y seguimiento de proyecto específico.',
            'settings' => [
                'require_approval' => false,
                'allow_file_sharing' => true,
                'max_members' => 15,
                'auto_archive_days' => 180,
            ],
        ]);
    }
}
