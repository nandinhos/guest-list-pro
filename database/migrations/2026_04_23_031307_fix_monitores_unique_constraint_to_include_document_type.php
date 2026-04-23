<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitores', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'document_number']);
        });

        Schema::table('monitores', function (Blueprint $table) {
            $table->unique(['event_id', 'document_type', 'document_number'], 'monitores_event_doc_type_doc_unique');
        });
    }

    public function down(): void
    {
        Schema::table('monitores', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'document_type', 'document_number']);
        });

        Schema::table('monitores', function (Blueprint $table) {
            $table->unique(['event_id', 'document_number']);
        });
    }
};
