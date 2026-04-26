<?php

namespace Database\Factories;

use App\Enums\TipoVeiculo;
use App\Models\Excursao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Veiculo>
 */
class VeiculoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'excursao_id' => Excursao::factory(),
            'tipo' => $this->faker->randomElement(TipoVeiculo::cases())->value,
            'placa' => strtoupper($this->faker->bothify('???-####')),
        ];
    }
}
