<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promoter_permissions', function (Blueprint $table) {
            $table->id();
            // Usuário que recebe a permissão (deve ser do perfil PROMOTER)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sector_id')->constrained()->cascadeOnDelete();
            
            // Limites e restrições
            $table->integer('guest_limit');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promoter_permissions');
    }
};
