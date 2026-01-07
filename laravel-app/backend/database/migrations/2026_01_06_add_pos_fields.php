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
        // Add solution_item_id to order_items
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'solution_item_id')) {
                $table->foreignId('solution_item_id')->nullable()->after('product_id')->constrained('solution_items')->onDelete('set null');
            }
        });

        // Add payment_method to orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'card', 'mobile'])->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'solution_item_id')) {
                $table->dropForeignIdFor('SolutionItem::class');
                $table->dropColumn('solution_item_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
