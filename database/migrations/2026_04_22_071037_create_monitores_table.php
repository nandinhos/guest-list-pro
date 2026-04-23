<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('nome', 150);
            $table->string('cpf', 14); // formatado para display
            $table->foreignId('criado_por')->constrained('users');
            $table->timestamps();
            $table->unique(['event_id', 'cpf']);
            $table->index(['event_id', 'veiculo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitores');
    }
};
