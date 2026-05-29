<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE service_order_status AS ENUM ('pending', 'in_progress', 'done', 'cancelled')");

        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->unique();          // Contoh: SRV-20260528-001

            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();

            // Ambil dari customer tapi bisa beda (kendaraan titipan, dll)
            $table->string('vehicle_plate', 20);
            $table->string('vehicle_type', 100)->nullable();

            $table->text('complaint');                         // Keluhan pelanggan
            $table->text('diagnosis')->nullable();             // Diagnosa mekanik
            $table->text('notes')->nullable();                 // Catatan tambahan

            $table->decimal('service_fee', 12, 2)->default(0); // Biaya jasa mekanik
            $table->decimal('discount', 12, 2)->default(0);

            // Siapa yang terima & kerjakan
            $table->foreignId('handled_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE service_orders
            ADD COLUMN status service_order_status NOT NULL DEFAULT 'pending'
        ");

        DB::statement("
            ALTER TABLE service_orders
            ADD CONSTRAINT chk_service_orders_service_fee_non_negative
            CHECK (service_fee >= 0)
        ");

        DB::statement("
            ALTER TABLE service_orders
            ADD CONSTRAINT chk_service_orders_discount_non_negative
            CHECK (discount >= 0)
        ");

        DB::statement("
            ALTER TABLE service_orders
            ADD CONSTRAINT chk_service_orders_discount_not_exceed_fee
            CHECK (discount <= service_fee)
        ");

        DB::statement("
            ALTER TABLE service_orders
            ADD CONSTRAINT chk_service_orders_finished_at_after_created
            CHECK (finished_at IS NULL OR finished_at >= created_at)
        ");

        DB::statement('CREATE INDEX idx_service_orders_customer_id  ON service_orders(customer_id)');
        DB::statement('CREATE INDEX idx_service_orders_status        ON service_orders(status)');
        DB::statement('CREATE INDEX idx_service_orders_handled_by    ON service_orders(handled_by)');
        DB::statement('CREATE INDEX idx_service_orders_created_at    ON service_orders(created_at DESC)');
        DB::statement('CREATE INDEX idx_service_orders_vehicle_plate ON service_orders(vehicle_plate)');

        // Composite: laporan harian status
        DB::statement('
            CREATE INDEX idx_service_orders_status_created
            ON service_orders(status, created_at DESC)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
        DB::statement('DROP TYPE IF EXISTS service_order_status');
    }
};
