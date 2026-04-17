<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\EventAssignment;
use App\Models\Guest;
use App\Models\User;
use App\Rules\Traits\ValidationResponse;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GuestLimitRule implements ValidationRule
{
    use ValidationResponse;

    public function __construct(
        private User $user,
        private int $eventId,
        private int $sectorId
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = $this->passes($attribute, $value);

        if (! $result['allowed']) {
            $fail($result['message'] ?? 'Limite de convidados excedido.');
        }
    }

    public function passes(string $attribute, mixed $value): array
    {
        return $this->validateLimit($this->user, $this->eventId, $this->sectorId);
    }

    public static function validateLimit(User $user, int $eventId, int $sectorId): array
    {
        if ($user->role !== UserRole::PROMOTER || ! $user->is_active) {
            return self::failure('Usuário sem permissão de promoter ou inativo.');
        }

        $permission = EventAssignment::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->first();

        if (! $permission) {
            return self::failure('Você não tem permissão para cadastrar convidados neste setor/evento.');
        }

        $count = Guest::where('promoter_id', $user->id)
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->count();

        if ($count >= $permission->guest_limit) {
            return self::failure("Limite de convidados atingido ({$permission->guest_limit}).");
        }

        return self::success(['remaining' => $permission->guest_limit - $count]);
    }

    public static function getRemaining(User $user, int $eventId, int $sectorId): int
    {
        $result = self::validateLimit($user, $eventId, $sectorId);

        return $result['allowed'] ? ($result['remaining'] ?? 0) : 0;
    }
}
