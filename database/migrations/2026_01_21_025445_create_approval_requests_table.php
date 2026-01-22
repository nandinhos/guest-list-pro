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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();

            // Contexto do evento
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sector_id')->nullable()->constrained()->nullOnDelete();

            // Tipo e Status (usando enums)
            $table->string('type', 50);
            $table->string('status', 50)->default('pending');

            // Solicitante
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();

            // Dados do convidado
            $table->string('guest_name');
            $table->string('guest_document', 50)->nullable();
            $table->string('guest_document_type', 20)->nullable();
            $table->string('guest_email')->nullable();

            // Referência a guest existente (se aplicável)
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();

            // Notas do solicitante
            $table->text('requester_notes')->nullable();

            // Resposta do administrador
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();

            // Metadados de auditoria
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Expiração automática
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Índices para queries frequentes
            $table->index(['event_id', 'status']);
            $table->index(['requester_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
