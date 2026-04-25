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
        DB::statement('TRUNCATE products, categories, suppliers, users, stock_movements RESTART IDENTITY CASCADE');

        // DB::table('users')->truncate();
        // DB::table('products')->truncate();
        // DB::table('categories')->truncate();
        // DB::table('suppliers')->truncate();
        
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
       $supplierIds = [];

        for ($i = 1; $i <= 10; $i++) {
            $supplierIds[] = DB::table('suppliers')->insertGetId([
                'name'       => "Supplier {$i}",
                'email'      => "supplier{$i}@mail.test",
                'phone'      => '+62 8' . rand(1000000000, 9999999999),
                'address'    => "Jl. Supplier No. {$i}",
                'city'       => ['Jakarta', 'Bandung', 'Surabaya', 'Semarang'][array_rand([0,1,2,3])],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Sample products ───────────────────────────────────────────────────
        $dbCategories = DB::table('categories')->get();
        $supplierIds = DB::table('suppliers')->pluck('id')->toArray();
        foreach ($dbCategories as $category) {
            for ($i = 1; $i <= 3; $i++) {

                DB::table('products')->insert([
                    'name'         => $category->name . " Product " . $i,
                    'sku' => strtoupper(substr($category->slug, 0, 3))
                            . '-' . $category->id
                            . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'description'  => "Sample product {$i} for {$category->name}",
                    'category_id'  => $category->id,
                    'supplier_id'  => $supplierIds[array_rand($supplierIds)],
                    'price'        => rand(50000, 5000000),
                    'stock'        => rand(5, 50),
                    'min_stock'    => rand(1, 5),
                    'is_active'    => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

            }
        }
        // ── Sample stock movements ───────────────────────────────────────
        $products = DB::table('products')->get();
        $userIds  = DB::table('users')->pluck('id')->toArray();

        foreach ($products as $product) {

            $currentStock = $product->stock;

            for ($i = 0; $i < 7; $i++) { // ~ total 100 data (15 produk x 7)
                
                $isIn = rand(0, 1); // random in / out
                $qty  = rand(1, 10);

                // Kalau OUT tapi stok gak cukup → paksa jadi IN
                if (!$isIn && $currentStock < $qty) {
                    $isIn = true;
                }

                $type = $isIn ? 'in' : 'out';

                $stockBefore = $currentStock;

                if ($type === 'in') {
                    $currentStock += $qty;
                } else {
                    $currentStock -= $qty;
                }

                $stockAfter = $currentStock;

                DB::table('stock_movements')->insert([
                    'product_id'   => $product->id,
                    'user_id'      => $userIds[array_rand($userIds)],
                    'type'         => $type,
                    'quantity'     => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'notes'        => $type === 'in'
                                        ? 'Restock barang'
                                        : 'Pengeluaran barang',
                    'created_at'   => now()->subDays(rand(0, 30)),
                ]);
            }

            // Update stock terakhir ke tabel products
            DB::table('products')
                ->where('id', $product->id)
                ->update(['stock' => $currentStock]);
        }
    }
    
}