<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Guest;
use App\Models\User;

class GuestPolicy
{
    /**
     * Determine whether the user can view any guests.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role === UserRole::ADMIN && $user->is_active) {
            return true;
        }

        if ($user->role === UserRole::VALIDATOR && $user->is_active) {
            return true;
        }

        if ($user->role === UserRole::PROMOTER && $user->is_active) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the guest.
     */
    public function view(User $user, Guest $guest): bool
    {
        if ($user->role === UserRole::ADMIN && $user->is_active) {
            return true;
        }

        if ($user->role === UserRole::VALIDATOR && $user->is_active) {
            return true;
        }

        if ($user->role === UserRole::PROMOTER && $user->is_active) {
            return $this->userCanAccessGuestSector($user, $guest);
        }

        return false;
    }

    /**
     * Determine whether the user can create guests.
     */
    public function create(User $user): bool
    {
        if ($user->role === UserRole::ADMIN && $user->is_active) {
            return true;
        }

        if ($user->role === UserRole::PROMOTER && $user->is_active) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the guest.
     */
    public function update(User $user, Guest $guest): bool
    {
        if ($user->role === UserRole::ADMIN && $user->is_active) {
            return true;
        }

        if ($user->role === UserRole::PROMOTER && $user->is_active) {
            return $this->userCanAccessGuestSector($user, $guest);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the guest.
     */
    public function delete(User $user, Guest $guest): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }

    /**
     * Determine whether the user can restore the guest.
     */
    public function restore(User $user, Guest $guest): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }

    /**
     * Determine whether the user can force delete the guest.
     */
    public function forceDelete(User $user, Guest $guest): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }

    /**
     * Check if user has permission to access guest's sector.
     */
    private function userCanAccessGuestSector(User $user, Guest $guest): bool
    {
        return $user->permissions()
            ->where('event_id', $guest->event_id)
            ->where('sector_id', $guest->sector_id)
            ->exists();
    }
}
