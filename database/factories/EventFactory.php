<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' - '.fake()->randomElement(['Festival', 'Show', 'Festa', 'Evento']),
            'location' => fake()->address(),
            'date' => fake()->dateTimeBetween('now', '+3 months'),
            'start_time' => fake()->time('H:i'),
            'end_time' => fake()->time('H:i'),
            'status' => EventStatus::ACTIVE,
            'ticket_price' => fake()->randomFloat(2, 0, 500),
            'bilheteria_enabled' => fake()->boolean(70),
        ];
    }

    /**
     * State: Evento ativo.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::ACTIVE,
        ]);
    }

    /**
     * State: Evento em rascunho.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::DRAFT,
        ]);
    }

    /**
     * State: Evento finalizado.
     */
    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::FINISHED,
            'date' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    /**
     * State: Evento cancelado.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::CANCELLED,
        ]);
    }

    /**
     * State: Com bilheteria habilitada.
     */
    public function withBilheteria(float $ticketPrice = 50.00): static
    {
        return $this->state(fn (array $attributes) => [
            'bilheteria_enabled' => true,
            'ticket_price' => $ticketPrice,
        ]);
    }
}
