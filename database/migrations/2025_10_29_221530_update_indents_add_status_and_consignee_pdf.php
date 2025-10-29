<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('indents', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('indent_date');
            $table->string('consignee_signed_pdf_path')->nullable()->after('consignee_signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('indents', function (Blueprint $table) {
            $table->dropColumn(['status', 'consignee_signed_pdf_path']);
        });
    }
};
