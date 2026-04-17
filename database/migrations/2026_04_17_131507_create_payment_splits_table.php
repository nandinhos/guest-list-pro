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
        Schema::create('payment_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_sale_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method', 50);
            $table->decimal('value', 10, 2);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['ticket_sale_id', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_splits');
    }
};
