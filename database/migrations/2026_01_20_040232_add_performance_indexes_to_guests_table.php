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
        Schema::table('guests', function (Blueprint $table) {
            $table->index('is_checked_in', 'guests_is_checked_in_idx');
            $table->index('checked_in_at', 'guests_checked_in_at_idx');
            $table->index(['event_id', 'is_checked_in'], 'guests_event_checked_in_idx');
            $table->index(['event_id', 'sector_id', 'promoter_id'], 'guests_event_sector_promoter_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex('guests_is_checked_in_idx');
            $table->dropIndex('guests_checked_in_at_idx');
            $table->dropIndex('guests_event_checked_in_idx');
            $table->dropIndex('guests_event_sector_promoter_idx');
        });
    }
};
