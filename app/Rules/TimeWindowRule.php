<?php

namespace App\Rules;

use App\Models\EventAssignment;
use App\Rules\Traits\ValidationResponse;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TimeWindowRule implements ValidationRule
{
    use ValidationResponse;

    public function __construct(
        private int $userId,
        private int $eventId,
        private int $sectorId
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = $this->passes($attribute, $value);

        if (! $result['allowed']) {
            $fail($result['message'] ?? 'Fora do horário permitido.');
        }
    }

    public function passes(string $attribute, mixed $value): array
    {
        return $this->validateTimeWindow($this->userId, $this->eventId, $this->sectorId);
    }

    public static function validateTimeWindow(int $userId, int $eventId, int $sectorId): array
    {
        $permission = EventAssignment::where('user_id', $userId)
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->first();

        if (! $permission) {
            return self::failure('Permissão não encontrada para este setor/evento.');
        }

        if (! self::isWithinWindow($permission)) {
            $now = now();

            if ($permission->start_time && $now < $permission->start_time) {
                return self::failure('O cadastro para este setor só abre às '.$permission->start_time->format('H:i').'.');
            }

            if ($permission->end_time && $now > $permission->end_time) {
                return self::failure('O cadastro para este setor encerrou às '.$permission->end_time->format('H:i').'.');
            }
        }

        return self::success();
    }

    public static function isWithinWindow(EventAssignment $permission): bool
    {
        $now = now();

        if ($permission->start_time && $now < $permission->start_time) {
            return false;
        }

        if ($permission->end_time && $now > $permission->end_time) {
            return false;
        }

        return true;
    }
}
