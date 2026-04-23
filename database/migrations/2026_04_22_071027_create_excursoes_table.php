<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excursoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('nome', 150);
            $table->foreignId('criado_por')->constrained('users');
            $table->timestamps();
            $table->index(['event_id', 'criado_por']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excursoes');
    }
};
