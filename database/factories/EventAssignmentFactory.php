<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventAssignment>
 */
class EventAssignmentFactory extends Factory
{
    protected $model = EventAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => UserRole::PROMOTER]),
            'event_id' => Event::factory(),
            'sector_id' => Sector::factory(),
            'role' => UserRole::PROMOTER->value,
            'guest_limit' => fake()->numberBetween(10, 100),
            'start_time' => now()->setTime(18, 0),
            'end_time' => now()->setTime(23, 59),
        ];
    }

    /**
     * State: Atribuição para promoter.
     */
    public function forPromoter(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->state(['role' => UserRole::PROMOTER]),
            'role' => UserRole::PROMOTER->value,
        ]);
    }

    /**
     * State: Atribuição para validator.
     */
    public function forValidator(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->state(['role' => UserRole::VALIDATOR]),
            'role' => UserRole::VALIDATOR->value,
            'sector_id' => null,
            'guest_limit' => null,
        ]);
    }

    /**
     * State: Atribuição para bilheteria.
     */
    public function forBilheteria(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->state(['role' => UserRole::BILHETERIA]),
            'role' => UserRole::BILHETERIA->value,
            'sector_id' => null,
            'guest_limit' => null,
        ]);
    }

    /**
     * State: Sem limite de convidados.
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_limit' => null,
        ]);
    }

    /**
     * State: Com limite específico de convidados.
     */
    public function withGuestLimit(int $limit): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_limit' => $limit,
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
     * State: Para um usuário específico.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'role' => $user->role->value,
        ]);
    }

    /**
     * State: Para um setor específico.
     */
    public function forSector(Sector $sector): static
    {
        return $this->state(fn (array $attributes) => [
            'sector_id' => $sector->id,
        ]);
    }

    /**
     * State: Horário integral (dia inteiro).
     */
    public function allDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => now()->setTime(0, 0),
            'end_time' => now()->setTime(23, 59),
        ]);
    }

    /**
     * State: Período noturno.
     */
    public function nightShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => now()->setTime(22, 0),
            'end_time' => now()->addDay()->setTime(6, 0),
        ]);
    }
}
