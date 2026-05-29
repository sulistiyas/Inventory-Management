<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 20)->nullable();
            $table->string('vehicle_plate', 20)->nullable();   // Nomor polisi
            $table->string('vehicle_type', 100)->nullable();   // Contoh: Honda Beat 2019
            $table->text('address')->nullable();
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE customers
            ADD CONSTRAINT chk_customers_name_not_empty
            CHECK (TRIM(name) <> '')
        ");

        DB::statement('CREATE INDEX idx_customers_phone         ON customers(phone)');
        DB::statement('CREATE INDEX idx_customers_vehicle_plate ON customers(vehicle_plate)');

        // Partial index untuk pencarian pelanggan bermotor (paling sering dicari)
        DB::statement("
            CREATE INDEX idx_customers_plate_not_null
            ON customers(vehicle_plate)
            WHERE vehicle_plate IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
