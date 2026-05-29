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
                    WHERE typname = 'payment_method_type'
                ) THEN
                    CREATE TYPE payment_method_type AS ENUM (
                        'cash',
                        'transfer',
                        'ewallet',
                        'qris',
                        'other'
                    );
                END IF;
            END
            $$;
        ");

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add ENUM column
        DB::statement("
            ALTER TABLE payment_methods
            ADD COLUMN type payment_method_type NOT NULL DEFAULT 'cash'
        ");

        // Validation constraint
        DB::statement("
            ALTER TABLE payment_methods
            ADD CONSTRAINT chk_payment_methods_name_not_empty
            CHECK (TRIM(name) <> '')
        ");

        // Indexes
        DB::statement('CREATE INDEX idx_payment_methods_is_active ON payment_methods(is_active)');
        DB::statement('CREATE INDEX idx_payment_methods_type ON payment_methods(type)');

        // Seed default data
        DB::table('payment_methods')->insert([
            [
                'name' => 'Cash',
                'type' => 'cash',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transfer Bank',
                'type' => 'transfer',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'QRIS',
                'type' => 'qris',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');

        DB::statement('DROP TYPE IF EXISTS payment_method_type');
    }
};