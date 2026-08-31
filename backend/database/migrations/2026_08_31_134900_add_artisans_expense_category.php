<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $slug = 'artisans';
        $exists = DB::table('finance_expense_categories')->where('slug', $slug)->exists();

        if (!$exists) {
            DB::table('finance_expense_categories')->insert([
                'slug'       => $slug,
                'name'       => 'Artisans',
                'sort_order' => 320,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('finance_expense_categories')->where('slug', 'artisans')->delete();
    }
};
