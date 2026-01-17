<?php

namespace App\Services;

use App\Models\User;
use App\Models\Event;
use App\Models\Sector;
use App\Models\Guest;
use App\Models\PromoterPermission;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuestService
{
    /**
     * Verifica se o promoter pode cadastrar convidados para um determinado evento e setor.
     */
    public function canRegisterGuest(User $user, int $eventId, int $sectorId): array
    {
        // 1. Verificar se o usuário é um promoter ativo
        if ($user->role !== \App\Enums\UserRole::PROMOTER || !$user->is_active) {
            return [
                'allowed' => false,
                'message' => 'Usuário sem permissão de promoter ou inativo.'
            ];
        }

        // 2. Buscar a permissão específica
        $permission = PromoterPermission::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->first();

        if (!$permission) {
            return [
                'allowed' => false,
                'message' => 'Você não tem permissão para cadastrar convidados neste setor/evento.'
            ];
        }

        // 3. Verificar janela de horário (se definida)
        $now = now();
        if ($permission->start_time && $now->format('H:i:s') < $permission->start_time) {
            return [
                'allowed' => false,
                'message' => "O cadastro para este setor só abre às " . Carbon::parse($permission->start_time)->format('H:i') . "."
            ];
        }

        if ($permission->end_time && $now->format('H:i:s') > $permission->end_time) {
            return [
                'allowed' => false,
                'message' => "O cadastro para este setor encerrou às " . Carbon::parse($permission->end_time)->format('H:i') . "."
            ];
        }

        // 4. Verificar limite de convidados
        $count = Guest::where('promoter_id', $user->id)
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->count();

        if ($count >= $permission->guest_limit) {
            return [
                'allowed' => false,
                'message' => "Limite de convidados atingido ({$permission->guest_limit})."
            ];
        }

        return [
            'allowed' => true,
            'remaining' => $permission->guest_limit - $count
        ];
    }

    /**
     * Retorna os eventos onde o promoter tem permissão.
     */
    public function getAuthorizedEvents(User $user)
    {
        return Event::whereIn('id', function($query) use ($user) {
            $query->select('event_id')
                ->from('promoter_permissions')
                ->where('user_id', $user->id);
        })->get();
    }

    /**
     * Retorna os setores autorizados para um evento.
     */
    public function getAuthorizedSectors(User $user, int $eventId)
    {
        return Sector::whereIn('id', function($query) use ($user, $eventId) {
            $query->select('sector_id')
                ->from('promoter_permissions')
                ->where('user_id', $user->id)
                ->where('event_id', $eventId);
        })->get();
    }
}
