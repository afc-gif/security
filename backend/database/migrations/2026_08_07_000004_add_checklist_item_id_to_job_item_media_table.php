<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_item_media', function (Blueprint $table) {
            $table->foreignId('job_checklist_item_id')
                ->nullable()
                ->after('job_item_attempt_id')
                ->constrained('job_checklist_items')
                ->nullOnDelete();

            $table->index('job_checklist_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('job_item_media', function (Blueprint $table) {
            $table->dropConstrainedForeignId('job_checklist_item_id');
        });
    }
};
