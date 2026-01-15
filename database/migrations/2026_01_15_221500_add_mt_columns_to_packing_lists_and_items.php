<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('packing_list_items', function (Blueprint $table) {
            if (!Schema::hasColumn('packing_list_items', 'weight_mt')) {
                $table->decimal('weight_mt', 12, 3)->nullable()->after('weight_kg');
            }
        });

        Schema::table('packing_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('packing_lists', 'total_weight_mt')) {
                $table->decimal('total_weight_mt', 12, 3)->nullable()->after('total_weight_kg');
            }
        });

        try {
            DB::statement('ALTER TABLE `packing_list_items` MODIFY `weight_lbs` DECIMAL(15,3) NULL');
        } catch (\Throwable $e) {
            // ignore if already nullable or platform not MySQL
        }

        // Backfill MT using existing KG values
        try {
            DB::statement('UPDATE packing_list_items SET weight_mt = ROUND(COALESCE(weight_kg, 0) / 1000, 3) WHERE weight_mt IS NULL');
        } catch (\Throwable $e) { /* ignore in case columns absent in some envs */ }

        try {
            DB::statement('UPDATE packing_lists SET total_weight_mt = ROUND(COALESCE(total_weight_kg, 0) / 1000, 3) WHERE total_weight_mt IS NULL');
        } catch (\Throwable $e) { /* ignore */ }
    }

    public function down(): void
    {
        Schema::table('packing_list_items', function (Blueprint $table) {
            if (Schema::hasColumn('packing_list_items', 'weight_mt')) {
                $table->dropColumn('weight_mt');
            }
        });

        Schema::table('packing_lists', function (Blueprint $table) {
            if (Schema::hasColumn('packing_lists', 'total_weight_mt')) {
                $table->dropColumn('total_weight_mt');
            }
        });

        try {
            DB::statement('ALTER TABLE `packing_list_items` MODIFY `weight_lbs` DECIMAL(15,3) NOT NULL');
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
