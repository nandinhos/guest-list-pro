<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Administrador Principal
        User::firstOrCreate(
            ['email' => 'admin@guestlist.pro'],
            [
                'name' => 'Admin do Sistema',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
            ]
        );

        // Promoter para Testes
        User::firstOrCreate(
            ['email' => 'promoter@guestlist.pro'],
            [
                'name' => 'Promoter Exemplo',
                'password' => Hash::make('password'),
                'role' => UserRole::PROMOTER,
                'is_active' => true,
            ]
        );

        // Validador para Testes
        User::firstOrCreate(
            ['email' => 'validator@guestlist.pro'],
            [
                'name' => 'Validador Exemplo',
                'password' => Hash::make('password'),
                'role' => UserRole::VALIDATOR,
                'is_active' => true,
            ]
        );

        // Bilheteria (Adicionado para completar o fluxo)
        User::firstOrCreate(
            ['email' => 'bilheteria@guestlist.pro'],
            [
                'name' => 'Bilheteria Central',
                'password' => Hash::make('password'),
                'role' => UserRole::BILHETERIA,
                'is_active' => true,
            ]
        );
    }
}
