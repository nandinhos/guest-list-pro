<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Seeder;

class E2ETestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Running E2E Test Seeder...');

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ]
        );
        $this->command->info('Created admin user: admin@admin.com / password');

        $promoter = User::firstOrCreate(
            ['email' => 'promoter@promoter.com'],
            [
                'name' => 'Promoter User',
                'password' => bcrypt('password'),
                'role' => UserRole::PROMOTER,
                'is_active' => true,
            ]
        );
        $this->command->info('Created promoter user: promoter@promoter.com / password');

        $validator = User::firstOrCreate(
            ['email' => 'validador@guestlist.pro'],
            [
                'name' => 'Validator User',
                'password' => bcrypt('password'),
                'role' => UserRole::VALIDATOR,
                'is_active' => true,
            ]
        );
        $this->command->info('Created validator user: validador@guestlist.pro / password');

        User::firstOrCreate(
            ['email' => 'inactive@inactive.com'],
            [
                'name' => 'Inactive User',
                'password' => bcrypt('password'),
                'role' => UserRole::ADMIN,
                'is_active' => false,
            ]
        );
        $this->command->info('Created inactive user: inactive@inactive.com / password (inactive)');

        $event = Event::updateOrCreate(
            ['name' => 'E2E Test Event'],
            [
                'date' => '2026-12-31',
                'start_time' => '20:00:00',
                'end_time' => '23:59:00',
                'location' => 'E2E Test Location',
                'status' => EventStatus::ACTIVE,
                'ticket_price' => 100.00,
            ]
        );
        $this->command->info('Created event: E2E Test Event (id='.$event->id.')');

        $vipSector = Sector::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'VIP'],
            [
                'capacity' => 100,
            ]
        );
        $this->command->info('Created sector: VIP for event '.$event->id);

        $pistaSector = Sector::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Pista'],
            [
                'capacity' => 500,
            ]
        );
        $this->command->info('Created sector: Pista for event '.$event->id);

        $guests = [
            ['name' => 'Ana Silva', 'document' => '12345678901'],
            ['name' => 'Bruno Santos', 'document' => '23456789012'],
            ['name' => 'Carlos Oliveira', 'document' => '34567890123'],
            ['name' => 'Diana Costa', 'document' => '45678901234'],
            ['name' => 'Eduardo Lima', 'document' => '56789012345'],
            ['name' => 'Fernanda Souza', 'document' => '67890123456'],
            ['name' => 'Gabriel Mendes', 'document' => '78901234567'],
            ['name' => 'Helena Pereira', 'document' => '89012345678'],
            ['name' => 'Igor Rodrigues', 'document' => '90123456789'],
            ['name' => 'Julia Almeida', 'document' => '01234567890'],
        ];

        $sectors = [$vipSector, $pistaSector];

        foreach ($guests as $index => $guestData) {
            $sector = $sectors[$index % count($sectors)];
            $existingGuest = Guest::where('event_id', $event->id)
                ->where('document', $guestData['document'])
                ->first();

            if (! $existingGuest) {
                Guest::create([
                    'event_id' => $event->id,
                    'document' => $guestData['document'],
                    'name' => $guestData['name'],
                    'document_type' => DocumentType::CPF,
                    'sector_id' => $sector->id,
                    'promoter_id' => $promoter->id,
                    'is_checked_in' => false,
                ]);
            }
        }
        $this->command->info('Created '.count($guests).' guests for E2E Test Event');

        $this->command->info('E2E Test Seeder completed!');
    }
}
