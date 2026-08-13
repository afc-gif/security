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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 50)->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('job_request_id')->nullable()->constrained('job_requests')->nullOnDelete();
            $table->foreignId('job_request_item_id')->nullable()->constrained('job_request_items')->nullOnDelete();
            $table->foreignId('inspection_id')->nullable()->constrained('inspections')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();

            $table->string('title', 255);
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->string('status', 30)->default('draft'); // draft, sent, accepted, rejected, expired, cancelled

            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index('quotation_number');
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('description', 255);
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 15, 2)->default(0.00);
            $table->decimal('total_price', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('project_payments') && !Schema::hasColumn('project_payments', 'quotation_id')) {
            Schema::table('project_payments', function (Blueprint $table) {
                $table->foreignId('quotation_id')->nullable()->after('client_id')->constrained('quotations')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_payments') && Schema::hasColumn('project_payments', 'quotation_id')) {
            Schema::table('project_payments', function (Blueprint $table) {
                $table->dropForeign(['quotation_id']);
                $table->dropColumn('quotation_id');
            });
        }

        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
