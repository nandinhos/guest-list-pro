<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/admin');
    }

    public function test_admin_is_redirected_to_admin_panel(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect('/admin');
    }

    public function test_promoter_is_redirected_to_promoter_panel(): void
    {
        $user = User::factory()->create(['role' => UserRole::PROMOTER, 'is_active' => true]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect('/promoter');
    }

    public function test_validator_is_redirected_to_validator_panel(): void
    {
        $user = User::factory()->create(['role' => UserRole::VALIDATOR, 'is_active' => true]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect('/validator');
    }

    public function test_bilheteria_is_redirected_to_bilheteria_panel(): void
    {
        $user = User::factory()->create(['role' => UserRole::BILHETERIA, 'is_active' => true]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect('/bilheteria');
    }

    public function test_invalid_credentials_show_error(): void
    {
        User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'wrong@example.com')
            ->set('password', 'wrongpassword')
            ->call('authenticate')
            ->assertHasErrors(['email']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => false]);

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email']);
    }

    public function test_email_is_required(): void
    {
        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', '')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => 'required']);
    }

    public function test_password_is_required(): void
    {
        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', '')
            ->call('authenticate')
            ->assertHasErrors(['password' => 'required']);
    }

    public function test_rate_limiting_blocks_after_5_attempts(): void
    {
        RateLimiter::clear('login.test@example.com.'.request()->ip());

        $component = Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrongpassword');

        for ($i = 0; $i < 5; $i++) {
            $component->call('authenticate');
        }

        $component->call('authenticate')
            ->assertHasErrors(['email']);
    }

    public function test_logout_destroys_session_and_redirects_to_home(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_accessing_admin_panel_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }
}
