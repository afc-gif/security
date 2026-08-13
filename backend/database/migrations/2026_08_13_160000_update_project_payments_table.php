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
        Schema::table('project_payments', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->change();
            $table->foreignId('inspection_id')->nullable()->after('project_id')->constrained('inspections')->nullOnDelete();
            $table->foreignId('job_request_id')->nullable()->after('inspection_id')->constrained('job_requests')->nullOnDelete();
            $table->foreignId('job_request_item_id')->nullable()->after('job_request_id')->constrained('job_request_items')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->after('job_request_item_id')->constrained('clients')->nullOnDelete();
            $table->string('payment_type')->default('part_payment')->after('client_id');
            $table->string('original_context_type')->nullable()->after('payment_type');
            $table->unsignedBigInteger('original_context_id')->nullable()->after('original_context_type');

            $table->index('inspection_id');
            $table->index('job_request_id');
            $table->index('job_request_item_id');
            $table->index('client_id');
            $table->index(['original_context_type', 'original_context_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_payments', function (Blueprint $table) {
            $table->dropForeign(['inspection_id']);
            $table->dropForeign(['job_request_id']);
            $table->dropForeign(['job_request_item_id']);
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'inspection_id',
                'job_request_id',
                'job_request_item_id',
                'client_id',
                'payment_type',
                'original_context_type',
                'original_context_id',
            ]);
        });
    }
};
