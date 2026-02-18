<?php

namespace App\Livewire\Auth;

use App\Services\AuthenticationService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Rule(['required', 'string', 'email'])]
    public string $email = '';

    #[Rule(['required', 'string'])]
    public string $password = '';

    public bool $remember = false;

    public function authenticate(AuthenticationService $authService): void
    {
        $this->validate();

        $throttleKey = Str::lower($this->email).'.'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Muitas tentativas. Tente novamente em {$seconds} segundos.",
            ]);
        }

        try {
            $redirectUrl = $authService->authenticate($this->email, $this->password, $this->remember);

            RateLimiter::clear($throttleKey);

            $this->redirect($redirectUrl, navigate: false);
        } catch (ValidationException $e) {
            RateLimiter::hit($throttleKey, 60);

            throw $e;
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.auth.login');
    }
}
