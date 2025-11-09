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
        Schema::create('form6_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('packing_list_id')->nullable()->constrained('packing_lists')->nullOnDelete();
            $table->foreignId('bl_correction_id')->nullable()->constrained('bl_corrections')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            
            // Form Reference
            $table->string('form6_no')->unique();                   // Auto-generated or manual
            $table->date('form6_date');                             // 08/08/2025
            $table->string('applicant_ref_no')->nullable();         // 1002
            
            // 1. Exporter Details (Shipper - MOKSHAM)
            $table->text('exporter_name_address');                  // MOKSHAM EXPORT IMPORT LLC...
            $table->string('exporter_contact_person');              // VIKAS PATEL
            $table->string('exporter_phone')->nullable();           // 2019824039
            $table->string('exporter_fax')->nullable();
            $table->string('exporter_email')->nullable();           // Mokshamusa108@gmail.com
            
            // 2. Generator Details (Usually same as exporter)
            $table->text('generator_name_address');                 // MOKSHAM EXPORT IMPORT LLC...
            $table->string('generator_contact_person');             // VIKAS PATEL
            $table->string('generator_phone')->nullable();          // 2019824039
            $table->string('generator_fax')->nullable();
            $table->string('generator_email')->nullable();
            $table->text('site_of_generation')->nullable();
            
            // 3. Importer/Actual User Details (Buyer)
            $table->foreignId('importer_id')->nullable()->constrained('companies');
            $table->text('importer_name_address');                  // SWARAJ PULP & PAPERS...
            $table->string('importer_contact_person');              // RAHUL PATEL
            $table->string('importer_phone')->nullable();           // +91 9714003111
            $table->string('importer_fax')->nullable();
            $table->string('importer_email')->nullable();           // swarajpaper1@gmail.com
            
            // 4. Trader Details (if applicable, usually same as actual user)
            $table->text('trader_name_address')->nullable();
            $table->string('trader_contact_person')->nullable();
            $table->string('trader_phone')->nullable();
            $table->string('trader_fax')->nullable();
            $table->string('trader_email')->nullable();
            $table->text('actual_user_details')->nullable();        // If different from trader
            
            // 5 & 6. Reference and Bill of Lading
            $table->string('bill_of_lading')->nullable();           // BL reference
            
            // 7. Country Details
            $table->string('country_of_export')->default('United States');
            $table->string('country_of_import')->default('India');
            
            // 8. General Description of Waste
            $table->decimal('quantity_kgs', 15, 3);                 // 105237.967 KGS
            $table->string('physical_characteristics');             // Solid
            $table->text('chemical_composition')->nullable();       // EN 643
            $table->string('basel_no')->nullable();                 // B3020
            $table->string('un_shipping_name')->nullable();         // N.A.
            $table->string('un_class')->nullable();                 // N.A.
            $table->string('un_no')->nullable();                    // N.A.
            $table->string('h_number')->nullable();                 // N.A.
            $table->string('y_number')->nullable();                 // N.A.
            $table->string('itc_hs')->nullable();                   // 4707.90
            $table->string('customs_code_hs');                      // 47079000
            $table->string('other_codes')->nullable();              // N.A.
            
            // 9. Packaging Details
            $table->string('package_type');                         // BALES
            $table->integer('package_number');                      // 174
            
            // 10. Special Handling
            $table->text('special_handling_requirements')->nullable(); // N.A.
            
            // 11. Movement Type
            $table->enum('movement_type', ['single', 'multiple'])->default('single');
            $table->text('expected_shipment_dates')->nullable();    // For multiple shipments
            $table->text('estimated_quantities')->nullable();       // For multiple shipments
            
            // 12. Transporter Details
            $table->text('transporter_name_address')->nullable();   // N.A.
            $table->string('transporter_registration_no')->nullable(); // N.A.
            $table->enum('means_of_transport', ['road', 'rail', 'inland_waterway', 'sea', 'air'])->default('sea');
            $table->date('date_of_transfer')->nullable();           // 08/08/2025
            $table->string('carrier_signature')->nullable();
            
            // 13. Exporter Declaration
            $table->text('exporter_declaration')->nullable();       // N.A. (pre-filled text)
            $table->string('exporter_signature_name');              // VIKAS PATEL
            $table->date('exporter_signature_date');                // 08/08/2025
            
            // 14. Importer Receipt (To be filled by buyer)
            $table->decimal('quantity_received_kgs', 15, 3)->nullable();
            $table->string('importer_signature_name')->nullable();
            $table->date('importer_signature_date')->nullable();
            
            // 15 & 16. Importer Certification
            $table->string('importer_applicant_ref_no')->nullable();
            $table->string('r_code')->nullable();                   // N.A.
            $table->text('technology_employed')->nullable();        // N.A.
            $table->date('importer_certification_date')->nullable();
            $table->string('importer_certification_signature')->nullable();
            
            // 17. Specific Conditions
            $table->text('specific_conditions')->nullable();
            
            // Status Management
            $table->enum('status', [
                'draft',                    // Being prepared
                'submitted',                // Submitted to authorities
                'approved',                 // Approved by authorities
                'in_transit',              // Goods in transit
                'received',                // Received by importer (section 14 filled)
                'completed'                // Fully completed (section 16 filled)
            ])->default('draft');
            
            // PDF Storage
            $table->string('pdf_path')->nullable();
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form6');
    }
};
