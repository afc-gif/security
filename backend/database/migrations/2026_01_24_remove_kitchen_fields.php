<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $columns = [
            'kitchen_status',
            'kitchen_note',
            'kitchen_eta_minutes',
            'kitchen_eta_at',
            'kitchen_sent_at',
        ];

        $existingColumns = array_values(array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn('orders', $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Restore kitchen-related columns
            if (!Schema::hasColumn('orders', 'kitchen_status')) {
                $table->string('kitchen_status')->default('pending')->after('status');
            }
            if (!Schema::hasColumn('orders', 'kitchen_note')) {
                $table->text('kitchen_note')->nullable()->after('kitchen_status');
            }
            if (!Schema::hasColumn('orders', 'kitchen_eta_minutes')) {
                $table->integer('kitchen_eta_minutes')->nullable()->after('kitchen_note');
            }
            if (!Schema::hasColumn('orders', 'kitchen_eta_at')) {
                $table->dateTime('kitchen_eta_at')->nullable()->after('kitchen_eta_minutes');
            }
            if (!Schema::hasColumn('orders', 'kitchen_sent_at')) {
                $table->dateTime('kitchen_sent_at')->nullable()->after('kitchen_eta_at');
            }
        });
    }
};
