<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form6_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('form6_documents', 'notes')) {
                $table->text('notes')->nullable()->after('terms_and_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('form6_documents', function (Blueprint $table) {
            if (Schema::hasColumn('form6_documents', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
