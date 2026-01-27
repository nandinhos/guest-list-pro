<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índices de performance identificados como faltantes na análise do projeto.
     *
     * Gargalos resolvidos:
     * - CheckinAttempt: queries de widgets sem índices
     * - Guests: busca composta por evento + nome/documento
     * - TicketSales: relatórios por operador
     * - ApprovalRequests: verificação de expiração
     */
    public function up(): void
    {
        // Índices para checkin_attempts (widget SuspiciousCheckins)
        Schema::table('checkin_attempts', function (Blueprint $table) {
            $table->index(['event_id', 'result'], 'checkin_attempts_event_result_idx');
            $table->index(['event_id', 'created_at'], 'checkin_attempts_event_created_idx');
            $table->index(['validator_id', 'created_at'], 'checkin_attempts_validator_created_idx');
        });

        // Índices compostos para guests (busca otimizada)
        Schema::table('guests', function (Blueprint $table) {
            $table->index(['event_id', 'name_normalized'], 'guests_event_name_norm_idx');
            $table->index(['event_id', 'document_normalized'], 'guests_event_doc_norm_idx');
        });

        // Índice para ticket_sales (relatórios por operador)
        Schema::table('ticket_sales', function (Blueprint $table) {
            $table->index('sold_by', 'ticket_sales_sold_by_idx');
        });

        // Índice para approval_requests (verificação de expiração)
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->index('expires_at', 'approval_requests_expires_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkin_attempts', function (Blueprint $table) {
            $table->dropIndex('checkin_attempts_event_result_idx');
            $table->dropIndex('checkin_attempts_event_created_idx');
            $table->dropIndex('checkin_attempts_validator_created_idx');
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex('guests_event_name_norm_idx');
            $table->dropIndex('guests_event_doc_norm_idx');
        });

        Schema::table('ticket_sales', function (Blueprint $table) {
            $table->dropIndex('ticket_sales_sold_by_idx');
        });

        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropIndex('approval_requests_expires_at_idx');
        });
    }
};
