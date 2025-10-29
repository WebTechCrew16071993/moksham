<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // Company Information
            $table->string('company_name');
            $table->string('company_address');
            $table->string('company_city');
            $table->string('company_state');
            $table->string('company_zip', 20);
            $table->string('company_country');
            $table->string('company_phone', 30);
            $table->string('company_email');

            // Bank Information
            $table->string('bank_name');
            $table->string('bank_address');
            $table->string('bank_city');
            $table->string('bank_state');
            $table->string('bank_zip', 20);
            $table->string('bank_country');
            $table->string('bank_account_number');
            $table->string('bank_swift_code')->nullable();
            $table->string('bank_routing_number')->nullable();

            // Tax & Registration
            $table->string('tax_iec_number')->nullable();
            $table->string('tax_gstin')->nullable();
            $table->string('tax_pan_number')->nullable();

            // Company signed logo
            $table->string('company_signed_logo')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
