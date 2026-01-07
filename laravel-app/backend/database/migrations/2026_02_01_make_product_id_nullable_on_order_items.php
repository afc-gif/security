<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow POS-created order items without a products.product_id.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->unsignedBigInteger('product_id')->nullable()->change();
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->unsignedBigInteger('product_id')->nullable(false)->change();
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('cascade');
            }
        });
    }
};
