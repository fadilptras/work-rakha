<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sales = \Illuminate\Support\Facades\DB::table('sales');
        $products = \Illuminate\Support\Facades\DB::table('products');

        $salesProductNames = $sales->pluck('product_name')->unique()->toArray();

        foreach ($salesProductNames as $salesName) {
            $matchedProduct = $products->where('product_name', $salesName)->first();

            if ($matchedProduct) {
                \Illuminate\Support\Facades\DB::table('sales')
                    ->where('product_name', $salesName)
                    ->update(['product_name' => $matchedProduct->product_name_clean]);
            }
        }
    }

    public function down(): void
    {
        // Optionally revert, but product_name_clean is the "clean" version
        // This migration is intended as a one-time sync
    }
};