<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend financial_expenses with office expense flag + payment info
        Schema::table('financial_expenses', function (Blueprint $table) {
            // True when this is a general/office expense (not tied to a job/project/inspection)
            $table->boolean('is_office_expense')->default(false)->after('updated_by');
            // Payment method for the expense (cash, bank_transfer, pos, etc.)
            $table->string('payment_method')->nullable()->after('is_office_expense');
            // Reference number (cheque no, transfer ref, etc.)
            $table->string('reference')->nullable()->after('payment_method');

            $table->index('is_office_expense');
        });

        // 2. Add office-specific categories (only insert new ones)
        $now = now();
        $existing = DB::table('finance_expense_categories')->pluck('slug')->toArray();

        $officeCategories = [
            ['slug' => 'diesel',          'name' => 'Diesel',          'sort_order' => 200],
            ['slug' => 'salary',          'name' => 'Salary',          'sort_order' => 210],
            ['slug' => 'waste-disposal',  'name' => 'Waste Disposal',  'sort_order' => 220],
            ['slug' => 'battery',         'name' => 'Battery',         'sort_order' => 230],
            ['slug' => 'electricity',     'name' => 'Electricity',     'sort_order' => 240],
            ['slug' => 'internet',        'name' => 'Internet',        'sort_order' => 250],
            ['slug' => 'office-supplies', 'name' => 'Office Supplies', 'sort_order' => 260],
            ['slug' => 'rent',            'name' => 'Rent',            'sort_order' => 270],
            ['slug' => 'maintenance',     'name' => 'Maintenance',     'sort_order' => 280],
            ['slug' => 'repairs',         'name' => 'Repairs',         'sort_order' => 290],
            ['slug' => 'security',        'name' => 'Security',        'sort_order' => 300],
        ];

        $toInsert = [];
        foreach ($officeCategories as $cat) {
            if (!in_array($cat['slug'], $existing, true)) {
                $toInsert[] = array_merge($cat, ['created_at' => $now, 'updated_at' => $now]);
            }
        }

        if (!empty($toInsert)) {
            DB::table('finance_expense_categories')->insert($toInsert);
        }
    }

    public function down(): void
    {
        Schema::table('financial_expenses', function (Blueprint $table) {
            $table->dropIndex(['is_office_expense']);
            $table->dropColumn(['is_office_expense', 'payment_method', 'reference']);
        });

        DB::table('finance_expense_categories')->whereIn('slug', [
            'diesel', 'salary', 'waste-disposal', 'battery', 'electricity',
            'internet', 'office-supplies', 'rent', 'maintenance', 'repairs', 'security',
        ])->delete();
    }
};
