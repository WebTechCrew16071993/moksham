<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form9_documents', function (Blueprint $table) {
            // Make these columns nullable as requested
            $table->string('r_code', 10)->nullable()->change();
            $table->text('waste_designation_composition')->nullable()->change();
            $table->text('waste_description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('form9_documents', function (Blueprint $table) {
            // Revert to NOT NULL
            $table->string('r_code', 10)->nullable(false)->change();
            $table->text('waste_designation_composition')->nullable(false)->change();
            $table->text('waste_description')->nullable(false)->change();
        });
    }
};
