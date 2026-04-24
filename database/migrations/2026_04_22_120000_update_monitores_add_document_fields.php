<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitores', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'cpf']);
        });

        Schema::table('monitores', function (Blueprint $table) {
            $table->dropColumn('cpf');
        });

        Schema::table('monitores', function (Blueprint $table) {
            $table->string('document_type', 20)->after('nome');
            $table->string('document_number', 20)->after('document_type');
        });

        Schema::table('monitores', function (Blueprint $table) {
            $table->unique(['event_id', 'document_number']);
        });
    }

    public function down(): void
    {
        Schema::table('monitores', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'document_number']);
        });

        Schema::table('monitores', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'document_number']);
            $table->string('cpf', 14)->after('nome');
        });

        Schema::table('monitores', function (Blueprint $table) {
            $table->unique(['event_id', 'cpf']);
        });
    }
};
