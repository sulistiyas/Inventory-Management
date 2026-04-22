<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ────────────────────────────────────────────────────────
        $adminId = DB::table('users')->insertGetId([
            'name'       => 'Administrator',
            'email'      => 'admin@warehouse.test',
            'password'   => Hash::make('password'),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set role via raw SQL (native PG enum)
        DB::statement("UPDATE users SET role = 'admin' WHERE id = ?", [$adminId]);

        // ── Staff user ────────────────────────────────────────────────────────
        DB::table('users')->insert([
            'name'       => 'Staff User',
            'email'      => 'staff@warehouse.test',
            'password'   => Hash::make('password'),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Sample categories ─────────────────────────────────────────────────
        $categories = [
            ['name' => 'Electronics',      'slug' => 'electronics'],
            ['name' => 'Office Supplies',  'slug' => 'office-supplies'],
            ['name' => 'Furniture',        'slug' => 'furniture'],
            ['name' => 'Raw Materials',    'slug' => 'raw-materials'],
            ['name' => 'Packaging',        'slug' => 'packaging'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name'        => $cat['name'],
                'slug'        => $cat['slug'],
                'description' => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // ── Sample supplier ───────────────────────────────────────────────────
        DB::table('suppliers')->insert([
            'name'       => 'Acme Corp',
            'email'      => 'supply@acme.test',
            'phone'      => '+62 21 1234 5678',
            'address'    => 'Jl. Sudirman No. 1',
            'city'       => 'Jakarta',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}