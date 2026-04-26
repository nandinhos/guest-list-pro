<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Event;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Monitor>
 */
class MonitorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'veiculo_id' => Veiculo::factory(),
            'nome' => $this->faker->name(),
            'document_type' => DocumentType::CPF->value,
            'document_number' => $this->faker->numerify('###########'),
            'criado_por' => User::factory(),
        ];
    }
}
