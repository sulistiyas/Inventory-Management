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
        // Create the role ENUM type in PostgreSQL first
        DB::statement("CREATE TYPE user_role AS ENUM ('admin', 'staff')");
 
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                           // BIGINT, PK, auto-increment
            $table->string('name', 100);                           // NOT NULL
            $table->string('email', 150)->unique();                // NOT NULL, UNIQUE
            $table->string('password', 255);                       // NOT NULL (bcrypt hash)
            $table->boolean('is_active')->default(true);    
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
        DB::statement('DROP TYPE IF EXISTS user_role');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
