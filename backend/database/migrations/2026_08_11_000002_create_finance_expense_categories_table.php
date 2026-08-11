<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $now = now();
        DB::table('finance_expense_categories')->insert([
            ['slug' => 'transportation', 'name' => 'Transportation', 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'materials', 'name' => 'Materials', 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'fuel', 'name' => 'Fuel', 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'accommodation', 'name' => 'Accommodation', 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'other', 'name' => 'Other', 'sort_order' => 100, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_expense_categories');
    }
};
