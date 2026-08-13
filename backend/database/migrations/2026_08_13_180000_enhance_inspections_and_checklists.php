<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('inspections', 'service_category_id')) {
                $table->foreignId('service_category_id')->nullable()->after('client_id')->constrained('service_categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('inspections', 'job_request_item_id')) {
                $table->foreignId('job_request_item_id')->nullable()->after('service_category_id')->constrained('job_request_items')->nullOnDelete();
            }
            if (!Schema::hasColumn('inspections', 'return_reason')) {
                $table->text('return_reason')->nullable()->after('review_notes');
            }
            if (!Schema::hasColumn('inspections', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('return_reason');
            }
            if (!Schema::hasColumn('inspections', 'returned_by')) {
                $table->foreignId('returned_by')->nullable()->after('returned_at')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('job_checklist_items', function (Blueprint $table) {
            if (Schema::hasColumn('job_checklist_items', 'job_request_item_id')) {
                $table->foreignId('job_request_item_id')->nullable()->change();
            }
            if (!Schema::hasColumn('job_checklist_items', 'inspection_id')) {
                $table->foreignId('inspection_id')->nullable()->after('job_request_item_id')->constrained('inspections')->cascadeOnDelete();
                $table->index('inspection_id');
            }
        });

        if (!Schema::hasTable('inspection_revisions')) {
            Schema::create('inspection_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action'); // submitted, returned, approved, rejected
                $table->text('findings')->nullable();
                $table->text('risks_identified')->nullable();
                $table->text('recommendations')->nullable();
                $table->text('return_reason')->nullable();
                $table->text('admin_notes')->nullable();
                $table->json('snapshot_data')->nullable();
                $table->timestamps();

                $table->index(['inspection_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_revisions');

        Schema::table('job_checklist_items', function (Blueprint $table) {
            if (Schema::hasColumn('job_checklist_items', 'inspection_id')) {
                $table->dropForeign(['inspection_id']);
                $table->dropColumn('inspection_id');
            }
        });

        Schema::table('inspections', function (Blueprint $table) {
            if (Schema::hasColumn('inspections', 'returned_by')) {
                $table->dropForeign(['returned_by']);
                $table->dropColumn('returned_by');
            }
            if (Schema::hasColumn('inspections', 'service_category_id')) {
                $table->dropForeign(['service_category_id']);
                $table->dropColumn('service_category_id');
            }
            if (Schema::hasColumn('inspections', 'job_request_item_id')) {
                $table->dropForeign(['job_request_item_id']);
                $table->dropColumn('job_request_item_id');
            }
            $table->dropColumn(['return_reason', 'returned_at']);
        });
    }
};
