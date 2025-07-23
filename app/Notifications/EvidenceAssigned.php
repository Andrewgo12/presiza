<?php

namespace App\Notifications;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvidenceAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $evidence;
    protected $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Evidence $evidence, User $assignedBy)
    {
        $this->evidence = $evidence;
        $this->assignedBy = $assignedBy;
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
        return (new MailMessage)
            ->subject('Nueva Evidencia Asignada')
            ->greeting('Hola ' . $notifiable->first_name . ',')
            ->line('Se te ha asignado una nueva evidencia para revisión.')
            ->line('Título: ' . $this->evidence->title)
            ->line('Prioridad: ' . ucfirst($this->evidence->priority))
            ->line('Asignado por: ' . $this->assignedBy->full_name)
            ->action('Ver Evidencia', route('evidences.show', $this->evidence))
            ->line('Por favor, revisa la evidencia lo antes posible.');
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
            'evidence_priority' => $this->evidence->priority,
            'assigned_by' => [
                'id' => $this->assignedBy->id,
                'name' => $this->assignedBy->full_name,
            ],
            'message' => "Se te ha asignado la evidencia \"{$this->evidence->title}\"",
            'action_url' => route('evidences.show', $this->evidence),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'evidence_assigned';
    }
}
