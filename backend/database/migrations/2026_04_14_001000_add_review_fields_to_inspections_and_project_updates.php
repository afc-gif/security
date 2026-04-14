<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('inspections', 'review_status')) {
                $table->string('review_status')->default('pending_review');
            }
            if (!Schema::hasColumn('inspections', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('inspections', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('inspections', 'review_notes')) {
                $table->text('review_notes')->nullable();
            }
        });

        Schema::table('project_updates', function (Blueprint $table) {
            if (!Schema::hasColumn('project_updates', 'review_status')) {
                $table->string('review_status')->default('pending_review');
            }
            if (!Schema::hasColumn('project_updates', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('project_updates', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('project_updates', 'review_notes')) {
                $table->text('review_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_updates', function (Blueprint $table) {
            if (Schema::hasColumn('project_updates', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
            }
            $drop = array_values(array_filter([
                Schema::hasColumn('project_updates', 'review_status') ? 'review_status' : null,
                Schema::hasColumn('project_updates', 'reviewed_by') ? 'reviewed_by' : null,
                Schema::hasColumn('project_updates', 'reviewed_at') ? 'reviewed_at' : null,
                Schema::hasColumn('project_updates', 'review_notes') ? 'review_notes' : null,
            ]));
            if ($drop) {
                $table->dropColumn($drop);
            }
        });

        Schema::table('inspections', function (Blueprint $table) {
            if (Schema::hasColumn('inspections', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
            }
            $drop = array_values(array_filter([
                Schema::hasColumn('inspections', 'review_status') ? 'review_status' : null,
                Schema::hasColumn('inspections', 'reviewed_by') ? 'reviewed_by' : null,
            ]));
            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};
