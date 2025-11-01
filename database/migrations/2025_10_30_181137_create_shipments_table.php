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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
    $table->foreignId('indent_id')->constrained('indents')->onDelete('cascade');
    $table->string('booking_no')->unique();             // 257360678
    $table->date('booking_date');                       // 08/08/2025
    $table->string('carrier')->nullable();              // MAERSK
    $table->string('vessel')->nullable();               // MAERSK SELETAR
    $table->string('status')->default('draft');
    $table->timestamps();
    $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
