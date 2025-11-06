<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates_of_origin', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates_of_origin', 'place_of_delivery_on_carrier')) {
                $table->string('place_of_delivery_on_carrier')->nullable()->after('port_of_discharge');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificates_of_origin', function (Blueprint $table) {
            if (Schema::hasColumn('certificates_of_origin', 'place_of_delivery_on_carrier')) {
                $table->dropColumn('place_of_delivery_on_carrier');
            }
        });
    }
};
