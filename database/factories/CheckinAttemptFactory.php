<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\CheckinAttempt;
use App\Models\Event;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CheckinAttempt>
 */
class CheckinAttemptFactory extends Factory
{
    protected $model = CheckinAttempt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'validator_id' => User::factory()->state(['role' => UserRole::VALIDATOR]),
            'guest_id' => Guest::factory(),
            'search_query' => fake()->name(),
            'result' => 'success',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * State: Check-in bem-sucedido.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'success',
        ]);
    }

    /**
     * State: Convidado não encontrado.
     */
    public function notFound(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'not_found',
            'guest_id' => null,
        ]);
    }

    /**
     * State: Check-in duplicado (já realizado antes).
     */
    public function alreadyCheckedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'already_checked_in',
        ]);
    }

    /**
     * State: Estorno de check-in.
     */
    public function estorno(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'estorno',
        ]);
    }

    /**
     * State: Tentativa suspeita (para testes de widgets).
     */
    public function suspicious(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => fake()->randomElement(['not_found', 'already_checked_in']),
        ]);
    }

    /**
     * State: Sem guest associado.
     */
    public function withoutGuest(): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_id' => null,
        ]);
    }

    /**
     * State: Para um evento específico.
     */
    public function forEvent(Event $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $event->id,
        ]);
    }

    /**
     * State: Por um validador específico.
     */
    public function byValidator(User $validator): static
    {
        return $this->state(fn (array $attributes) => [
            'validator_id' => $validator->id,
        ]);
    }
}
