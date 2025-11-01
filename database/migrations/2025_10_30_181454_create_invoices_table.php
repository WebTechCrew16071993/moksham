<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('consignee_id')->nullable()->constrained('companies'); // BILL TO
        
            $table->string('invoice_no')->unique();             // 1002
            $table->date('date');                               // 08/08/2025
            $table->string('employee');                         // NIK PATEL
            $table->string('obl_ref')->nullable();              // 1002
        
            $table->string('no_of_cont')->nullable();           // 5 X40 FT FC
            $table->decimal('weight_mt', 15, 3)->nullable();    // 105.238
            $table->string('description')->nullable();          // WASTEPAPER OCC 11...
            $table->string('moisture_contain')->nullable();     // MOISTURE CONTAIN: LESS THAN 12%
            $table->decimal('rate_mt', 10, 2)->nullable();      // 202.00
            $table->decimal('amount', 15, 2)->nullable();       // 21258.08
            $table->string('payment_terms')->nullable();        // DP
            $table->decimal('advance', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->nullable();
        
            $table->date('return_date')->nullable();            // 08/04/2025
            $table->date('cut_off_date')->nullable();           // 08/08/2025
            $table->date('dept_est')->nullable();               // 08/16/2025
            $table->date('arrival')->nullable();                // 09/29/2025
            $table->date('si_cut_off')->nullable();             // 08/12/2025
            $table->string('f_dest')->nullable();               // HAZIRA
        
            $table->longText('remarks')->nullable();
            $table->longText('terms_conditions')->nullable();   // From Page 2
        
            $table->string('status')->default('draft');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
