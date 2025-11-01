<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('notify')->nullable()->after('f_dest');
            $table->string('quotation')->nullable()->after('notify');
            $table->string('origin')->nullable()->after('si_cut_off');

        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['notify', 'quotation','origin']);
        });
    }
};
