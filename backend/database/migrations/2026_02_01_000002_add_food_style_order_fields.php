<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'code')) {
                $table->string('code')->nullable()->after('id');
            }
            if (!Schema::hasColumn('orders', 'channel')) {
                $table->string('channel')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('channel');
            }
            if (!Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('customer_phone');
            }
            if (!Schema::hasColumn('orders', 'tax')) {
                $table->decimal('tax', 10, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('tax');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'kitchen_status')) {
                $table->string('kitchen_status')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('orders', 'kitchen_note')) {
                $table->string('kitchen_note')->nullable()->after('kitchen_status');
            }
            if (!Schema::hasColumn('orders', 'kitchen_eta_minutes')) {
                $table->integer('kitchen_eta_minutes')->nullable()->after('kitchen_note');
            }
            if (!Schema::hasColumn('orders', 'kitchen_eta_at')) {
                $table->timestamp('kitchen_eta_at')->nullable()->after('kitchen_eta_minutes');
            }
            if (!Schema::hasColumn('orders', 'kitchen_sent_at')) {
                $table->timestamp('kitchen_sent_at')->nullable()->after('kitchen_eta_at');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'name')) {
                $table->string('name')->nullable()->after('solution_item_id');
            }
            if (!Schema::hasColumn('order_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('order_items', 'total')) {
                $table->decimal('total', 10, 2)->nullable()->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'code',
                'channel',
                'customer_name',
                'customer_phone',
                'subtotal',
                'tax',
                'discount',
                'paid_at',
                'kitchen_status',
                'kitchen_note',
                'kitchen_eta_minutes',
                'kitchen_eta_at',
                'kitchen_sent_at',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            $columns = ['name', 'unit_price', 'total'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
