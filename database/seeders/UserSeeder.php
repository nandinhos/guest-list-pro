<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
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
        User::create([
            'name' => 'Admin do Sistema',
            'email' => 'admin@guestlist.pro',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        // Promoter para Testes
        User::create([
            'name' => 'Promoter Exemplo',
            'email' => 'promoter@guestlist.pro',
            'password' => Hash::make('password'),
            'role' => UserRole::PROMOTER,
            'is_active' => true,
        ]);

        // Validador para Testes
        User::create([
            'name' => 'Validador Exemplo',
            'email' => 'validator@guestlist.pro',
            'password' => Hash::make('password'),
            'role' => UserRole::VALIDATOR,
            'is_active' => true,
        ]);
    }
}
