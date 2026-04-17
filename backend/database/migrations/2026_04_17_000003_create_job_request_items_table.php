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
        Schema::create('job_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_request_id')->constrained('job_requests')->onDelete('cascade');
            $table->foreignId('service_category_id')->constrained('service_categories')->onDelete('restrict');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Assignment
            $table->foreignId('claimed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('claimed_at')->nullable();
            
            // Workflow
            $table->string('status')->default('open');
            $table->dateTime('due_date')->nullable();
            $table->timestamp('reopened_at')->nullable();
            
            // Priority & Classification
            $table->string('priority')->default('medium'); // low, medium, high
            
            // Submission
            $table->timestamp('submitted_at')->nullable();
            
            // Tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('job_request_id');
            $table->index('service_category_id');
            $table->index('status');
            $table->index('claimed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_request_items');
    }
};
