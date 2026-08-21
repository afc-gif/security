<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $slug = 'office-products';
        $exists = DB::table('finance_expense_categories')->where('slug', $slug)->exists();

        if (!$exists) {
            DB::table('finance_expense_categories')->insert([
                'slug' => $slug,
                'name' => 'Office Products',
                'sort_order' => 265,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('finance_expense_categories')->where('slug', 'office-products')->delete();
    }
};
