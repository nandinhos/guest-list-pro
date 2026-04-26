<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Excursao>
 */
class ExcursaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'nome' => $this->faker->company(),
            'criado_por' => User::factory(),
        ];
    }
}
