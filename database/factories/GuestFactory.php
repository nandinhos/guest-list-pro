<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guest>
 */
class GuestFactory extends Factory
{
    protected $model = Guest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'sector_id' => Sector::factory(),
            'promoter_id' => User::factory()->state(['role' => UserRole::PROMOTER]),
            'name' => fake()->name(),
            'document' => fake()->cpf(false),
            'document_type' => DocumentType::CPF,
            'email' => fake()->optional()->safeEmail(),
            'is_checked_in' => false,
            'checked_in_at' => null,
            'checked_in_by' => null,
        ];
    }

    /**
     * State: Convidado com check-in realizado.
     */
    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_checked_in' => true,
            'checked_in_at' => now(),
            'checked_in_by' => User::factory()->state(['role' => UserRole::VALIDATOR]),
        ]);
    }

    /**
     * State: Convidado sem check-in.
     */
    public function notCheckedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_checked_in' => false,
            'checked_in_at' => null,
            'checked_in_by' => null,
        ]);
    }

    /**
     * State: Com documento RG.
     */
    public function withRg(): static
    {
        return $this->state(fn (array $attributes) => [
            'document' => fake()->rg(false),
            'document_type' => DocumentType::RG,
        ]);
    }

    /**
     * State: Com passaporte.
     */
    public function withPassport(): static
    {
        return $this->state(fn (array $attributes) => [
            'document' => strtoupper(fake()->bothify('??######')),
            'document_type' => DocumentType::PASSPORT,
        ]);
    }
}
