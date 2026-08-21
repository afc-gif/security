<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create Suppliers Table
        Schema::create('finance_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 2. Create Inventory Products Table
        Schema::create('finance_inventory_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            $table->decimal('current_stock', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // 3. Create Procurements Table
        Schema::create('finance_procurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('finance_suppliers')->onDelete('cascade');
            $table->foreignId('inventory_product_id')->constrained('finance_inventory_products')->onDelete('cascade');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('total_cost', 14, 2);
            $table->date('purchase_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('inventory_product_id');
            $table->index('purchase_date');
        });

        // 4. Modify financial_material_costs
        Schema::table('financial_material_costs', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('finance_suppliers')->nullOnDelete();
            $table->foreignId('inventory_product_id')->nullable()->constrained('finance_inventory_products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_material_costs', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['inventory_product_id']);
            $table->dropColumn(['supplier_id', 'inventory_product_id']);
        });

        Schema::dropIfExists('finance_procurements');
        Schema::dropIfExists('finance_inventory_products');
        Schema::dropIfExists('finance_suppliers');
    }
};
