<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Evidence;
use App\Models\Project;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $evidences = Evidence::all();
        $projects = Project::all();
        $messages = Message::all();

        foreach ($users as $user) {
            // Create 5-15 notifications per user
            $notificationCount = rand(5, 15);
            
            for ($i = 0; $i < $notificationCount; $i++) {
                $this->createRandomNotification($user, $evidences, $projects, $messages);
            }
        }

        $this->command->info('Notificaciones creadas exitosamente.');
    }

    private function createRandomNotification($user, $evidences, $projects, $messages): void
    {
        $notificationTypes = [
            'evidence_assigned',
            'evidence_status_changed',
            'message_received',
            'project_invitation',
            'milestone_deadline',
            'system_update',
        ];

        $type = $notificationTypes[array_rand($notificationTypes)];
        $createdAt = now()->subDays(rand(0, 30));
        $readAt = rand(0, 1) ? $createdAt->copy()->addHours(rand(1, 48)) : null;

        $notificationData = $this->getNotificationData($type, $user, $evidences, $projects, $messages);

        DB::table('notifications')->insert([
            'id' => Str::uuid(),
            'type' => $notificationData['type'],
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => json_encode($notificationData['data']),
            'read_at' => $readAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function getNotificationData($type, $user, $evidences, $projects, $messages): array
    {
        switch ($type) {
            case 'evidence_assigned':
                $evidence = $evidences->random();
                return [
                    'type' => 'App\\Notifications\\EvidenceAssigned',
                    'data' => [
                        'title' => 'Nueva evidencia asignada',
                        'message' => "Se te ha asignado la evidencia: {$evidence->title}",
                        'evidence_id' => $evidence->id,
                        'evidence_title' => $evidence->title,
                        'assigned_by' => $evidence->submitted_by,
                        'url' => "/evidences/{$evidence->id}",
                    ]
                ];

            case 'evidence_status_changed':
                $evidence = $evidences->random();
                $statuses = ['pending', 'approved', 'rejected', 'under_review'];
                $newStatus = $statuses[array_rand($statuses)];
                return [
                    'type' => 'App\\Notifications\\EvidenceStatusChanged',
                    'data' => [
                        'title' => 'Estado de evidencia actualizado',
                        'message' => "La evidencia '{$evidence->title}' cambió a estado: {$newStatus}",
                        'evidence_id' => $evidence->id,
                        'evidence_title' => $evidence->title,
                        'old_status' => 'pending',
                        'new_status' => $newStatus,
                        'url' => "/evidences/{$evidence->id}",
                    ]
                ];

            case 'message_received':
                $message = $messages->random();
                return [
                    'type' => 'App\\Notifications\\MessageReceived',
                    'data' => [
                        'title' => 'Nuevo mensaje recibido',
                        'message' => "Tienes un nuevo mensaje: {$message->subject}",
                        'message_id' => $message->id,
                        'message_subject' => $message->subject,
                        'sender_name' => $message->sender->full_name,
                        'sender_id' => $message->sender_id,
                        'url' => "/messages/{$message->id}",
                    ]
                ];

            case 'project_invitation':
                $project = $projects->random();
                return [
                    'type' => 'App\\Notifications\\ProjectInvitation',
                    'data' => [
                        'title' => 'Invitación a proyecto',
                        'message' => "Has sido invitado al proyecto: {$project->name}",
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'invited_by' => $project->created_by,
                        'role' => 'member',
                        'url' => "/projects/{$project->id}",
                    ]
                ];

            case 'milestone_deadline':
                $project = $projects->random();
                return [
                    'type' => 'App\\Notifications\\MilestoneDeadline',
                    'data' => [
                        'title' => 'Milestone próximo a vencer',
                        'message' => "El milestone del proyecto '{$project->name}' vence pronto",
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'milestone_title' => 'Milestone importante',
                        'due_date' => now()->addDays(3)->toDateString(),
                        'url' => "/projects/{$project->id}/milestones",
                    ]
                ];

            case 'system_update':
                return [
                    'type' => 'App\\Notifications\\SystemUpdate',
                    'data' => [
                        'title' => 'Actualización del sistema',
                        'message' => 'El sistema ha sido actualizado con nuevas funcionalidades',
                        'version' => '2.1.0',
                        'features' => [
                            'Mejoras en el rendimiento',
                            'Nueva interfaz de usuario',
                            'Corrección de errores'
                        ],
                        'url' => '/dashboard',
                    ]
                ];

            default:
                return [
                    'type' => 'App\\Notifications\\GeneralNotification',
                    'data' => [
                        'title' => 'Notificación general',
                        'message' => 'Tienes una nueva notificación en el sistema',
                        'url' => '/dashboard',
                    ]
                ];
        }
    }
}
