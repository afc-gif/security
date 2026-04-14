<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('assigned_field_staff_id')->nullable()->after('assigned_manager_id')->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('progress_percentage')->default(0)->after('status');
        });

        Schema::create('project_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('summary')->nullable();
            $table->text('work_done')->nullable();
            $table->text('materials_used')->nullable();
            $table->text('issues_encountered')->nullable();
            $table->unsignedTinyInteger('progress_percentage')->nullable();
            $table->text('next_step')->nullable();
            $table->date('work_date')->nullable();
            $table->timestamps();
        });

        Schema::create('project_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_update_id')->nullable()->constrained('project_updates')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_media');
        Schema::dropIfExists('project_updates');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['assigned_field_staff_id']);
            $table->dropColumn(['assigned_field_staff_id', 'progress_percentage']);
        });
    }
};
