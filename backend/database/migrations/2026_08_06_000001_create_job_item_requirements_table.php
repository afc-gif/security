<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_item_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_item_attempt_id')->constrained('job_item_attempts')->cascadeOnDelete();
            $table->string('type')->default('material');
            $table->string('name');
            $table->string('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('job_item_attempt_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_item_requirements');
    }
};
