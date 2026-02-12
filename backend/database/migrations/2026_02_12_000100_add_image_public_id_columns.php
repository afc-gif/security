<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['products', 'categories', 'menu_items', 'solution_items'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (Schema::hasColumn($tableName, 'image_public_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('image_public_id')->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        $tables = ['products', 'categories', 'menu_items', 'solution_items'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'image_public_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('image_public_id');
            });
        }
    }
};
