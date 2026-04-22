<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the role ENUM type in PostgreSQL first
        DB::statement("CREATE TYPE user_role AS ENUM ('admin', 'staff')");

        Schema::create('users', function (Blueprint $table) {
            $table->id();                                           // BIGINT, PK, auto-increment
            $table->string('name', 100);                           // NOT NULL
            $table->string('email', 150)->unique();                // NOT NULL, UNIQUE
            $table->string('password', 255);                       // NOT NULL (bcrypt hash)
            $table->boolean('is_active')->default(true);           // NOT NULL, DEFAULT true
            $table->timestamps();                                  // created_at, updated_at
        });

        // Add the ENUM column manually (Laravel Blueprint doesn't support PG native ENUMs)
        DB::statement("
            ALTER TABLE users
            ADD COLUMN role user_role NOT NULL DEFAULT 'staff'
        ");

        // Add CHECK constraints
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT chk_users_email_format
            CHECK (email ~* '^[A-Za-z0-9._%+\\-]+@[A-Za-z0-9.\\-]+\\.[A-Za-z]{2,}$')
        ");

        // Index for frequent lookups
        DB::statement('CREATE INDEX idx_users_email    ON users(email)');
        DB::statement('CREATE INDEX idx_users_role     ON users(role)');
        DB::statement('CREATE INDEX idx_users_is_active ON users(is_active)');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        DB::statement('DROP TYPE IF EXISTS user_role');
    }
};