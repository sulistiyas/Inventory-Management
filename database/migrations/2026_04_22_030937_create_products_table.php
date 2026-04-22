<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);                      // NOT NULL
            $table->string('sku', 50)->unique();              // NOT NULL, UNIQUE — indexed
            $table->text('description')->nullable();

            // Foreign keys — RESTRICT prevents orphaning products
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();

            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();

            // Financial & inventory fields
            $table->decimal('price', 12, 2);                  // NOT NULL — up to 9,999,999,999.99
            $table->integer('stock')->default(0);             // NOT NULL, DEFAULT 0
            $table->integer('min_stock')->default(5);         // Threshold for low-stock alert
            $table->boolean('is_active')->default(true);      // Soft delete
            $table->timestamps();
        });

        // PostgreSQL CHECK constraints — enforce business rules at DB level
        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT chk_products_price_positive
            CHECK (price >= 0)
        ");

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT chk_products_stock_non_negative
            CHECK (stock >= 0)
        ");

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT chk_products_min_stock_non_negative
            CHECK (min_stock >= 0)
        ");

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT chk_products_name_not_empty
            CHECK (TRIM(name) <> '')
        ");

        DB::statement("
            ALTER TABLE products
            ADD CONSTRAINT chk_products_sku_not_empty
            CHECK (TRIM(sku) <> '')
        ");

        // Indexes for performance
        DB::statement('CREATE INDEX idx_products_sku         ON products(sku)');
        DB::statement('CREATE INDEX idx_products_category_id ON products(category_id)');
        DB::statement('CREATE INDEX idx_products_supplier_id ON products(supplier_id)');
        DB::statement('CREATE INDEX idx_products_is_active   ON products(is_active)');

        // Partial index: only index active products with low stock (used on dashboard)
        DB::statement('
            CREATE INDEX idx_products_low_stock
            ON products(stock)
            WHERE is_active = true
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};