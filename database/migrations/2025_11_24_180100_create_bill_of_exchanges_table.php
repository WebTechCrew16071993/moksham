<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bill_of_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('packing_list_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bl_correction_id')->nullable()->constrained('bl_corrections')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedSmallInteger('ref_year')->nullable();
            $table->unsignedInteger('ref_container_count')->nullable();
            $table->string('ref_invoice_no')->nullable();
            $table->string('ref_no')->nullable();

            $table->date('issue_date')->nullable();
            $table->string('place_of_issue')->nullable();
            $table->decimal('amount_usd', 15, 2)->nullable();
            $table->string('amount_in_words')->nullable();

            $table->string('drawee_name')->nullable();
            $table->text('drawee_address')->nullable();

            $table->string('issuer_name')->nullable();
            $table->string('issuer_title')->nullable();
            $table->string('authorised_signature_image_path')->nullable();

            $table->string('status')->default('draft');
            $table->string('pdf_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_exchanges');
    }
};
