<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'bl_correction_id')) {
                $table->foreignId('bl_correction_id')->after('packing_list_id')->nullable()->constrained('bl_corrections')->nullOnDelete();
            }
        });

        Schema::table('bl_corrections', function (Blueprint $table) {
            if (!Schema::hasColumn('bl_corrections', 'invoice_id')) {
                $table->foreignId('invoice_id')->after('packing_list_id')->nullable()->constrained('invoices')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'bl_correction_id')) {
                $table->dropConstrainedForeignId('bl_correction_id');
            }
        });

        Schema::table('bl_corrections', function (Blueprint $table) {
            if (Schema::hasColumn('bl_corrections', 'invoice_id')) {
                $table->dropConstrainedForeignId('invoice_id');
            }
        });
    }
};
