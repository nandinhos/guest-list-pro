<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Sector;
use App\Enums\EventStatus;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criação de um evento de teste
        $event = Event::create([
            'name' => 'Lançamento Guest List Pro 2026',
            'date' => now()->addDays(30)->toDateString(),
            'start_time' => '20:00:00',
            'end_time' => '04:00:00',
            'status' => EventStatus::ACTIVE,
        ]);

        // Criação de setores para o evento
        $sectors = ['Pista', 'VIP', 'Camarote Master'];
        $capacities = [500, 200, 50];

        foreach ($sectors as $index => $sectorName) {
            Sector::create([
                'event_id' => $event->id,
                'name' => $sectorName,
                'capacity' => $capacities[$index],
            ]);
        }
    }
}
