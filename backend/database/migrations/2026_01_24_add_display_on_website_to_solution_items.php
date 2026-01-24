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
        Schema::table('solution_items', function (Blueprint $table) {
            // Add display_on_website column - default to true for existing products
            $table->boolean('display_on_website')->default(true)->after('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solution_items', function (Blueprint $table) {
            $table->dropColumn('display_on_website');
        });
    }
};
