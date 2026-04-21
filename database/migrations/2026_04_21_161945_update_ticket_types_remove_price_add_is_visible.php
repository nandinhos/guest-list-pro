<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->after('is_active');
        });

        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->after('description');
        });

        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
