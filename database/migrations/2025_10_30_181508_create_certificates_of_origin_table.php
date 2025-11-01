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
        Schema::create('certificates_of_origin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
        
            $table->string('document_no')->unique();            // 257360678
            $table->string('bl_no')->nullable();
        
            $table->string('port_of_loading')->nullable();      // NEWARK
            $table->string('port_of_discharge')->nullable();    // HAZIRA
            $table->string('place_of_receipt')->nullable();     // CHICAGO
            $table->string('exporting_carrier')->nullable();    // MAERSK SELETAR
            $table->string('type_of_move')->nullable();         // Vessel, Containerized
            $table->boolean('containerized')->default(true);
        
            $table->integer('total_packages')->nullable();      // 174 BALES
            $table->string('description')->nullable();          // WASTEPAPER OCC. 11
            $table->decimal('total_gross_weight_kg', 15, 3)->nullable(); // 105237.967
            $table->integer('total_bales')->nullable();         // 174
        
            $table->date('shipped_date')->nullable();           // 16-August-2025
            $table->date('sworn_date')->nullable();             // 16-August-2025
        
            $table->string('owner_signature_path')->nullable();
            $table->string('chamber_signature_path')->nullable();
        
            $table->string('status')->default('draft');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // 10. certificate_items
        Schema::create('certificate_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('certificates_of_origin')->onDelete('cascade');
            $table->string('marks_numbers');                    // MRKU5824716
            $table->integer('number_of_packages');              // 35
            $table->string('description');                      // WASTEPAPER OCC. 11
            $table->decimal('gross_weight_kg', 15, 3);          // 21309.770
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates_of_origin');
        Schema::dropIfExists('certificate_items');
    }
};
