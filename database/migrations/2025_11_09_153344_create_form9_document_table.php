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
        Schema::create('form9_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('packing_list_id')->nullable()->constrained('packing_lists')->nullOnDelete();
            $table->foreignId('bl_correction_id')->nullable()->constrained('bl_corrections')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('form6_id')->nullable()->constrained('form6_documents')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            
            // Form Reference
            $table->string('form9_no', 100)->unique();
            $table->date('form9_date');
            
            // 1(i). Exporter Details - Use MEDIUMTEXT for long addresses
            $table->mediumText('exporter_name_address');
            $table->string('exporter_contact_person', 150);
            $table->string('exporter_phone', 50)->nullable();
            $table->string('exporter_fax', 50)->nullable();
            $table->string('exporter_email', 150)->nullable();
            
            // 1(ii). Waste Generator Details
            $table->mediumText('generator_name_address');
            $table->string('generator_contact_person', 150);
            $table->string('generator_phone', 50)->nullable();
            $table->string('generator_fax', 50)->nullable();
            $table->string('generator_email', 150)->nullable();
            $table->text('site_of_generation')->nullable();
            
            // 2. Importer/Recycler Details
            $table->foreignId('importer_id')->nullable()->constrained('companies');
            $table->mediumText('importer_name_address');
            $table->string('importer_contact_person', 150);
            $table->string('importer_phone', 50)->nullable();
            $table->string('importer_fax', 50)->nullable();
            $table->string('importer_email', 150)->nullable();
            
            // 3 & 4. References
            $table->string('applicant_ref_no', 100)->nullable();
            $table->enum('movement_type', ['single', 'multiple'])->default('single');
            $table->string('bill_of_lading', 150)->nullable();
            
            // 5. Carrier Details - MOVE TO SEPARATE TABLE (form9_carriers)
            // This reduces row size significantly
            
            // 6. Disposer Details
            $table->mediumText('disposer_name_address');
            $table->string('disposer_contact_person', 150)->nullable();
            $table->text('actual_site_of_disposal');
            $table->string('disposer_phone', 50)->nullable();
            $table->string('disposer_fax', 50)->nullable();
            $table->string('disposer_email', 150)->nullable();
            
            // 7. Method of Recovery
            $table->string('method_of_recovery', 150);
            $table->string('r_code', 10);
            $table->text('technology_employed');
            
            // 8-10. Waste Details
            $table->text('waste_designation_composition');
            $table->string('physical_characteristics', 100);
            $table->decimal('actual_quantity_kgs', 15, 3);
            $table->text('waste_description');
            
            // 11. Waste Identification Codes
            $table->string('waste_identification_code', 50)->nullable();
            $table->string('basel_no', 50)->nullable();
            $table->string('oecd_no', 50)->nullable();
            $table->string('un_no', 50)->nullable();
            $table->string('itc_hs', 50)->nullable();
            $table->string('customs_code_hs', 50);
            $table->string('other_codes', 100)->nullable();
            
            // 12. OECD Classification
            $table->string('oecd_classification', 50)->nullable();
            $table->string('oecd_color', 50)->nullable();
            $table->string('oecd_number', 50)->nullable();
            
            // 13. Packaging
            $table->string('packaging_type', 100);
            $table->integer('packaging_number');
            
            // 14. UN Classification
            $table->string('un_classification', 50)->nullable();
            $table->string('un_shipping_name', 150)->nullable();
            $table->string('un_identification_no', 50)->nullable();
            $table->string('un_class', 20)->nullable();
            $table->string('h_number', 20)->nullable();
            $table->string('y_number', 50)->nullable();
            
            // 15-16. Handling and Shipment
            $table->text('special_handling_requirements')->nullable();
            $table->date('actual_shipment_date');
            $table->string('means_of_transport', 20)->nullable();
            
            // 17. Exporter Declaration
            $table->text('exporter_declaration')->nullable();
            $table->date('exporter_declaration_date');
            $table->string('exporter_signature_name', 150);
            $table->string('exporter_signature_image_path')->nullable();
            
            // 18. Importer Receipt
            $table->decimal('quantity_received_importer', 15, 3)->nullable();
            $table->date('date_received_importer')->nullable();
            $table->string('signature_received_importer', 150)->nullable();
            $table->string('signature_received_importer_image_path')->nullable();
            $table->string('name_received_importer', 150)->nullable();
            
            // 19. Recycler Receipt
            $table->decimal('quantity_received_recycler', 15, 3)->nullable();
            $table->decimal('quantity_accepted_recycler', 15, 3)->nullable();
            $table->date('date_received_recycler')->nullable();
            $table->string('signature_received_recycler', 150)->nullable();
            $table->string('signature_received_recycler_image_path')->nullable();
            $table->string('name_received_recycler', 150)->nullable();
            
            // 20-22. Recycling Details
            $table->date('approximate_recycling_date')->nullable();
            $table->text('method_of_recycling')->nullable();
            $table->text('recycler_certification')->nullable();
            $table->date('recycler_certification_date')->nullable();
            $table->string('recycler_certification_signature', 150)->nullable();
            $table->string('recycler_certification_signature_image_path')->nullable();
            
            // 23. Specific Conditions
            $table->text('specific_conditions')->nullable();
            $table->text('terms_and_description')->nullable();
            $table->json('recovery_operations')->nullable();
            $table->text('notes')->nullable();
            
            // Status Management
            $table->enum('status', [
                'draft', 'submitted', 'approved', 'shipped', 
                'received_importer', 'received_recycler', 
                'recycling_scheduled', 'recycling_completed', 'completed'
            ])->default('draft');
            
            // PDF Storage
            $table->string('pdf_path')->nullable();
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_importer_at')->nullable();
            $table->timestamp('received_recycler_at')->nullable();
            $table->timestamp('recycling_scheduled_at')->nullable();
            $table->timestamp('recycling_completed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
        
        // SEPARATE TABLE for Carriers (reduces main table row size)
        Schema::create('form9_carriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form9_id')->constrained('form9_documents')->onDelete('cascade');
            
            $table->enum('carrier_type', ['first', 'second', 'last']); // Which carrier
            $table->text('name_address')->nullable();
            $table->string('registration_no', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('transport_identity', 150)->nullable();
            $table->date('transfer_date')->nullable();
            $table->string('signature', 150)->nullable();
            $table->string('signature_image_path')->nullable();
            
            $table->timestamps();
            
            $table->unique(['form9_id', 'carrier_type']); // Only one of each type per form
        });
        
        // Track document flow for customs forms
        Schema::create('customs_form_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            
            $table->string('form_type');                                // form6, form9
            $table->unsignedBigInteger('form_id');                     // ID of form6 or form9
            $table->string('action');                                   // created, submitted, approved, received, completed
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();                       // Store additional data
            
            $table->timestamps();
            
            $table->index(['form_type', 'form_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customs_form_history');
        Schema::dropIfExists('form9_carriers');
        Schema::dropIfExists('form9_documents');
    }
};
