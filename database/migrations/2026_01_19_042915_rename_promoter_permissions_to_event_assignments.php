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
        // Renomear a tabela
        Schema::rename('promoter_permissions', 'event_assignments');

        // Adicionar coluna role e tornar campos nullable
        Schema::table('event_assignments', function (Blueprint $table) {
            $table->string('role')->default('promoter')->after('user_id');

            // Tornar nullable para validators/bilheteria (que nao precisam de setor/limite)
            $table->unsignedBigInteger('sector_id')->nullable()->change();
            $table->integer('guest_limit')->nullable()->change();
        });

        // Adicionar indice para performance
        Schema::table('event_assignments', function (Blueprint $table) {
            $table->index(['user_id', 'event_id', 'role'], 'event_assignments_user_event_role_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover indice
        Schema::table('event_assignments', function (Blueprint $table) {
            $table->dropIndex('event_assignments_user_event_role_idx');
        });

        // Reverter as alteracoes nas colunas
        Schema::table('event_assignments', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->unsignedBigInteger('sector_id')->nullable(false)->change();
            $table->integer('guest_limit')->nullable(false)->change();
        });

        // Renomear de volta
        Schema::rename('event_assignments', 'promoter_permissions');
    }
};
