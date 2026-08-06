<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('type')->default('material');
            $table->string('name');
            $table->string('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_done')->default(false);
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('project_id');
            $table->index('type');
            $table->index('is_done');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requirements');
    }
};
