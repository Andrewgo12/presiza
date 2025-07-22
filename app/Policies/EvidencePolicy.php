<?php

namespace App\Policies;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EvidencePolicy
{
    /**
     * Determine whether the user can view any evidences.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the evidence.
     */
    public function view(User $user, Evidence $evidence): bool
    {
        // Admins pueden ver cualquier evidencia
        if ($user->isAdmin()) {
            return true;
        }

        // El que envió la evidencia puede verla
        if ($evidence->submitted_by === $user->id) {
            return true;
        }

        // El asignado puede ver la evidencia
        if ($evidence->assigned_to === $user->id) {
            return true;
        }

        // Analistas e investigadores pueden ver evidencias no confidenciales
        if (in_array($user->role, ['analyst', 'investigator'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create evidences.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the evidence.
     */
    public function update(User $user, Evidence $evidence): bool
    {
        // Admins pueden actualizar cualquier evidencia
        if ($user->isAdmin()) {
            return true;
        }

        // El que envió la evidencia puede actualizarla si está pendiente
        if ($evidence->submitted_by === $user->id && $evidence->status === 'pending') {
            return true;
        }

        // El asignado puede actualizar la evidencia
        if ($evidence->assigned_to === $user->id) {
            return true;
        }

        // Analistas pueden actualizar evidencias asignadas a ellos o sin asignar
        if ($user->role === 'analyst' && in_array($evidence->status, ['pending', 'under_review'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the evidence.
     */
    public function delete(User $user, Evidence $evidence): bool
    {
        // Solo admins pueden eliminar evidencias
        if ($user->isAdmin()) {
            return true;
        }

        // El que envió la evidencia puede eliminarla solo si está pendiente
        if ($evidence->submitted_by === $user->id && $evidence->status === 'pending') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the evidence status.
     */
    public function updateStatus(User $user, Evidence $evidence): bool
    {
        // Admins pueden cambiar cualquier estado
        if ($user->isAdmin()) {
            return true;
        }

        // Analistas e investigadores pueden cambiar estados de evidencias asignadas
        if (in_array($user->role, ['analyst', 'investigator']) && $evidence->assigned_to === $user->id) {
            return true;
        }

        // Analistas pueden cambiar estados de evidencias sin asignar
        if ($user->role === 'analyst' && !$evidence->assigned_to) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can assign the evidence.
     */
    public function assign(User $user, Evidence $evidence): bool
    {
        // Solo admins y analistas pueden asignar evidencias
        return in_array($user->role, ['admin', 'analyst']);
    }

    /**
     * Determine whether the user can evaluate the evidence.
     */
    public function evaluate(User $user, Evidence $evidence): bool
    {
        // Solo analistas e investigadores pueden evaluar
        if (!in_array($user->role, ['analyst', 'investigator'])) {
            return false;
        }

        // No pueden evaluar sus propias evidencias
        if ($evidence->submitted_by === $user->id) {
            return false;
        }

        // Solo pueden evaluar evidencias en revisión o aprobadas
        if (!in_array($evidence->status, ['under_review', 'approved'])) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the evidence history.
     */
    public function viewHistory(User $user, Evidence $evidence): bool
    {
        // Usar la misma lógica que view
        return $this->view($user, $evidence);
    }

    /**
     * Determine whether the user can restore the evidence.
     */
    public function restore(User $user, Evidence $evidence): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the evidence.
     */
    public function forceDelete(User $user, Evidence $evidence): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export evidence data.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }

    /**
     * Determine whether the user can view analytics.
     */
    public function viewAnalytics(User $user): bool
    {
        return in_array($user->role, ['admin', 'analyst']);
    }
}
