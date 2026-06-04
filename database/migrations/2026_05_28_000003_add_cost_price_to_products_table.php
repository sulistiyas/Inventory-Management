<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Tambah harga beli (HPP) setelah kolom price
            // Nullable dulu agar tidak break produk yang sudah ada
            $table->decimal('cost_price', 12, 2)->nullable()->after('price');
        });

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT chk_products_cost_price_positive
            CHECK (cost_price IS NULL OR cost_price >= 0)
        ");

        // Cost price tidak boleh lebih mahal dari harga jual (warning level)
        // Dijadikan CHECK opsional — dinonaktifkan jika bisnis model berbeda
        // DB::statement("
        //     ALTER TABLE products
        //     ADD CONSTRAINT chk_products_margin_positive
        //     CHECK (cost_price IS NULL OR price >= cost_price)
        // ");
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
