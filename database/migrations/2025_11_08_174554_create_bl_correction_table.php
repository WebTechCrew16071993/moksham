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
        Schema::create('bl_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('packing_list_id')->constrained('packing_lists')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            
            // BL Reference
            $table->string('bl_no')->nullable();                    // Will be assigned by shipping line later
            $table->string('booking_no');                           // 59750881 (from shipment)
            $table->date('bl_date');
            
            // Shipper Details (from companies table or config)
            $table->text('shipper_details')->nullable();            // MOKSHAM EXPORT IMPORT LLC full address
            $table->string('shipper_phone')->nullable();            // 201-982-4039
            $table->string('shipper_email')->nullable();            // Mokshamusa108@gmail.com
            
            // Consignee Details (from companies/buyers table)
            $table->foreignId('consignee_id')->nullable()->constrained('companies');
            $table->text('consignee_details')->nullable();          // AMBITION PAPER TECH full address
            $table->string('consignee_iec')->nullable();            // AAUCA0892M
            $table->string('consignee_gstin')->nullable();          // 24AAUCA0892M1Z3
            $table->string('consignee_pan')->nullable();            // AAUCA0892M
            $table->string('consignee_email')->nullable();          // ambitionpaper2020@gmail.com
            
            // Notify Party (Usually same as consignee)
            $table->text('notify_party_details')->nullable();       // Can be same as consignee
            $table->string('notify_party_iec')->nullable();
            $table->string('notify_party_gstin')->nullable();
            $table->string('notify_party_pan')->nullable();
            $table->string('notify_party_email')->nullable();
            
            // Port & Location Details
            $table->string('port_of_loading');                      // NORFOLK, VA
            $table->string('origin');                               // CHICAGO, IL
            $table->string('destination');                          // MUNDRA, INDIA
            
            // Cargo Summary
            $table->decimal('net_weight_kgs', 15, 3);              // 108925.671
            $table->decimal('net_weight_mts', 15, 3);              // 108.925671
            $table->decimal('cargo_value', 15, 2);                 // $21,785.20
            $table->string('currency', 3)->default('USD');
            
            // Container Details
            $table->string('packaging_type')->nullable();           // BALE, COMPRESSED
            $table->string('ship_in')->nullable();                  // 40'X9'6"HC
            $table->integer('no_of_containers');                    // 5
            $table->string('container_type')->nullable();           // 40HC
            $table->string('document_type')->default('OBL');        // OBL
            
            // Commodity Summary
            $table->integer('total_bales');                         // 192
            $table->string('hs_code')->nullable();                  // 47079000
            $table->text('commodity_description')->nullable();      // WASTEPAPER OCC 11 HS CODE 47079000
            
            // Status Workflow
            $table->enum('status', [
                'draft',                    // Being prepared
                'pending_review',           // Sent to buyer for 24hr review
                'corrections_requested',    // Buyer found issues
                'approved',                 // Buyer approved
                'finalized'                // Final BL issued
            ])->default('draft');
            
            // Buyer Review Tracking
            $table->timestamp('sent_to_buyer_at')->nullable();
            $table->timestamp('buyer_reviewed_at')->nullable();
            $table->text('buyer_comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            // PDF Storage
            $table->string('draft_pdf_path')->nullable();
            $table->string('final_pdf_path')->nullable();
            
            // Revision Management
            $table->integer('revision_number')->default(1);
            $table->foreignId('parent_bl_id')->nullable()->constrained('bl_corrections')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('bl_correction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bl_correction_id')->constrained('bl_corrections')->onDelete('cascade');
            $table->string('container_no');                         // FANU3181228
            $table->string('seal_no');                              // 237538
            $table->string('commodity');                            // WASTPAPER OCC 11 HS CODE 47079000
            $table->string('hs_code');                              // 47079000
            $table->integer('no_of_bales');                         // 40
            $table->decimal('weight_kgs', 15, 3);                   // 24167.401
            
            $table->text('correction_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bl_correction_items');
        Schema::dropIfExists('bl_corrections');
    }
};
