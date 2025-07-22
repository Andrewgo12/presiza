<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjects = [
            'Actualización de procedimientos de seguridad',
            'Revisión de evidencias pendientes',
            'Reunión semanal del equipo',
            'Nuevo protocolo de análisis',
            'Reporte de actividades mensuales',
            'Capacitación en nuevas herramientas',
            'Consulta sobre caso específico',
            'Propuesta de mejora de procesos',
            'Alerta de seguridad importante',
            'Documentación actualizada disponible',
            'Solicitud de revisión urgente',
            'Coordinación de tareas del proyecto',
            'Feedback sobre implementación',
            'Planificación de auditoría',
            'Resultados de investigación',
            'Notificación de cambios en el sistema',
            'Invitación a sesión de trabajo',
            'Recordatorio de fechas límite',
            'Compartir mejores prácticas',
            'Solicitud de apoyo técnico',
        ];

        $contentTemplates = [
            'Estimado equipo,\n\n{content}\n\nSaludos cordiales.',
            'Hola a todos,\n\n{content}\n\nQuedo atento a sus comentarios.',
            'Buenos días,\n\n{content}\n\nGracias por su atención.',
            'Equipo,\n\n{content}\n\nPor favor confirmen recepción.',
            'Colegas,\n\n{content}\n\nEspero sus comentarios.',
        ];

        $content = fake()->paragraphs(rand(1, 3), true);
        $template = fake()->randomElement($contentTemplates);
        $formattedContent = str_replace('{content}', $content, $template);

        return [
            'subject' => fake()->randomElement($subjects),
            'content' => $formattedContent,
            'sender_id' => User::factory(),
            'type' => fake()->randomElement(['direct', 'group']),
            'group_id' => null, // Se establecerá según el tipo
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'attachments' => fake()->optional(0.2)->randomElements([
                ['name' => 'documento.pdf', 'size' => '1.2MB'],
                ['name' => 'imagen.jpg', 'size' => '856KB'],
                ['name' => 'reporte.xlsx', 'size' => '2.1MB'],
            ], rand(1, 2)),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function ($message) {
            // Si es mensaje de grupo, asignar un grupo
            if ($message->type === 'group') {
                $message->group_id = Group::inRandomOrder()->first()?->id;
            }
        });
    }

    /**
     * Indicate that the message is direct.
     */
    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'direct',
            'group_id' => null,
        ]);
    }

    /**
     * Indicate that the message is for a group.
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'group',
            'group_id' => Group::factory(),
        ]);
    }

    /**
     * Indicate that the message is a system message.
     */
    public function system(): static
    {
        $systemSubjects = [
            'Mantenimiento programado del sistema',
            'Actualización de seguridad aplicada',
            'Nuevo usuario registrado en el sistema',
            'Backup completado exitosamente',
            'Alerta de capacidad de almacenamiento',
            'Actualización de políticas de privacidad',
            'Notificación de cambio de contraseña',
            'Recordatorio de renovación de certificados',
        ];

        return $this->state(fn (array $attributes) => [
            'type' => 'system',
            'group_id' => null,
            'subject' => fake()->randomElement($systemSubjects),
            'content' => 'Este es un mensaje automático del sistema.\n\n' . fake()->paragraph(),
            'priority' => fake()->randomElement(['normal', 'high']),
            'sender_id' => User::where('role', 'admin')->inRandomOrder()->first()?->id ?? User::factory(),
        ]);
    }

    /**
     * Indicate that the message has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'subject' => '[IMPORTANTE] ' . fake()->randomElement([
                'Revisión urgente requerida',
                'Incidente de seguridad detectado',
                'Fecha límite próxima',
                'Acción inmediata necesaria',
                'Escalamiento de caso crítico',
            ]),
        ]);
    }

    /**
     * Indicate that the message is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
            'subject' => '[URGENTE] ' . fake()->randomElement([
                'Brecha de seguridad crítica',
                'Sistema comprometido',
                'Respuesta inmediata requerida',
                'Escalamiento ejecutivo',
                'Incidente mayor en curso',
            ]),
            'content' => 'ATENCIÓN INMEDIATA REQUERIDA\n\n' . fake()->paragraph() . '\n\nPor favor responder de inmediato.',
        ]);
    }

    /**
     * Indicate that the message has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => [
                ['name' => 'reporte_evidencias.pdf', 'size' => '2.3MB'],
                ['name' => 'analisis_forense.docx', 'size' => '1.8MB'],
                ['name' => 'capturas_pantalla.zip', 'size' => '5.1MB'],
            ],
        ]);
    }

    /**
     * Indicate that the message is a reply.
     */
    public function reply(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Re: ' . fake()->randomElement([
                'Consulta sobre procedimiento',
                'Revisión de documentación',
                'Seguimiento de caso',
                'Actualización de estado',
                'Confirmación de recepción',
            ]),
            'content' => 'En respuesta a su mensaje anterior:\n\n' . fake()->paragraph() . '\n\nQuedo a su disposición.',
        ]);
    }

    /**
     * Indicate that the message is a forward.
     */
    public function forward(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Fwd: ' . fake()->randomElement([
                'Información importante',
                'Actualización de procedimientos',
                'Notificación relevante',
                'Documentación compartida',
                'Alerta de seguridad',
            ]),
            'content' => 'Les reenvío esta información que puede ser de su interés:\n\n--- Mensaje reenviado ---\n\n' . fake()->paragraph(),
        ]);
    }

    /**
     * Indicate that the message is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the message is old.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }
}
