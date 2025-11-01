<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packing_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('packing_lists', 'container_no_summary')) {
                $table->string('container_no_summary')->nullable()->after('ship_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packing_lists', function (Blueprint $table) {
            if (Schema::hasColumn('packing_lists', 'container_no_summary')) {
                $table->dropColumn('container_no_summary');
            }
        });
    }
};
