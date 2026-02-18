<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventSimulationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Identificar Usuários
        $promoter = User::where('role', UserRole::PROMOTER)->first();
        $validator = User::where('role', UserRole::VALIDATOR)->first();

        if (!$promoter || !$validator) {
            $this->command->error('Promoter ou Validator não encontrados. Execute o UserSeeder primeiro.');
            return;
        }

        // 2. Definir Eventos
        $eventsData = [
            [
                'name' => 'Summer Festival 2026',
                'status' => EventStatus::ACTIVE,
                'date' => Carbon::now()->addMonths(2),
                'start_time' => '18:00:00',
                'end_time' => '04:00:00',
                'location' => 'Arena Central',
                'ticket_price' => 150.00,
            ],
            [
                'name' => 'Night Club Live',
                'status' => EventStatus::ACTIVE,
                'date' => Carbon::now()->addWeeks(1),
                'start_time' => '22:00:00',
                'end_time' => '05:00:00',
                'location' => 'Club House',
                'ticket_price' => 80.00,
            ],
            [
                'name' => 'Retro Party 2025',
                'status' => EventStatus::FINISHED,
                'date' => Carbon::now()->subMonths(1),
                'start_time' => '21:00:00',
                'end_time' => '03:00:00',
                'location' => 'Vintage Hall',
                'ticket_price' => 50.00,
            ],
        ];

        foreach ($eventsData as $data) {
            $event = Event::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            // 3. Criar Setores para cada evento
            $sectors = [
                ['name' => 'VIP', 'capacity' => 100],
                ['name' => 'Backstage', 'capacity' => 50],
                ['name' => 'Pista', 'capacity' => 500],
            ];

            $createdSectors = [];
            foreach ($sectors as $sectorData) {
                $createdSectors[] = Sector::updateOrCreate(
                    ['event_id' => $event->id, 'name' => $sectorData['name']],
                    ['capacity' => $sectorData['capacity']]
                );
            }

            // 4. Criar Convidados (Simulação)
            $this->seedGuests($event, $createdSectors, $promoter, $validator);
        }

        $this->command->info('Simulação de eventos concluída com sucesso!');
    }

    private function seedGuests(Event $event, array $sectors, User $promoter, User $validator): void
    {
        $faker = \Faker\Factory::create('pt_BR');

        foreach ($sectors as $sector) {
            // Criar 10 convidados por setor
            for ($i = 0; $i < 10; $i++) {
                $isCheckedIn = $event->status === EventStatus::FINISHED || ($event->status === EventStatus::ACTIVE && $faker->boolean(40));
                
                Guest::firstOrCreate(
                    [
                        'event_id' => $event->id,
                        'document' => $faker->cpf(false),
                    ],
                    [
                        'sector_id' => $sector->id,
                        'promoter_id' => $promoter->id,
                        'name' => $faker->name,
                        'document_type' => DocumentType::CPF,
                        'email' => $faker->unique()->safeEmail,
                        'is_checked_in' => $isCheckedIn,
                        'checked_in_at' => $isCheckedIn ? Carbon::now()->subMinutes(rand(10, 120)) : null,
                        'checked_in_by' => $isCheckedIn ? $validator->id : null,
                    ]
                );
            }
        }
    }
}
