<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create ENUM if not exists
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_type
                    WHERE typname = 'sale_status'
                ) THEN
                    CREATE TYPE sale_status AS ENUM (
                        'draft',
                        'paid',
                        'cancelled'
                    );
                END IF;
            END
            $$;
        ");

        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_no', 50)->unique();

            // Cashier
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();

            // Optional customer
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();

            // Optional linked service order
            $table->foreignId('service_order_id')
                  ->nullable()
                  ->constrained('service_orders')
                  ->nullOnDelete();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);

            $table->text('notes')->nullable();

            $table->timestamp('sold_at')->useCurrent();

            $table->timestamps();
        });

        // Add ENUM column
        DB::statement("
            ALTER TABLE sales
            ADD COLUMN status sale_status NOT NULL DEFAULT 'draft'
        ");

        // Constraints
        DB::statement("
            ALTER TABLE sales
            ADD CONSTRAINT chk_sales_subtotal_non_negative
            CHECK (subtotal >= 0)
        ");

        DB::statement("
            ALTER TABLE sales
            ADD CONSTRAINT chk_sales_discount_non_negative
            CHECK (discount >= 0)
        ");

        DB::statement("
            ALTER TABLE sales
            ADD CONSTRAINT chk_sales_tax_non_negative
            CHECK (tax >= 0)
        ");

        DB::statement("
            ALTER TABLE sales
            ADD CONSTRAINT chk_sales_grand_total_non_negative
            CHECK (grand_total >= 0)
        ");

        DB::statement("
            ALTER TABLE sales
            ADD CONSTRAINT chk_sales_grand_total_correct
            CHECK (
                grand_total = subtotal - discount + tax
            )
        ");

        // Indexes
        DB::statement('CREATE INDEX idx_sales_user_id ON sales(user_id)');
        DB::statement('CREATE INDEX idx_sales_customer_id ON sales(customer_id)');
        DB::statement('CREATE INDEX idx_sales_service_order_id ON sales(service_order_id)');
        DB::statement('CREATE INDEX idx_sales_status ON sales(status)');
        DB::statement('CREATE INDEX idx_sales_sold_at ON sales(sold_at DESC)');

        // Partial index for dashboard revenue queries
        DB::statement("
            CREATE INDEX idx_sales_status_sold_at
            ON sales(status, sold_at DESC)
            WHERE status = 'paid'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');

        DB::statement('DROP TYPE IF EXISTS sale_status');
    }
};