<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Seeder;

class E2ETestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Running E2E Test Seeder...');

        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ]
        );
        $this->command->info('Created admin user: admin@admin.com / password');

        User::updateOrCreate(
            ['email' => 'promoter@promoter.com'],
            [
                'name' => 'Promoter User',
                'password' => bcrypt('password'),
                'role' => UserRole::PROMOTER,
                'is_active' => true,
            ]
        );
        $this->command->info('Created promoter user: promoter@promoter.com / password');

        User::updateOrCreate(
            ['email' => 'validator@validator.com'],
            [
                'name' => 'Validator User',
                'password' => bcrypt('password'),
                'role' => UserRole::VALIDATOR,
                'is_active' => true,
            ]
        );
        $this->command->info('Created validator user: validator@validator.com / password');

        User::updateOrCreate(
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
                'status' => 'draft',
                'ticket_price' => 100.00,
            ]
        );
        $this->command->info('Created event: E2E Test Event (id='.$event->id.')');

        Sector::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'VIP'],
            [
                'capacity' => 100,
            ]
        );
        $this->command->info('Created sector: VIP for event '.$event->id);

        Sector::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Pista'],
            [
                'capacity' => 500,
            ]
        );
        $this->command->info('Created sector: Pista for event '.$event->id);

        $this->command->info('E2E Test Seeder completed!');
    }
}
