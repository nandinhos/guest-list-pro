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
        Schema::create('ticket_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sold_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('value', 10, 2);
            $table->string('payment_method', 50);
            $table->string('buyer_name');
            $table->string('buyer_document', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_sales');
    }
};
