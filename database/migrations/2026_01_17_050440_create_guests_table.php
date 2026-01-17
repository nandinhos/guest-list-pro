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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sector_id')->constrained()->cascadeOnDelete();
            
            // Quem cadastrou o convidado
            $table->foreignId('promoter_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('name');
            $table->string('document'); // CPF/RG/Passaporte
            $table->string('email')->nullable();
            
            // Controle de Check-in
            $table->boolean('is_checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Regra principal: documento único por evento
            $table->unique(['event_id', 'document']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
