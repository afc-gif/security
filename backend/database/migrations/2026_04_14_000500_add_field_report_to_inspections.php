<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->text('findings')->nullable()->after('priority');
            $table->text('risks_identified')->nullable()->after('findings');
            $table->text('recommendations')->nullable()->after('risks_identified');
            $table->timestamp('submitted_at')->nullable()->after('recommendations');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->text('review_notes')->nullable()->after('reviewed_at');
        });

        Schema::create('inspection_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
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
        Schema::dropIfExists('inspection_media');

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn([
                'findings',
                'risks_identified',
                'recommendations',
                'submitted_at',
                'reviewed_at',
                'review_notes',
            ]);
        });
    }
};
