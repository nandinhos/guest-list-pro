<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TicketType;
use App\Models\User;

class TicketTypePolicy
{
    /**
     * @see P3 (DEVORQ review 2026-04-21): FALSE POSITIVE em code review.
     * is_visible e um flag de NEGOCIO (visibilidade na bilheteria), nao um flag
     * de PERMISSAO. A regra ja e aplicada no formulario (TicketSaleForm) via
     * where('is_visible', true) no options() do Select. Policy e para autorizacao
     * (quem pode fazer o que), nao para filtragem de listagem.
     * Se BILHETERIA nao deve ver TicketTypes invisiveis nem no menu, isso e
     * configurado via viewAny - mas para um flag de negocio como is_visible,
     * a filtragem correta esta na camada de formulario, nao na Policy.
     */
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
