<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_sale_id')->constrained('ticket_sales')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users');
            $table->text('reason')->comment('Motivo obrigatório da solicitação');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewer_id')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('ticket_sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
