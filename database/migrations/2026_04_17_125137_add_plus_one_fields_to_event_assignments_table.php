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
        Schema::table('event_assignments', function (Blueprint $table) {
            $table->boolean('plus_one_enabled')->default(false)->after('guest_limit');
            $table->integer('plus_one_limit')->default(0)->after('plus_one_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_assignments', function (Blueprint $table) {
            $table->dropColumn(['plus_one_enabled', 'plus_one_limit']);
        });
    }
};
