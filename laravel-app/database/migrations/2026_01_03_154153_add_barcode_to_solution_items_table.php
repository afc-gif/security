<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solution_items', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('name');
        });

        // Backfill existing records with unique barcodes
        $items = DB::table('solution_items')->whereNull('barcode')->get();
        foreach ($items as $item) {
            $barcode = $this->generateBarcode();
            DB::table('solution_items')
                ->where('id', $item->id)
                ->update(['barcode' => $barcode]);
        }
    }

    public function down(): void
    {
        Schema::table('solution_items', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }

    private function generateBarcode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (DB::table('solution_items')->where('barcode', $code)->exists());

        return $code;
    }
};
