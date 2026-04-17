<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use App\Rules\CheckinRule;
use App\Rules\GuestLimitRule;
use App\Rules\TimeWindowRule;

class GuestService
{
    /**
     * Realiza o check-in de um convidado através do token do QR Code (ULID).
     */
    public function checkinByQrToken(string $qrToken, User $validator): array
    {
        $validation = CheckinRule::validateCheckin($validator, $qrToken);

        if (! $validation['allowed']) {
            return [
                'success' => false,
                'message' => $validation['message'],
            ];
        }

        /** @var Guest $guest */
        $guest = $validation['guest'];

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
        $limitCheck = GuestLimitRule::validateLimit($user, $eventId, $sectorId);

        if (! $limitCheck['allowed']) {
            return $limitCheck;
        }

        $timeCheck = TimeWindowRule::validateTimeWindow($user->id, $eventId, $sectorId);

        if (! $timeCheck['allowed']) {
            return $timeCheck;
        }

        return [
            'allowed' => true,
            'remaining' => $limitCheck['remaining'] ?? 0,
        ];
    }

    /**
     * Retorna os eventos onde o promoter tem permissão.
     */
    public function getAuthorizedEvents(User $user)
    {
        return Event::whereIn('id', function ($query) use ($user) {
            $query->select('event_id')
                ->from('event_assignments')
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
                ->from('event_assignments')
                ->where('user_id', $user->id)
                ->where('event_id', $eventId);
        })->get();
    }
}
