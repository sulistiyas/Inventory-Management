<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE sale_status AS ENUM ('draft', 'paid', 'cancelled')");

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 50)->unique();   // Contoh: INV-20260528-0001

            // Kasir yang input transaksi
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();

            // Pelanggan opsional (walk-in tidak wajib isi)
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();

            // Linked ke work order servis (opsional — bisa juga jual part saja)
            $table->foreignId('service_order_id')
                  ->nullable()
                  ->constrained('service_orders')
                  ->nullOnDelete();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);       // Simpan nominal, bukan persen
            $table->decimal('grand_total', 12, 2);

            $table->text('notes')->nullable();
            $table->timestamp('sold_at')->useCurrent();
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE sales
            ADD COLUMN status sale_status NOT NULL DEFAULT 'draft'
        ");

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
            CHECK (grand_total = subtotal - discount + tax)
        ");

        DB::statement('CREATE INDEX idx_sales_user_id           ON sales(user_id)');
        DB::statement('CREATE INDEX idx_sales_customer_id       ON sales(customer_id)');
        DB::statement('CREATE INDEX idx_sales_service_order_id  ON sales(service_order_id)');
        DB::statement('CREATE INDEX idx_sales_status            ON sales(status)');
        DB::statement('CREATE INDEX idx_sales_sold_at           ON sales(sold_at DESC)');

        // Composite: laporan harian omzet (query paling sering untuk dashboard owner)
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
