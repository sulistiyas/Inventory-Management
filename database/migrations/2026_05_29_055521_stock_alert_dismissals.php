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
        // Tabel ini menyimpan kapan terakhir kali user "dismiss" notifikasi low stock
        // Sehingga alert tidak muncul terus-menerus setelah dibaca
        Schema::create('stock_alert_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->timestamp('dismissed_at')->useCurrent();
        });

        DB::statement('CREATE INDEX idx_sad_user_product ON stock_alert_dismissals(user_id, product_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alert_dismissals');
    }
};