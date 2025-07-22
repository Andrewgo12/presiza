<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $groups = Group::all();

        // Create direct messages between users
        $this->createDirectMessages($users);

        // Create group messages
        $this->createGroupMessages($users, $groups);

        $this->command->info('Mensajes creados exitosamente.');
    }

    private function createDirectMessages($users): void
    {
        // Create 50 direct messages
        for ($i = 0; $i < 50; $i++) {
            $sender = $users->random();
            $recipient = $users->where('id', '!=', $sender->id)->random();

            $message = Message::create([
                'sender_id' => $sender->id,
                'subject' => $this->getRandomSubject(),
                'content' => $this->getRandomContent(),
                'priority' => $this->getRandomPriority(),
                'type' => 'direct',
                'created_at' => now()->subDays(rand(0, 30)),
            ]);

            // Attach recipient
            $message->recipients()->attach($recipient->id, [
                'read_at' => rand(0, 1) ? now()->subDays(rand(0, 5)) : null,
                'created_at' => $message->created_at,
                'updated_at' => $message->created_at,
            ]);

            // 30% chance of having a reply
            if (rand(0, 9) < 3) {
                $this->createReply($message, $recipient, $sender);
            }
        }
    }

    private function createGroupMessages($users, $groups): void
    {
        foreach ($groups as $group) {
            $groupMembers = $group->members;
            
            if ($groupMembers->count() < 2) continue;

            // Create 5-10 messages per group
            $messageCount = rand(5, 10);
            
            for ($i = 0; $i < $messageCount; $i++) {
                $sender = $groupMembers->random();

                $message = Message::create([
                    'sender_id' => $sender->id,
                    'subject' => $this->getRandomGroupSubject($group->name),
                    'content' => $this->getRandomGroupContent(),
                    'priority' => $this->getRandomPriority(),
                    'type' => 'group',
                    'group_id' => $group->id,
                    'created_at' => now()->subDays(rand(0, 15)),
                ]);

                // Attach all group members as recipients
                foreach ($groupMembers as $member) {
                    if ($member->id !== $sender->id) {
                        $message->recipients()->attach($member->id, [
                            'read_at' => rand(0, 1) ? now()->subDays(rand(0, 3)) : null,
                            'created_at' => $message->created_at,
                            'updated_at' => $message->created_at,
                        ]);
                    }
                }
            }
        }
    }

    private function createReply($originalMessage, $sender, $recipient): void
    {
        $reply = Message::create([
            'sender_id' => $sender->id,
            'subject' => 'Re: ' . $originalMessage->subject,
            'content' => $this->getRandomReplyContent(),
            'priority' => $originalMessage->priority,
            'type' => 'direct',
            'parent_id' => $originalMessage->id,
            'created_at' => $originalMessage->created_at->addHours(rand(1, 24)),
        ]);

        $reply->recipients()->attach($recipient->id, [
            'read_at' => rand(0, 1) ? now()->subDays(rand(0, 2)) : null,
            'created_at' => $reply->created_at,
            'updated_at' => $reply->created_at,
        ]);
    }

    private function getRandomSubject(): string
    {
        $subjects = [
            'Consulta sobre evidencia #EV-001',
            'Revisión de documentos pendientes',
            'Actualización de estado del proyecto',
            'Solicitud de información adicional',
            'Reunión de seguimiento programada',
            'Entrega de reporte mensual',
            'Validación de datos requerida',
            'Aprobación de evidencia solicitada',
            'Cambios en el cronograma',
            'Notificación de nueva asignación',
            'Feedback sobre implementación',
            'Coordinación de actividades',
            'Resolución de incidencia',
            'Actualización de procedimientos',
            'Solicitud de acceso al sistema',
        ];

        return $subjects[array_rand($subjects)];
    }

    private function getRandomContent(): string
    {
        $contents = [
            'Hola, espero que estés bien. Te escribo para consultarte sobre el estado de la evidencia que te fue asignada la semana pasada. ¿Podrías proporcionarme una actualización?',
            'Buenos días, necesito que revises los documentos que adjunté en el sistema. Es importante que los valides antes del viernes.',
            'Te informo que hemos actualizado el cronograma del proyecto. Por favor revisa las nuevas fechas y confirma tu disponibilidad.',
            'Hola, ¿podrías proporcionarme información adicional sobre el caso que estás manejando? Necesito algunos detalles para el reporte.',
            'Recordatorio: tenemos reunión de seguimiento mañana a las 10:00 AM. Por favor confirma tu asistencia.',
            'Adjunto el reporte mensual para tu revisión. Si tienes comentarios, por favor házmelos saber.',
            'Necesito que valides los datos que ingresaste en el sistema. Hay algunas inconsistencias que debemos resolver.',
            'Por favor revisa y aprueba la evidencia que subí al sistema. Es urgente para el cierre del caso.',
            'Te informo que hemos realizado algunos cambios en el cronograma debido a nuevos requerimientos del cliente.',
            'Se te ha asignado una nueva tarea en el proyecto. Por favor revisa los detalles en el sistema.',
        ];

        return $contents[array_rand($contents)];
    }

    private function getRandomGroupSubject($groupName): string
    {
        $subjects = [
            "Reunión semanal - $groupName",
            "Actualización del proyecto - $groupName",
            "Nuevos procedimientos para $groupName",
            "Reporte de avances - $groupName",
            "Coordinación de actividades - $groupName",
            "Feedback del equipo - $groupName",
            "Planificación mensual - $groupName",
            "Resolución de incidencias - $groupName",
            "Capacitación programada - $groupName",
            "Evaluación de resultados - $groupName",
        ];

        return $subjects[array_rand($subjects)];
    }

    private function getRandomGroupContent(): string
    {
        $contents = [
            'Equipo, espero que todos estén bien. Quería compartir con ustedes las actualizaciones más importantes de esta semana.',
            'Buenos días a todos. Les escribo para coordinar las actividades de la próxima semana y asegurar que estemos alineados.',
            'Hola equipo, adjunto el reporte de avances del proyecto. Por favor revisen y compartan sus comentarios.',
            'Recordatorio para todos: tenemos reunión de equipo el viernes. Por favor confirmen su asistencia.',
            'Quería felicitar al equipo por el excelente trabajo realizado esta semana. Sigamos con el mismo ritmo.',
            'Necesitamos coordinar mejor nuestras actividades. Propongo que implementemos una nueva metodología.',
            'Por favor todos revisen los nuevos procedimientos que se han implementado y confirmen que los entienden.',
            'Equipo, necesito que me proporcionen feedback sobre el proceso actual. ¿Qué podemos mejorar?',
            'Les informo que tendremos una capacitación la próxima semana. Los detalles están en el calendario.',
            'Hola a todos, es momento de evaluar nuestros resultados del mes. Preparemos el reporte conjunto.',
        ];

        return $contents[array_rand($contents)];
    }

    private function getRandomReplyContent(): string
    {
        $replies = [
            'Gracias por tu mensaje. He revisado la información y todo está en orden.',
            'Perfecto, procederé con lo solicitado y te mantendré informado del progreso.',
            'Entendido. Confirmo mi disponibilidad para la reunión.',
            'He revisado los documentos y tengo algunas observaciones que te enviaré por separado.',
            'Gracias por la actualización. ¿Podríamos programar una llamada para discutir los detalles?',
            'Recibido. Trabajaré en esto y te enviaré los resultados antes del plazo establecido.',
            'Perfecto, gracias por la información. Procederé según las instrucciones.',
            'Confirmo que he recibido la asignación. Comenzaré a trabajar en ello inmediatamente.',
            'Gracias por el recordatorio. Estaré presente en la reunión.',
            'Entendido. Si necesitas algo más, no dudes en contactarme.',
        ];

        return $replies[array_rand($replies)];
    }

    private function getRandomPriority(): string
    {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $weights = [40, 35, 20, 5]; // Most messages are low/medium priority
        
        $random = rand(1, 100);
        if ($random <= 40) return 'low';
        if ($random <= 75) return 'medium';
        if ($random <= 95) return 'high';
        return 'urgent';
    }
}
