<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);                      // NOT NULL
            $table->string('email', 150)->unique()->nullable(); // UNIQUE but nullable
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->boolean('is_active')->default(true);      // Soft-disable
            $table->timestamps();
        });

        // CHECK: name must not be empty
        DB::statement("
            ALTER TABLE suppliers
            ADD CONSTRAINT chk_suppliers_name_not_empty
            CHECK (TRIM(name) <> '')
        ");

        // CHECK: phone format — digits, spaces, dashes, plus allowed
        DB::statement("
            ALTER TABLE suppliers
            ADD CONSTRAINT chk_suppliers_phone_format
            CHECK (phone IS NULL OR phone ~* '^[+\\d\\s\\-()]{6,20}$')
        ");

        // Indexes
        DB::statement('CREATE INDEX idx_suppliers_name      ON suppliers(name)');
        DB::statement('CREATE INDEX idx_suppliers_city      ON suppliers(city)');
        DB::statement('CREATE INDEX idx_suppliers_is_active ON suppliers(is_active)');
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};