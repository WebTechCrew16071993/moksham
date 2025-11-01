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
        Schema::create('packing_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->date('date');                               // 08/08/2025
            $table->string('employee');                         // Nic Patel
        
            $table->string('origin')->nullable();               // CHICAGO, IL
            $table->string('destination')->nullable();          // HAZIRA, INDIA
            $table->date('ship_date')->nullable();              // 08/16/2025
            $table->date('arrival_date')->nullable();           // 09/29/2025
            $table->string('order_no')->nullable();             // 1002
            $table->string('ship_in')->nullable();              // 40FT HC CNTR
            $table->string('contact')->nullable();              // Rahul Patel
            $table->string('phone')->nullable();                // +91 9714003111
        
            $table->integer('total_bales')->nullable();         // 174
            $table->decimal('total_weight_lbs', 15, 3)->nullable(); // 232010
            $table->decimal('total_weight_kg', 15, 3)->nullable();  // 105238.00 (calculated)
        
            $table->string('status')->default('draft');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // 7. packing_list_items
        Schema::create('packing_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained('packing_lists')->onDelete('cascade');
            $table->string('container_no');                     // MRKU5824716
            $table->string('seal_no');                          // 002334
            $table->string('description');                      // OCC 11 HS CODE 47079000
            $table->integer('no_of_bales');                     // 35
            $table->decimal('weight_lbs', 15, 3);               // 46980
            $table->decimal('weight_kg', 15, 3)->nullable();    // Auto-calculated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_lists');
        Schema::dropIfExists('packing_list_items');
    }
};
