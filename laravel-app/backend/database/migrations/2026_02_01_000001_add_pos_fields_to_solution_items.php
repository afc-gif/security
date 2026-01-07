<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solution_items', function (Blueprint $table) {
            if (!Schema::hasColumn('solution_items', 'is_sold_out')) {
                $table->boolean('is_sold_out')->default(false)->after('active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solution_items', function (Blueprint $table) {
            if (Schema::hasColumn('solution_items', 'is_sold_out')) {
                $table->dropColumn('is_sold_out');
            }
        });
    }
};
