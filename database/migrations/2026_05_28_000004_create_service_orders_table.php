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
                    WHERE typname = 'service_order_status'
                ) THEN
                    CREATE TYPE service_order_status AS ENUM (
                        'pending',
                        'in_progress',
                        'done',
                        'cancelled'
                    );
                END IF;
            END
            $$;
        ");

        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_no', 50)->unique();

            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->restrictOnDelete()
                  ->restrictOnUpdate();

            $table->string('vehicle_plate', 20);
            $table->string('vehicle_type', 100)->nullable();

            $table->text('complaint');
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();

            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);

            $table->foreignId('handled_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });

        // Add ENUM column
        DB::statement("
            ALTER TABLE service_orders
            ADD COLUMN status service_order_status NOT NULL DEFAULT 'pending'
        ");

        // Constraints
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
            CHECK (
                finished_at IS NULL
                OR finished_at >= created_at
            )
        ");

        // Indexes
        DB::statement('CREATE INDEX idx_service_orders_customer_id ON service_orders(customer_id)');
        DB::statement('CREATE INDEX idx_service_orders_status ON service_orders(status)');
        DB::statement('CREATE INDEX idx_service_orders_handled_by ON service_orders(handled_by)');
        DB::statement('CREATE INDEX idx_service_orders_created_at ON service_orders(created_at DESC)');
        DB::statement('CREATE INDEX idx_service_orders_vehicle_plate ON service_orders(vehicle_plate)');

        DB::statement("
            CREATE INDEX idx_service_orders_status_created
            ON service_orders(status, created_at DESC)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');

        DB::statement('DROP TYPE IF EXISTS service_order_status');
    }
};