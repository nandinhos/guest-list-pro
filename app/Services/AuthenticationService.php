<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthenticationService
{
    /**
     * Mapa de roles para URLs dos painéis.
     *
     * @var array<string, string>
     */
    private const PANEL_ROUTES = [
        'admin' => '/admin',
        'promoter' => '/promoter',
        'validator' => '/validator',
        'bilheteria' => '/bilheteria',
    ];

    /**
     * Autentica o usuário e retorna a URL de redirecionamento.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(string $email, string $password, bool $remember = false): string
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Sua conta está inativa. Entre em contato com o administrador.',
            ]);
        }

        session()->regenerate();

        return $this->getPanelUrl($user->role);
    }

    /**
     * Retorna a URL do painel baseado na role do usuário.
     */
    public function getPanelUrl(UserRole $role): string
    {
        return self::PANEL_ROUTES[$role->value] ?? '/';
    }
}
