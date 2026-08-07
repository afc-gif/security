<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_checklist_templates', function (Blueprint $table) {
            $table->string('input_type')->default('textarea')->after('description');
            $table->json('options')->nullable()->after('input_type');
        });

        Schema::table('job_checklist_items', function (Blueprint $table) {
            $table->string('input_type')->default('textarea')->after('description');
            $table->json('options')->nullable()->after('input_type');
            $table->text('response')->nullable()->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('job_checklist_items', function (Blueprint $table) {
            $table->dropColumn(['input_type', 'options', 'response']);
        });

        Schema::table('category_checklist_templates', function (Blueprint $table) {
            $table->dropColumn(['input_type', 'options']);
        });
    }
};
