<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Guest;
use App\Models\PromoterPermission;
use App\Models\Sector;
use App\Models\User;
use Carbon\Carbon;

class GuestService
{
    /**
     * Realiza o check-in de um convidado através do token do QR Code (ULID).
     */
    public function checkinByQrToken(string $qrToken, User $validator): array
    {
        // 1. Verificar permissão do usuário que realiza a ação
        if (! in_array($validator->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::VALIDATOR])) {
            return [
                'success' => false,
                'message' => 'Você não tem permissão para realizar check-ins.',
            ];
        }

        // 2. Buscar convidado pelo token
        $guest = Guest::where('qr_token', $qrToken)->first();

        if (! $guest) {
            return [
                'success' => false,
                'message' => 'Convidado não encontrado.',
            ];
        }

        // 3. Verificar se já realizou check-in
        if ($guest->is_checked_in) {
            return [
                'success' => false,
                'message' => 'Este convidado já realizou o check-in.',
            ];
        }

        // 4. Realizar check-in
        $guest->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
            'checked_in_by' => $validator->id,
        ]);

        return [
            'success' => true,
            'message' => 'Check-in realizado com sucesso!',
            'guest' => $guest,
        ];
    }

    /**
     * Verifica se o promoter pode cadastrar convidados para um determinado evento e setor.
     */
    public function canRegisterGuest(User $user, int $eventId, int $sectorId): array
    {
        // 1. Verificar se o usuário é um promoter ativo
        if ($user->role !== \App\Enums\UserRole::PROMOTER || ! $user->is_active) {
            return [
                'allowed' => false,
                'message' => 'Usuário sem permissão de promoter ou inativo.',
            ];
        }

        // 2. Buscar a permissão específica
        $permission = PromoterPermission::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->first();

        if (! $permission) {
            return [
                'allowed' => false,
                'message' => 'Você não tem permissão para cadastrar convidados neste setor/evento.',
            ];
        }

        // 3. Verificar janela de horário (se definida)
        $now = now();
        if ($permission->start_time && $now->format('H:i:s') < $permission->start_time) {
            return [
                'allowed' => false,
                'message' => 'O cadastro para este setor só abre às '.Carbon::parse($permission->start_time)->format('H:i').'.',
            ];
        }

        if ($permission->end_time && $now->format('H:i:s') > $permission->end_time) {
            return [
                'allowed' => false,
                'message' => 'O cadastro para este setor encerrou às '.Carbon::parse($permission->end_time)->format('H:i').'.',
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
                'message' => "Limite de convidados atingido ({$permission->guest_limit}).",
            ];
        }

        return [
            'allowed' => true,
            'remaining' => $permission->guest_limit - $count,
        ];
    }

    /**
     * Retorna os eventos onde o promoter tem permissão.
     */
    public function getAuthorizedEvents(User $user)
    {
        return Event::whereIn('id', function ($query) use ($user) {
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
        return Sector::whereIn('id', function ($query) use ($user, $eventId) {
            $query->select('sector_id')
                ->from('promoter_permissions')
                ->where('user_id', $user->id)
                ->where('event_id', $eventId);
        })->get();
    }
}
