<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evidence>
 */
class EvidenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Incidente de seguridad en servidor principal',
            'Acceso no autorizado a base de datos',
            'Falla en sistema de respaldos',
            'Violación de protocolo de seguridad',
            'Pérdida de datos en migración',
            'Intento de phishing detectado',
            'Malware encontrado en estación de trabajo',
            'Acceso indebido a información confidencial',
            'Falla en sistema de autenticación',
            'Brecha de seguridad en aplicación web',
            'Uso inadecuado de recursos corporativos',
            'Violación de políticas de privacidad',
            'Incidente de ingeniería social',
            'Fuga de información sensible',
            'Ataque de denegación de servicio',
        ];

        $locations = [
            'Oficina Principal - Piso 3',
            'Centro de Datos - Rack A15',
            'Sucursal Norte',
            'Sala de Servidores',
            'Oficina de Finanzas',
            'Departamento de RRHH',
            'Laboratorio de Desarrollo',
            'Área de Recepción',
            'Sala de Juntas Ejecutiva',
            'Centro de Operaciones',
            'Almacén de Equipos',
            'Oficina Remota',
        ];

        return [
            'title' => fake()->randomElement($titles),
            'description' => fake()->paragraphs(3, true),
            'category' => fake()->randomElement(['security', 'investigation', 'compliance', 'audit', 'incident', 'other']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['pending', 'under_review', 'approved', 'rejected']),
            'submitted_by' => User::factory(),
            'assigned_to' => fake()->optional(0.7)->randomElement(User::pluck('id')->toArray()),
            'metadata' => fake()->optional(0.4)->randomElements([
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'session_id' => fake()->uuid(),
                'affected_systems' => fake()->randomElements(['CRM', 'ERP', 'Email', 'Database'], 2),
                'severity_level' => fake()->numberBetween(1, 10),
                'estimated_impact' => fake()->randomElement(['Low', 'Medium', 'High', 'Critical']),
            ]),
            'incident_date' => fake()->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'location' => fake()->optional(0.6)->randomElement($locations),
            'notes' => fake()->optional(0.5)->paragraph(),
        ];
    }

    /**
     * Indicate that the evidence is critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
            'status' => fake()->randomElement(['pending', 'under_review']),
            'incident_date' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the evidence is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'assigned_to' => null,
        ]);
    }

    /**
     * Indicate that the evidence is under review.
     */
    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_review',
            'assigned_to' => User::factory(),
        ]);
    }

    /**
     * Indicate that the evidence is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'assigned_to' => User::factory(),
        ]);
    }

    /**
     * Indicate that the evidence is a security incident.
     */
    public function security(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'security',
            'priority' => fake()->randomElement(['high', 'critical']),
            'title' => fake()->randomElement([
                'Intento de acceso no autorizado detectado',
                'Malware identificado en sistema',
                'Brecha de seguridad en aplicación',
                'Ataque de phishing reportado',
                'Vulnerabilidad crítica encontrada',
            ]),
            'metadata' => [
                'threat_level' => fake()->randomElement(['Medium', 'High', 'Critical']),
                'attack_vector' => fake()->randomElement(['Email', 'Web', 'Network', 'Physical']),
                'affected_assets' => fake()->randomElements(['Server', 'Database', 'Workstation', 'Network'], 2),
                'indicators_of_compromise' => fake()->randomElements([
                    'Unusual network traffic',
                    'Suspicious file modifications',
                    'Unauthorized login attempts',
                    'Malicious email attachments',
                ], 2),
            ],
        ]);
    }

    /**
     * Indicate that the evidence is an investigation.
     */
    public function investigation(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'investigation',
            'title' => fake()->randomElement([
                'Investigación de uso indebido de recursos',
                'Análisis de actividad sospechosa',
                'Revisión de accesos no autorizados',
                'Investigación de fuga de información',
                'Análisis forense de incidente',
            ]),
            'metadata' => [
                'investigation_type' => fake()->randomElement(['Internal', 'External', 'Regulatory']),
                'evidence_collected' => fake()->randomElements(['Logs', 'Screenshots', 'Documents', 'Testimonies'], 3),
                'timeline' => fake()->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                'key_findings' => fake()->sentence(),
            ],
        ]);
    }

    /**
     * Indicate that the evidence is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'incident_date' => fake()->dateTimeBetween('-7 days', 'now'),
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the evidence is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'priority' => fake()->randomElement(['high', 'critical']),
            'created_at' => fake()->dateTimeBetween('-30 days', '-8 days'),
        ]);
    }
}
