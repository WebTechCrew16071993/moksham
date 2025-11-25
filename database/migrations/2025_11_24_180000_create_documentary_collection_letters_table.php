<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documentary_collection_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('packing_list_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bl_correction_id')->nullable()->constrained('bl_corrections')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('consignee_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->string('letter_no')->nullable();
            $table->date('letter_date')->nullable();

            // SA's Contract No. pattern YY-<containers>/<invoice_no>
            $table->unsignedSmallInteger('ref_year')->nullable(); // last two digits
            $table->unsignedInteger('ref_container_count')->nullable();
            $table->string('ref_invoice_no')->nullable();
            $table->string('sa_contract_no')->nullable();

            $table->decimal('amount_usd', 15, 2)->nullable();

            $table->text('notes')->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_title')->nullable();

            $table->string('status')->default('draft');
            $table->string('pdf_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentary_collection_letters');
    }
};
