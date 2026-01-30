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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_item_id')->constrained('solution_items')->onDelete('cascade');
            $table->integer('quantity_changed'); // positive for stock add, negative for stock use
            $table->string('transaction_type'); // 'sale', 'manual_add', 'manual_remove', 'return'
            $table->string('reference_type')->nullable(); // 'order', 'admin', etc
            $table->unsignedBigInteger('reference_id')->nullable(); // order_id, etc
            $table->unsignedBigInteger('user_id')->nullable(); // who made the change
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['solution_item_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
