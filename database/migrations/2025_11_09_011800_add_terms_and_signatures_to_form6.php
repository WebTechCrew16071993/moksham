<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form6_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('form6_documents', 'terms_and_description')) {
                $table->text('terms_and_description')->nullable()->after('specific_conditions');
            }
            if (!Schema::hasColumn('form6_documents', 'signature_place')) {
                $table->string('signature_place')->nullable()->after('exporter_signature_date');
            }
            if (!Schema::hasColumn('form6_documents', 'signature_designation')) {
                $table->string('signature_designation')->nullable()->after('signature_place');
            }
            if (!Schema::hasColumn('form6_documents', 'exporter_signature_image_path')) {
                $table->string('exporter_signature_image_path')->nullable()->after('exporter_signature_date');
            }
            if (!Schema::hasColumn('form6_documents', 'importer_signature_image_path')) {
                $table->string('importer_signature_image_path')->nullable()->after('importer_signature_date');
            }
            if (!Schema::hasColumn('form6_documents', 'recovery_operations')) {
                $table->json('recovery_operations')->nullable()->after('specific_conditions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form6_documents', function (Blueprint $table) {
            if (Schema::hasColumn('form6_documents', 'terms_and_description')) {
                $table->dropColumn('terms_and_description');
            }
            if (Schema::hasColumn('form6_documents', 'signature_place')) {
                $table->dropColumn('signature_place');
            }
            if (Schema::hasColumn('form6_documents', 'signature_designation')) {
                $table->dropColumn('signature_designation');
            }
            if (Schema::hasColumn('form6_documents', 'exporter_signature_image_path')) {
                $table->dropColumn('exporter_signature_image_path');
            }
            if (Schema::hasColumn('form6_documents', 'importer_signature_image_path')) {
                $table->dropColumn('importer_signature_image_path');
            }
            if (Schema::hasColumn('form6_documents', 'recovery_operations')) {
                $table->dropColumn('recovery_operations');
            }
        });
    }
};
