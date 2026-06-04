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
        Schema::create('service_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_order_id')
                  ->constrained('service_orders')
                  ->cascadeOnDelete();    // Item ikut terhapus kalau work order dihapus

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();  // Produk tidak boleh dihapus kalau masih dipakai di WO

            $table->integer('qty');
            $table->decimal('price', 12, 2);   // Harga saat transaksi (snapshot)
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE service_order_items
            ADD CONSTRAINT chk_soi_qty_positive
            CHECK (qty > 0)
        ");

        DB::statement("
            ALTER TABLE service_order_items
            ADD CONSTRAINT chk_soi_price_non_negative
            CHECK (price >= 0)
        ");

        DB::statement("
            ALTER TABLE service_order_items
            ADD CONSTRAINT chk_soi_subtotal_correct
            CHECK (subtotal = qty * price)
        ");

        DB::statement('CREATE INDEX idx_soi_service_order_id ON service_order_items(service_order_id)');
        DB::statement('CREATE INDEX idx_soi_product_id        ON service_order_items(product_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_items');
    }
};
