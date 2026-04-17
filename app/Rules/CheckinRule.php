<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\Guest;
use App\Models\User;
use App\Rules\Traits\ValidationResponse;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckinRule implements ValidationRule
{
    use ValidationResponse;

    public function __construct(
        private User $validator
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = $this->passes($attribute, $value);

        if (! $result['allowed']) {
            $fail($result['message'] ?? 'Não é possível realizar o check-in.');
        }
    }

    public function passes(string $attribute, mixed $qrToken): array
    {
        return $this->validateCheckin($this->validator, $qrToken);
    }

    public static function validateCheckin(User $validator, string $qrToken): array
    {
        if (! in_array($validator->role, [UserRole::ADMIN, UserRole::VALIDATOR])) {
            return self::failure('Você não tem permissão para realizar check-ins.');
        }

        $guest = Guest::where('qr_token', $qrToken)->first();

        if (! $guest) {
            return self::failure('Convidado não encontrado.');
        }

        if ($guest->is_checked_in) {
            return self::failure('Este convidado já realizou o check-in.', ['guest' => $guest]);
        }

        return self::success(['guest' => $guest]);
    }

    public static function canCheckin(User $validator, Guest $guest): array
    {
        if (! in_array($validator->role, [UserRole::ADMIN, UserRole::VALIDATOR])) {
            return self::failure('Você não tem permissão para realizar check-ins.');
        }

        if ($guest->is_checked_in) {
            return self::failure('Este convidado já realizou o check-in.');
        }

        return self::success();
    }
}
