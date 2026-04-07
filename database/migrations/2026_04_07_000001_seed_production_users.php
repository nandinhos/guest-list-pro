<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin do Sistema',
                'email' => 'admin@guestlist.pro',
                'password' => '$2y$12$00rYovMuqqerJbj02CkrjO11cw135SoNI2JoZqdF1l7OlHJvgikW2',
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Promoter Exemplo',
                'email' => 'promoter@guestlist.pro',
                'password' => '$2y$12$00rYovMuqqerJbj02CkrjO11cw135SoNI2JoZqdF1l7OlHJvgikW2',
                'role' => 'promoter',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Validador Exemplo',
                'email' => 'validator@guestlist.pro',
                'password' => '$2y$12$00rYovMuqqerJbj02CkrjO11cw135SoNI2JoZqdF1l7OlHJvgikW2',
                'role' => 'validator',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bilheteria Central',
                'email' => 'bilheteria@guestlist.pro',
                'password' => '$2y$12$00rYovMuqqerJbj02CkrjO11cw135SoNI2JoZqdF1l7OlHJvgikW2',
                'role' => 'bilheteria',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('users')->whereIn('email', [
            'admin@guestlist.pro',
            'promoter@guestlist.pro',
            'validator@guestlist.pro',
            'bilheteria@guestlist.pro',
        ])->delete();
    }
};
