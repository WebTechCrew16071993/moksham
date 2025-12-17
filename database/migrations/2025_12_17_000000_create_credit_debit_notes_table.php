<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_debit_notes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['credit', 'debit'])->index();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->string('note_no')->index();
            $table->date('date');

            // Consignee link (optional, overrides invoice->consignee on PDF if set)
            // ImportCompany model uses table 'companies'
            $table->foreignId('consignee_id')->nullable()->constrained('companies');

            // Texts and details displayed in PDF
            $table->string('heading')->nullable();
            $table->text('row1_details')->nullable();
            $table->decimal('row1_amount_usd', 12, 2)->nullable();
            $table->decimal('row1_amount_credit', 12, 2)->nullable();

            $table->string('subheader')->nullable();
            $table->text('row2_details')->nullable();
            $table->decimal('row2_amount_usd', 12, 2)->nullable();
            $table->decimal('row2_amount_credit', 12, 2)->nullable();

            $table->decimal('total_amount', 12, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_debit_notes');
    }
};
