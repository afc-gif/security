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
        Schema::table('orders', function (Blueprint $table) {
            // Drop kitchen-related columns
            $table->dropColumn([
                'kitchen_status',
                'kitchen_note',
                'kitchen_eta_minutes',
                'kitchen_eta_at',
                'kitchen_sent_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Restore kitchen-related columns
            $table->string('kitchen_status')->default('pending')->after('status');
            $table->text('kitchen_note')->nullable()->after('kitchen_status');
            $table->integer('kitchen_eta_minutes')->nullable()->after('kitchen_note');
            $table->dateTime('kitchen_eta_at')->nullable()->after('kitchen_eta_minutes');
            $table->dateTime('kitchen_sent_at')->nullable()->after('kitchen_eta_at');
        });
    }
};
