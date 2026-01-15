<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('indents', function (Blueprint $table) {
            if (!Schema::hasColumn('indents', 'show_signature')) {
                $table->boolean('show_signature')->default(false)->after('status');
            }
        });

        Schema::table('documentary_collection_letters', function (Blueprint $table) {
            if (!Schema::hasColumn('documentary_collection_letters', 'show_signature')) {
                $table->boolean('show_signature')->default(false)->after('status');
            }
        });

        Schema::table('bill_of_exchanges', function (Blueprint $table) {
            if (!Schema::hasColumn('bill_of_exchanges', 'show_signature')) {
                $table->boolean('show_signature')->default(false)->after('status');
            }
        });

        Schema::table('self_declarations', function (Blueprint $table) {
            if (!Schema::hasColumn('self_declarations', 'show_signature')) {
                $table->boolean('show_signature')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('indents', function (Blueprint $table) {
            if (Schema::hasColumn('indents', 'show_signature')) {
                $table->dropColumn('show_signature');
            }
        });

        Schema::table('documentary_collection_letters', function (Blueprint $table) {
            if (Schema::hasColumn('documentary_collection_letters', 'show_signature')) {
                $table->dropColumn('show_signature');
            }
        });

        Schema::table('bill_of_exchanges', function (Blueprint $table) {
            if (Schema::hasColumn('bill_of_exchanges', 'show_signature')) {
                $table->dropColumn('show_signature');
            }
        });

        Schema::table('self_declarations', function (Blueprint $table) {
            if (Schema::hasColumn('self_declarations', 'show_signature')) {
                $table->dropColumn('show_signature');
            }
        });
    }
};
