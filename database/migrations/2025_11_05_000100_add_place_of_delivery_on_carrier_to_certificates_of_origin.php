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
            if (!Schema::hasColumn('certificates_of_origin', 'export_references')) {
                $table->string('export_references')->nullable()->after('bl_no');
            }
            if (!Schema::hasColumn('certificates_of_origin', 'forwarding_agent')) {
                $table->string('forwarding_agent')->nullable()->after('export_references');
            }
            if (!Schema::hasColumn('certificates_of_origin', 'consigned_to')) {
                $table->text('consigned_to')->nullable()->after('shipment_id');
            }
            if (!Schema::hasColumn('certificates_of_origin', 'notify_party')) {
                $table->text('notify_party')->nullable()->after('consigned_to');
            }
            if (!Schema::hasColumn('certificates_of_origin', 'origin_or_ftz')) {
                $table->string('origin_or_ftz')->nullable()->after('forwarding_agent');
            }
            if (!Schema::hasColumn('certificates_of_origin', 'domestic_routing_instructions')) {
                $table->string('domestic_routing_instructions')->nullable()->after('origin_or_ftz');
            }
            if (!Schema::hasColumn('certificates_of_origin', 'pre_carriage_by')) {
                $table->string('pre_carriage_by')->nullable()->after('domestic_routing_instructions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificates_of_origin', function (Blueprint $table) {
            if (Schema::hasColumn('certificates_of_origin', 'place_of_delivery_on_carrier')) {
                $table->dropColumn('place_of_delivery_on_carrier');
            }
            if (Schema::hasColumn('certificates_of_origin', 'forwarding_agent')) {
                $table->dropColumn('forwarding_agent');
            }
            if (Schema::hasColumn('certificates_of_origin', 'export_references')) {
                $table->dropColumn('export_references');
            }
            foreach (['consigned_to','notify_party','origin_or_ftz','domestic_routing_instructions','pre_carriage_by'] as $col) {
                if (Schema::hasColumn('certificates_of_origin', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
