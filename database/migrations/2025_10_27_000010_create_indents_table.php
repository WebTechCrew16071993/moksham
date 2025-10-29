<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('indents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            // Index/Indent meta
            $table->unsignedBigInteger('indent_no')->nullable()->unique();
            $table->date('indent_date')->nullable();

            // Foreign references
            $table->foreignId('consignee_id')->nullable()->constrained('companies');
            $table->foreignId('hsn_code_id')->nullable()->constrained('hsn_codes');

            // Snapshots (denormalized for document stability)
            // Shipper snapshot from company_settings (assume a single record in settings)
            $table->string('shipper_name')->nullable();
            $table->text('shipper_address')->nullable();
            $table->string('shipper_city')->nullable();
            $table->string('shipper_state')->nullable();
            $table->string('shipper_zip')->nullable();
            $table->string('shipper_country')->nullable();
            $table->string('shipper_email')->nullable();
            $table->string('shipper_phone')->nullable();
            // Shipper bank snapshot
            $table->string('shipper_bank_name')->nullable();
            $table->text('shipper_bank_address')->nullable();
            $table->string('shipper_bank_city')->nullable();
            $table->string('shipper_bank_state')->nullable();
            $table->string('shipper_bank_zip')->nullable();
            $table->string('shipper_bank_country')->nullable();
            $table->string('shipper_bank_account_number')->nullable();
            $table->string('shipper_bank_swift_code')->nullable();
            $table->string('shipper_bank_routing_number')->nullable();
            // Shipper tax snapshot
            $table->string('shipper_tax_iec_number')->nullable();
            $table->string('shipper_tax_gstin')->nullable();
            $table->string('shipper_tax_pan_number')->nullable();

            // Consignee snapshot from companies
            $table->string('consignee_name')->nullable();
            $table->text('consignee_address')->nullable();
            $table->string('consignee_city')->nullable();
            $table->string('consignee_state')->nullable();
            $table->string('consignee_zip')->nullable();
            $table->string('consignee_country')->nullable();
            $table->string('consignee_email')->nullable();
            $table->string('consignee_phone')->nullable();
            // Consignee tax snapshot
            $table->string('consignee_iec')->nullable();
            $table->string('consignee_gstin')->nullable();
            $table->string('consignee_pan')->nullable();
            // Consignee bank snapshot
            $table->string('consignee_bank_name')->nullable();
            $table->text('consignee_bank_address')->nullable();
            $table->string('consignee_bank_city')->nullable();
            $table->string('consignee_bank_state')->nullable();
            $table->string('consignee_bank_zip')->nullable();
            $table->string('consignee_bank_country')->nullable();
            $table->string('consignee_bank_account_number')->nullable();
            $table->string('consignee_bank_swift_code')->nullable();
            $table->string('consignee_bank_ifsc_code')->nullable();

            // HSN snapshot
            $table->string('hsn_code')->nullable();
            $table->string('hsn_category')->nullable();
            $table->string('hsn_description')->nullable();

            // Document content fields (from provided PDF)
            $table->string('kind_attention')->nullable();
            $table->text('quality')->nullable();
            $table->string('origin')->nullable();
            $table->string('quantity')->nullable();
            $table->string('moisture_and_throw')->nullable();
            $table->string('price')->nullable();
            $table->string('payment')->nullable();
            $table->string('shipment_schedule')->nullable();
            $table->string('payload')->nullable();
            $table->string('shipping_line')->nullable();
            $table->string('discharge_port')->nullable();
            $table->string('final_destination')->nullable();
            $table->string('release_type_of_obl')->nullable();

            // Rich text sections
            $table->longText('other_terms')->nullable();
            $table->longText('claims')->nullable();
            $table->longText('remarks')->nullable();

            // Signatures
            $table->string('shipper_signature_path')->nullable(); // from settings
            $table->string('consignee_signature_path')->nullable(); // uploaded on edit

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indents');
    }
};
