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
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solution_item_id')->constrained('solution_items')->onDelete('cascade');
            $table->string('alert_type'); // 'low_stock', 'out_of_stock'
            $table->integer('threshold')->default(0); // stock level that triggered alert
            $table->integer('current_stock'); // stock at time of alert
            $table->unsignedBigInteger('created_by')->nullable(); // who should be notified
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable(); // when admin acknowledged
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->foreign('acknowledged_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['solution_item_id', 'alert_type']);
            $table->index('acknowledged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
