<?php

namespace App\Notifications;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvidenceStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $evidence;
    protected $oldStatus;
    protected $newStatus;
    protected $changedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Evidence $evidence, string $oldStatus, string $newStatus, User $changedBy)
    {
        $this->evidence = $evidence;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusLabels = [
            'pending' => 'Pendiente',
            'under_review' => 'En Revisión',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
            'archived' => 'Archivada'
        ];

        return (new MailMessage)
            ->subject('Estado de Evidencia Actualizado')
            ->greeting('Hola ' . $notifiable->first_name . ',')
            ->line('El estado de la evidencia "' . $this->evidence->title . '" ha sido actualizado.')
            ->line('Estado anterior: ' . ($statusLabels[$this->oldStatus] ?? $this->oldStatus))
            ->line('Estado actual: ' . ($statusLabels[$this->newStatus] ?? $this->newStatus))
            ->line('Actualizado por: ' . $this->changedBy->full_name)
            ->action('Ver Evidencia', route('evidences.show', $this->evidence))
            ->line('Gracias por usar nuestro sistema de gestión de evidencias.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'evidence_id' => $this->evidence->id,
            'evidence_title' => $this->evidence->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_by' => [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->full_name,
            ],
            'message' => "El estado de la evidencia \"{$this->evidence->title}\" cambió a {$this->newStatus}",
            'action_url' => route('evidences.show', $this->evidence),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'evidence_status_changed';
    }
}
