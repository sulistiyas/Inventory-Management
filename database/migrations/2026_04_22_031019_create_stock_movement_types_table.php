<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create ENUM type if not exists
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_type
                    WHERE typname = 'stock_movement_type'
                ) THEN
                    CREATE TYPE stock_movement_type AS ENUM ('in', 'out', 'adjustment');
                END IF;
            END
            $$;
        ");

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->integer('quantity');

            $table->integer('stock_before');
            $table->integer('stock_after');

            $table->text('notes')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        // Add ENUM column
        DB::statement("
            ALTER TABLE stock_movements
            ADD COLUMN type stock_movement_type NOT NULL
        ");

        // Constraints
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

        DB::statement("
            ALTER TABLE stock_movements
            ADD CONSTRAINT chk_stock_movements_arithmetic
            CHECK (
                (type = 'in' AND stock_after = stock_before + quantity) OR
                (type = 'out' AND stock_after = stock_before - quantity) OR
                (type = 'adjustment')
            )
        ");

        // Indexes
        DB::statement('CREATE INDEX idx_stock_movements_product_id ON stock_movements(product_id)');
        DB::statement('CREATE INDEX idx_stock_movements_user_id ON stock_movements(user_id)');
        DB::statement('CREATE INDEX idx_stock_movements_type ON stock_movements(type)');
        DB::statement('CREATE INDEX idx_stock_movements_created_at ON stock_movements(created_at DESC)');

        DB::statement("
            CREATE INDEX idx_stock_movements_product_created
            ON stock_movements(product_id, created_at DESC)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');

        DB::statement('DROP TYPE IF EXISTS stock_movement_type');
    }
};