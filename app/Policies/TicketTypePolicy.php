<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TicketType;
use App\Models\User;

class TicketTypePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BILHETERIA]) && $user->is_active;
    }

    public function view(User $user, TicketType $ticketType): bool
    {
        if ($user->role === UserRole::ADMIN && $user->is_active) {
            return true;
        }

        if ($user->role === UserRole::BILHETERIA && $user->is_active) {
            return $ticketType->event_id === session('selected_event_id');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }

    public function update(User $user, TicketType $ticketType): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }

    public function delete(User $user, TicketType $ticketType): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }
}
