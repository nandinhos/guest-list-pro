<?php

namespace App\Rules;

use App\Models\EventAssignment;
use App\Models\Guest;
use App\Models\User;
use App\Rules\Traits\ValidationResponse;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PlusOneRule implements ValidationRule
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
            $fail($result['message'] ?? 'Não é possível adicionar acompanhante.');
        }
    }

    public function passes(string $attribute, mixed $value): array
    {
        return $this->validatePlusOne($this->user, $this->eventId, $this->sectorId);
    }

    public static function validatePlusOne(User $user, int $eventId, int $sectorId): array
    {
        $permission = EventAssignment::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->first();

        if (! $permission) {
            return self::failure('Você não tem permissão para cadastrar neste setor.');
        }

        if (! $permission->plus_one_enabled) {
            return self::failure('Este setor não permite convidados +1.');
        }

        $companionCount = Guest::where('parent_id', '!=', null)
            ->whereHas('parent', fn ($q) => $q->where('promoter_id', $user->id))
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->count();

        if ($companionCount >= $permission->plus_one_limit) {
            return self::failure("Limite de acompanhantes atingido ({$permission->plus_one_limit}).");
        }

        return self::success(['remaining' => $permission->plus_one_limit - $companionCount]);
    }

    public static function canAddCompanion(User $user, int $eventId, int $sectorId): array
    {
        return self::validatePlusOne($user, $eventId, $sectorId);
    }
}
