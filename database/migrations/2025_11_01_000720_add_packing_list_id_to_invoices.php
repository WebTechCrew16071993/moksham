<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'packing_list_id')) {
                $table->foreignId('packing_list_id')->nullable()->after('id')->constrained('packing_lists')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'packing_list_id')) {
                $table->dropConstrainedForeignId('packing_list_id');
            }
        });
    }
};
