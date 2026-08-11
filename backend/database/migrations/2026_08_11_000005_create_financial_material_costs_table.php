<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_material_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('inspection_id')->nullable()->constrained('inspections')->nullOnDelete();
            $table->foreignId('job_request_item_id')->nullable()->constrained('job_request_items')->nullOnDelete();
            $table->string('original_context_type')->nullable();
            $table->unsignedBigInteger('original_context_id')->nullable();
            $table->string('material_name');
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->decimal('total_cost', 14, 2);
            $table->date('incurred_on')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('project_id');
            $table->index('inspection_id');
            $table->index('job_request_item_id');
            $table->index(['original_context_type', 'original_context_id']);
            $table->index('status');
            $table->index('incurred_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_material_costs');
    }
};
