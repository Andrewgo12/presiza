<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    /**
     * Determine whether the user can view any messages.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        // Admins pueden ver cualquier mensaje
        if ($user->isAdmin()) {
            return true;
        }

        // El remitente puede ver su mensaje
        if ($message->sender_id === $user->id) {
            return true;
        }

        // Los destinatarios pueden ver el mensaje
        if ($message->recipients()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Para mensajes de grupo, verificar membresía
        if ($message->type === 'group' && $message->group) {
            return $message->group->hasMember($user);
        }

        return false;
    }

    /**
     * Determine whether the user can create messages.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can reply to the message.
     */
    public function reply(User $user, Message $message): bool
    {
        // Debe poder ver el mensaje para responder
        if (!$this->view($user, $message)) {
            return false;
        }

        // No puede responder a sus propios mensajes
        if ($message->sender_id === $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can forward the message.
     */
    public function forward(User $user, Message $message): bool
    {
        // Debe poder ver el mensaje para reenviarlo
        if (!$this->view($user, $message)) {
            return false;
        }

        // No se pueden reenviar mensajes confidenciales a menos que sea admin
        if ($message->priority === 'urgent' && !$user->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        // Solo el remitente o admin pueden eliminar
        return $message->sender_id === $user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can mark the message as read/unread.
     */
    public function toggleRead(User $user, Message $message): bool
    {
        // Solo los destinatarios pueden marcar como leído/no leído
        return $message->recipients()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can send system messages.
     */
    public function sendSystem(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can send messages to groups.
     */
    public function sendToGroup(User $user, $group): bool
    {
        // Debe ser miembro del grupo
        return $group->hasMember($user);
    }

    /**
     * Determine whether the user can export messages.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }

    /**
     * Determine whether the user can view message statistics.
     */
    public function viewStats(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }
}
