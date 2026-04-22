<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();        // NOT NULL, UNIQUE — "Electronics"
            $table->string('slug', 110)->unique();        // NOT NULL, UNIQUE — "electronics"
            $table->text('description')->nullable();      // Optional
            $table->timestamps();
        });

        // CHECK: name and slug must not be empty strings
        DB::statement("
            ALTER TABLE categories
            ADD CONSTRAINT chk_categories_name_not_empty
            CHECK (TRIM(name) <> '')
        ");

        DB::statement("
            ALTER TABLE categories
            ADD CONSTRAINT chk_categories_slug_format
            CHECK (slug ~* '^[a-z0-9]+(?:-[a-z0-9]+)*$')
        ");

        // Indexes
        DB::statement('CREATE INDEX idx_categories_slug ON categories(slug)');
        DB::statement('CREATE INDEX idx_categories_name ON categories(name)');
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};