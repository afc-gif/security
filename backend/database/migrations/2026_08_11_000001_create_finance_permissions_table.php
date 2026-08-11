<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_finance_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('finance_permission_id')->constrained('finance_permissions')->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('granted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'finance_permission_id']);
            $table->index('granted_by');
        });

        $now = now();
        DB::table('finance_permissions')->insert([
            [
                'slug' => 'finance.view',
                'name' => 'View finance records',
                'description' => 'View private installation/project financial records.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'finance.create',
                'name' => 'Create finance records',
                'description' => 'Create installation/project expenses, material costs, and financial values.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'finance.edit',
                'name' => 'Edit finance records',
                'description' => 'Edit private installation/project financial records.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'finance.approve',
                'name' => 'Approve finance records',
                'description' => 'Approve or reject private installation/project financial records.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'finance.delete',
                'name' => 'Delete finance records',
                'description' => 'Delete private installation/project financial records.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'finance.reports',
                'name' => 'View finance reports',
                'description' => 'View private installation/project finance reports.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_finance_permissions');
        Schema::dropIfExists('finance_permissions');
    }
};
