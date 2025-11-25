<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('self_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('packing_list_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bl_correction_id')->nullable()->constrained('bl_corrections')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Certificate number pattern YY-<containers>/<invoice_no>
            $table->unsignedSmallInteger('ref_year')->nullable();
            $table->unsignedInteger('ref_container_count')->nullable();
            $table->string('ref_invoice_no')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('issue_date')->nullable();

            $table->string('invoice_no')->nullable();
            $table->text('importer_details')->nullable();
            $table->string('goods_description')->nullable();
            $table->decimal('total_quantity_kgs', 15, 3)->nullable();

            $table->json('declaration_points')->nullable();
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
        Schema::dropIfExists('self_declarations');
    }
};
