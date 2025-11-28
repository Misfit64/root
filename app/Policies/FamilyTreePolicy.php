<?php

namespace App\Policies;

use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FamilyTreePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, FamilyTree $familyTree): bool
    {
        if ($familyTree->is_public) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $user->id === $familyTree->user_id || 
               $familyTree->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FamilyTree $familyTree): bool
    {
        if ($user->id === $familyTree->user_id) {
            return true;
        }

        return $familyTree->members()
            ->where('user_id', $user->id)
            ->where('role', \App\Enums\TreeRole::Editor->value)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FamilyTree $familyTree): bool
    {
        return $user->id === $familyTree->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FamilyTree $familyTree): bool
    {
        return $user->id === $familyTree->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FamilyTree $familyTree): bool
    {
        return $user->id === $familyTree->user_id;
    }
}
