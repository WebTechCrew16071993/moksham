<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hsn_codes', function (Blueprint $table) {
            if (Schema::hasColumn('hsn_codes', 'category')) {
                $table->dropColumn('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hsn_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('hsn_codes', 'category')) {
                $table->string('category')->nullable();
            }
        });
    }
};
