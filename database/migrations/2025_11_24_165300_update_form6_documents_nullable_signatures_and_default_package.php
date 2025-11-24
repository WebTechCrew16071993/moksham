<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form6_documents', function (Blueprint $table) {
            // Make exporter signature fields nullable to allow saving drafts without signatures
            $table->string('exporter_signature_name')->nullable()->change();
            $table->date('exporter_signature_date')->nullable()->change();

            // Ensure package_number never ends up NULL by setting a default at DB level
            $table->integer('package_number')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('form6_documents', function (Blueprint $table) {
            // Revert changes
            $table->string('exporter_signature_name')->nullable(false)->change();
            $table->date('exporter_signature_date')->nullable(false)->change();
            $table->integer('package_number')->default(null)->change();
        });
    }
};
