<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();    // Item ikut terhapus kalau transaksi dihapus

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();  // Produk tidak boleh dihapus kalau masih ada di transaksi

            $table->integer('qty');
            $table->decimal('price', 12, 2);      // Snapshot harga jual saat transaksi
            $table->decimal('cost_price', 12, 2)->nullable();  // Snapshot HPP untuk laporan margin
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE sale_items
            ADD CONSTRAINT chk_sale_items_qty_positive
            CHECK (qty > 0)
        ");

        DB::statement("
            ALTER TABLE sale_items
            ADD CONSTRAINT chk_sale_items_price_non_negative
            CHECK (price >= 0)
        ");

        DB::statement("
            ALTER TABLE sale_items
            ADD CONSTRAINT chk_sale_items_subtotal_correct
            CHECK (subtotal = qty * price)
        ");

        DB::statement('CREATE INDEX idx_sale_items_sale_id    ON sale_items(sale_id)');
        DB::statement('CREATE INDEX idx_sale_items_product_id ON sale_items(product_id)');

        // Composite: laporan produk terlaris
        DB::statement('
            CREATE INDEX idx_sale_items_product_sale
            ON sale_items(product_id, sale_id)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
