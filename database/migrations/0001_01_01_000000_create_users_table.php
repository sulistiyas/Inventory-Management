<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create ENUM type if not exists
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_type
                    WHERE typname = 'user_role'
                ) THEN
                    CREATE TYPE user_role AS ENUM ('admin', 'staff', 'owner');
                END IF;
            END
            $$;
        ");

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Add ENUM column
        DB::statement("
            ALTER TABLE users
            ADD COLUMN role user_role NOT NULL DEFAULT 'staff'
        ");

        // CHECK constraint
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT chk_users_email_format
            CHECK (
                email ~* '^[A-Za-z0-9._%+\\-]+@[A-Za-z0-9.\\-]+\\.[A-Za-z]{2,}$'
            )
        ");

        // Indexes
        DB::statement('CREATE INDEX idx_users_email ON users(email)');
        DB::statement('CREATE INDEX idx_users_role ON users(role)');
        DB::statement('CREATE INDEX idx_users_is_active ON users(is_active)');

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');

        DB::statement('DROP TYPE IF EXISTS user_role');
    }
};