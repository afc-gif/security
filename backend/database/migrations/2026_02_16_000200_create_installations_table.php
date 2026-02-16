<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 120);
            $table->string('city', 120);
            $table->string('client_type', 120)->nullable();
            $table->date('completed_at')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('cover_image_public_id')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('gallery_image_public_ids')->nullable();
            $table->text('summary')->nullable();
            $table->text('outcome')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['is_public', 'is_featured', 'sort_order']);
            $table->index(['category']);
            $table->index(['city']);
            $table->index(['completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};
