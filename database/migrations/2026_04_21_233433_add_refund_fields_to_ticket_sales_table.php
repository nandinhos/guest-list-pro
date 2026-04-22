<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            $table->boolean('is_refunded')->default(false)->after('notes');
            $table->timestamp('refunded_at')->nullable()->after('is_refunded');
            $table->foreignId('refunded_by')->nullable()->constrained('users')->after('refunded_at');
            $table->string('refund_reason')->nullable()->after('refunded_by');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            $table->dropForeign(['refunded_by']);
            $table->dropColumn(['is_refunded', 'refunded_at', 'refunded_by', 'refund_reason']);
        });
    }
};
