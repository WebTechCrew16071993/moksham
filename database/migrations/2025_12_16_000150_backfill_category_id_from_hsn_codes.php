<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories') || !Schema::hasTable('hsn_codes')) {
            return;
        }

        // Only run if legacy 'category' string column still exists
        if (!Schema::hasColumn('hsn_codes', 'category') || !Schema::hasColumn('hsn_codes', 'category_id')) {
            return;
        }

        // Create categories for any distinct legacy values
        $names = DB::table('hsn_codes')->select('category')->distinct()->pluck('category')->filter()->unique();
        foreach ($names as $name) {
            // Insert if missing
            $exists = DB::table('categories')->where('name', $name)->exists();
            if (!$exists) {
                DB::table('categories')->insert([
                    'name' => $name,
                    'description' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Map each HSN row to its category_id
        $map = DB::table('categories')->pluck('id', 'name');
        $rows = DB::table('hsn_codes')->select('id', 'category')->get();
        foreach ($rows as $row) {
            $cid = $map[$row->category] ?? null;
            if ($cid) {
                DB::table('hsn_codes')->where('id', $row->id)->update(['category_id' => $cid]);
            }
        }
    }

    public function down(): void
    {
        // No-op. We won't delete any categories or unset category_id.
    }
};
