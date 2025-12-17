<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop the old unique index on name (if it exists)
            try {
                $table->dropUnique('categories_name_unique');
            } catch (\Throwable $e) {
                // ignore if not present
            }
        });

    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');
        });
    }
};
