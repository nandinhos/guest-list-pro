<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sector>
 */
class SectorFactory extends Factory
{
    protected $model = Sector::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement(['VIP', 'Camarote', 'Pista', 'Premium', 'Backstage', 'Lounge', 'Arena']),
            'capacity' => fake()->numberBetween(50, 500),
        ];
    }

    /**
     * State: Setor VIP.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'VIP',
            'capacity' => fake()->numberBetween(20, 100),
        ]);
    }

    /**
     * State: Setor Pista.
     */
    public function pista(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pista',
            'capacity' => fake()->numberBetween(200, 1000),
        ]);
    }

    /**
     * State: Com capacidade específica.
     */
    public function withCapacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $capacity,
        ]);
    }
}
