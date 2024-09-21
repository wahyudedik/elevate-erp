<?php

namespace App\Policies\ManagementCRM;

use App\Models\User;
use App\Models\ManagementCRM\TicketResponse;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketResponsePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_ticket::response');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketResponse $ticketResponse): bool
    {
        return $user->can('view_ticket::response');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_ticket::response');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketResponse $ticketResponse): bool
    {
        return $user->can('update_ticket::response');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketResponse $ticketResponse): bool
    {
        return $user->can('delete_ticket::response');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_ticket::response');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, TicketResponse $ticketResponse): bool
    {
        return $user->can('force_delete_ticket::response');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_ticket::response');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, TicketResponse $ticketResponse): bool
    {
        return $user->can('restore_ticket::response');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_ticket::response');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, TicketResponse $ticketResponse): bool
    {
        return $user->can('replicate_ticket::response');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_ticket::response');
    }
}
