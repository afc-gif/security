<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to modify the enum type
        Schema::table('orders', function (Blueprint $table) {
            // Drop the old check constraint and recreate with new values
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_method_check");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_method_check CHECK (payment_method IS NULL OR payment_method IN ('cash', 'card', 'mobile', 'transfer', 'other'))");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_method_check");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_method_check CHECK (payment_method IS NULL OR payment_method IN ('cash', 'card', 'mobile'))");
        });
    }
};
