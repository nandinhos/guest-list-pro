<?php

namespace Database\Seeders;

use App\Enums\TipoVeiculo;
use App\Models\Excursao;
use App\Models\Veiculo;
use Illuminate\Database\Seeder;

class VeiculoSeeder extends Seeder
{
    public function run(): void
    {
        $excursoes = Excursao::all();

        if ($excursoes->isEmpty()) {
            $this->command->info('Nenhuma excursão encontrada. Pulando seed de veículos.');

            return;
        }

        $tipos = TipoVeiculo::cases();

        foreach ($excursoes as $excursao) {
            foreach ($tipos as $tipo) {
                Veiculo::create([
                    'excursao_id' => $excursao->id,
                    'tipo' => $tipo->value,
                    'placa' => $this->generatePlaca(),
                ]);
            }
        }

        $this->command->info('Veículos seedados: '.Veiculo::count());
    }

    private function generatePlaca(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';

        $plate = '';
        $plate .= $letters[rand(0, strlen($letters) - 1)];
        $plate .= $letters[rand(0, strlen($letters) - 1)];
        $plate .= $letters[rand(0, strlen($letters) - 1)];
        $plate .= '-';
        $plate .= $numbers[rand(0, strlen($numbers) - 1)];
        $plate .= $numbers[rand(0, strlen($numbers) - 1)];
        $plate .= $numbers[rand(0, strlen($numbers) - 1)];
        $plate .= $numbers[rand(0, strlen($numbers) - 1)];

        return $plate;
    }
}
