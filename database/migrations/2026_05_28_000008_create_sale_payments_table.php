<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();

            $table->foreignId('payment_method_id')
                  ->constrained('payment_methods')
                  ->restrictOnDelete();

            $table->decimal('amount', 12, 2);         // Jumlah yang dibayarkan
            $table->decimal('change_amount', 12, 2)->default(0);  // Kembalian (relevan untuk cash)
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE sale_payments
            ADD CONSTRAINT chk_sale_payments_amount_positive
            CHECK (amount > 0)
        ");

        DB::statement("
            ALTER TABLE sale_payments
            ADD CONSTRAINT chk_sale_payments_change_non_negative
            CHECK (change_amount >= 0)
        ");

        DB::statement('CREATE INDEX idx_sale_payments_sale_id           ON sale_payments(sale_id)');
        DB::statement('CREATE INDEX idx_sale_payments_payment_method_id ON sale_payments(payment_method_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
