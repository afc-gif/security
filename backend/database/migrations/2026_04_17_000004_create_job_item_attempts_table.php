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
        Schema::create('job_item_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_request_item_id')->constrained('job_request_items')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('status'); // submitted, rejected, approved
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('job_request_item_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_item_attempts');
    }
};
