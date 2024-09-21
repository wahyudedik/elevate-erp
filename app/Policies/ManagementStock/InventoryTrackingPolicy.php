<?php

namespace App\Policies\ManagementStock;

use App\Models\User;
use App\Models\ManagementStock\InventoryTracking;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryTrackingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_inventory::tracking');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InventoryTracking $inventoryTracking): bool
    {
        return $user->can('view_inventory::tracking');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_inventory::tracking');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InventoryTracking $inventoryTracking): bool
    {
        return $user->can('update_inventory::tracking');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InventoryTracking $inventoryTracking): bool
    {
        return $user->can('delete_inventory::tracking');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_inventory::tracking');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, InventoryTracking $inventoryTracking): bool
    {
        return $user->can('force_delete_inventory::tracking');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_inventory::tracking');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, InventoryTracking $inventoryTracking): bool
    {
        return $user->can('restore_inventory::tracking');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_inventory::tracking');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, InventoryTracking $inventoryTracking): bool
    {
        return $user->can('replicate_inventory::tracking');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_inventory::tracking');
    }
}
