<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the movement type ENUM
        DB::statement("CREATE TYPE stock_movement_type AS ENUM ('in', 'out')");

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Product: CASCADE — if product is deleted, its movement history goes too
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            // User: SET NULL — preserve movement history even if the user account is deleted
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Quantity of units moved — always positive
            $table->integer('quantity');

            // Snapshots — self-contained audit record
            $table->integer('stock_before');
            $table->integer('stock_after');

            $table->text('notes')->nullable();

            // Immutable log: only created_at, no updated_at
            $table->timestamp('created_at')->useCurrent();
        });

        // Add ENUM column for movement type
        DB::statement("
            ALTER TABLE stock_movements
            ADD COLUMN type stock_movement_type NOT NULL
        ");

        // CHECK constraints
        DB::statement("
            ALTER TABLE stock_movements
            ADD CONSTRAINT chk_stock_movements_quantity_positive
            CHECK (quantity > 0)
        ");

        DB::statement("
            ALTER TABLE stock_movements
            ADD CONSTRAINT chk_stock_movements_stock_before_non_negative
            CHECK (stock_before >= 0)
        ");

        DB::statement("
            ALTER TABLE stock_movements
            ADD CONSTRAINT chk_stock_movements_stock_after_non_negative
            CHECK (stock_after >= 0)
        ");

        // Enforce arithmetic consistency: stock_after must match stock_before ± quantity
        DB::statement("
            ALTER TABLE stock_movements
            ADD CONSTRAINT chk_stock_movements_arithmetic
            CHECK (
                (type = 'in'  AND stock_after = stock_before + quantity) OR
                (type = 'out' AND stock_after = stock_before - quantity)
            )
        ");

        // Indexes — support dashboard queries, product history, date-range reports
        DB::statement('CREATE INDEX idx_stock_movements_product_id  ON stock_movements(product_id)');
        DB::statement('CREATE INDEX idx_stock_movements_user_id      ON stock_movements(user_id)');
        DB::statement('CREATE INDEX idx_stock_movements_type         ON stock_movements(type)');
        DB::statement('CREATE INDEX idx_stock_movements_created_at   ON stock_movements(created_at DESC)');

        // Composite index: product history ordered by date (most common query pattern)
        DB::statement('
            CREATE INDEX idx_stock_movements_product_created
            ON stock_movements(product_id, created_at DESC)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        DB::statement('DROP TYPE IF EXISTS stock_movement_type');
    }
};