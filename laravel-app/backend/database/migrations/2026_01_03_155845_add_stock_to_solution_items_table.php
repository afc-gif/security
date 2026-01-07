<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solution_items', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('price');
        });

        // Backfill nulls to 0 for safety
        DB::table('solution_items')->whereNull('stock')->update(['stock' => 0]);
    }

    public function down(): void
    {
        Schema::table('solution_items', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};
