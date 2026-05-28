<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('TRUNCATE products, categories, suppliers, users, stock_movements RESTART IDENTITY CASCADE');

        // ── Admin user ────────────────────────────────────────────────────────
        $adminId = DB::table('users')->insertGetId([
            'name'       => 'Administrator',
            'email'      => 'admin@warehouse.test',
            'password'   => Hash::make('password'),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

        // ── Kategori sparepart motor ──────────────────────────────────────────
        $categories = [
            ['name' => 'Mesin & Transmisi',  'slug' => 'mesin-transmisi'],
            ['name' => 'Kelistrikan',         'slug' => 'kelistrikan'],
            ['name' => 'Rem & Kopling',       'slug' => 'rem-kopling'],
            ['name' => 'Suspensi & Kemudi',   'slug' => 'suspensi-kemudi'],
            ['name' => 'Body & Aksesoris',    'slug' => 'body-aksesoris'],
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

        // ── Supplier sparepart motor ──────────────────────────────────────────
        $suppliers = [
            ['name' => 'Astra Honda Motor Parts',   'city' => 'Jakarta'],
            ['name' => 'Yamaha Genuine Parts',       'city' => 'Jakarta'],
            ['name' => 'Suzuki Motor Parts',         'city' => 'Bandung'],
            ['name' => 'UD Maju Jaya Sparepart',     'city' => 'Surabaya'],
            ['name' => 'Toko Aneka Motor',           'city' => 'Semarang'],
            ['name' => 'CV Berkah Onderdil',         'city' => 'Bandung'],
            ['name' => 'PT Indoparts Distribusi',    'city' => 'Jakarta'],
            ['name' => 'UD Sentral Sparepart',       'city' => 'Yogyakarta'],
            ['name' => 'Toko Jaya Mandiri Motor',    'city' => 'Surabaya'],
            ['name' => 'CV Prima Onderdil',          'city' => 'Malang'],
        ];

        foreach ($suppliers as $i => $sup) {
            DB::table('suppliers')->insert([
                'name'       => $sup['name'],
                'email'      => 'supplier' . ($i + 1) . '@mail.test',
                'phone'      => '+62 8' . rand(100000000, 999999999),
                'address'    => 'Jl. Raya No. ' . (($i + 1) * 10),
                'city'       => $sup['city'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Produk sparepart per kategori ─────────────────────────────────────
        $productsByCategory = [
            'mesin-transmisi' => [
                ['name' => 'Kampas Kopling Honda Vario 125',  'price' => 45000],
                ['name' => 'Oli Mesin Yamalube 1L',           'price' => 35000],
                ['name' => 'Filter Oli Honda Beat',           'price' => 25000],
                ['name' => 'Piston Kit Mio M3',               'price' => 185000],
                ['name' => 'Gear Set Honda Supra X',          'price' => 95000],
                ['name' => 'V-Belt Yamaha NMAX',              'price' => 120000],
                ['name' => 'Roller CVT Vario 150',            'price' => 55000],
                ['name' => 'Gasket Top Set Beat ESP',         'price' => 75000],
                ['name' => 'Rantai 428 H Motor Bebek',        'price' => 65000],
                ['name' => 'Gigi Primer Honda Revo',          'price' => 88000],
            ],
            'kelistrikan' => [
                ['name' => 'Busi NGK CR7HSA',                 'price' => 28000],
                ['name' => 'Aki Yuasa YTZ5S',                 'price' => 235000],
                ['name' => 'Kiprok Regulator Vario 125',      'price' => 115000],
                ['name' => 'CDI Honda Supra X 125',           'price' => 320000],
                ['name' => 'Kabel Body Honda Beat',           'price' => 175000],
                ['name' => 'Lampu Depan LED Motor Matic',     'price' => 85000],
                ['name' => 'Spul / Stator Mio J',             'price' => 210000],
                ['name' => 'Switch Starter Honda Vario',      'price' => 45000],
                ['name' => 'Sensor TPS Yamaha NMAX',          'price' => 185000],
                ['name' => 'Flasher Sein Universal',          'price' => 22000],
            ],
            'rem-kopling' => [
                ['name' => 'Kampas Rem Depan Vario 150',      'price' => 55000],
                ['name' => 'Kampas Rem Belakang Beat',        'price' => 35000],
                ['name' => 'Minyak Rem Castrol DOT 4',        'price' => 32000],
                ['name' => 'Master Rem Depan Honda PCX',      'price' => 145000],
                ['name' => 'Cakram Depan Yamaha Aerox',       'price' => 385000],
                ['name' => 'Kaliper Rem Belakang Vario',      'price' => 265000],
                ['name' => 'Tuas Kopling Honda CB150R',       'price' => 68000],
                ['name' => 'Selang Rem Depan Ninja 250',      'price' => 95000],
                ['name' => 'Kampas Tromol Belakang Supra',    'price' => 28000],
                ['name' => 'Pegas Rem Tromol Universal',      'price' => 12000],
            ],
            'suspensi-kemudi' => [
                ['name' => 'Shock Depan Honda Vario 125',     'price' => 285000],
                ['name' => 'Shock Belakang Yamaha Mio',       'price' => 195000],
                ['name' => 'Bearing Roda Depan Universal',    'price' => 45000],
                ['name' => 'Stang Jepit Honda CBR 150',       'price' => 425000],
                ['name' => 'Ban Dalam 70/90-17',              'price' => 38000],
                ['name' => 'Ban Dalam 80/90-14 Matic',        'price' => 35000],
                ['name' => 'Seal Shock Depan Vario',          'price' => 55000],
                ['name' => 'Laher Setang / Steering Bearing', 'price' => 75000],
                ['name' => 'Grip / Handgrip Universal',       'price' => 22000],
                ['name' => 'Per Shock Belakang Honda Beat',   'price' => 85000],
            ],
            'body-aksesoris' => [
                ['name' => 'Cover Body Depan Vario 125',      'price' => 155000],
                ['name' => 'Spakbor Belakang Yamaha Mio',     'price' => 95000],
                ['name' => 'Kaca Spion Universal Bulat',      'price' => 45000],
                ['name' => 'Stiker Striping Honda Beat 2022', 'price' => 65000],
                ['name' => 'Jok Motor Honda Vario',           'price' => 245000],
                ['name' => 'Behel / Boncengan Belakang Mio',  'price' => 115000],
                ['name' => 'Knalpot Racing Honda Supra',      'price' => 385000],
                ['name' => 'Cover Mesin Honda Beat ESP',      'price' => 135000],
                ['name' => 'Box Motor Belakang 30L',          'price' => 275000],
                ['name' => 'Karet Pijakan / Footstep Bebek',  'price' => 28000],
            ],
        ];

        $dbCategories = DB::table('categories')->get()->keyBy('slug');
        $supplierIds  = DB::table('suppliers')->pluck('id')->toArray();

        foreach ($productsByCategory as $slug => $products) {
            $category = $dbCategories[$slug];
            foreach ($products as $i => $product) {
                $skuPrefix = strtoupper(implode('', array_filter(
                    explode('-', $slug),
                    fn($part) => strlen($part) > 2
                )));
                $skuPrefix = substr($skuPrefix, 0, 3);

                DB::table('products')->insert([
                    'name'        => $product['name'],
                    'sku'         => strtoupper($skuPrefix)
                                     . '-' . $category->id
                                     . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'description' => 'Sparepart motor: ' . $product['name'],
                    'category_id' => $category->id,
                    'supplier_id' => $supplierIds[array_rand($supplierIds)],
                    'price'       => $product['price'],
                    'stock'       => rand(5, 80),
                    'min_stock'   => rand(3, 10),
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // ── Sample stock movements ────────────────────────────────────────────
        $products = DB::table('products')->get();
        $userIds  = DB::table('users')->pluck('id')->toArray();

        $notes = [
            'in'  => ['Restock dari supplier', 'Pembelian onderdil baru', 'Retur dari mekanik', 'Pengiriman masuk'],
            'out' => ['Pemakaian servis motor', 'Pengeluaran untuk pelanggan', 'Digunakan mekanik', 'Penjualan eceran'],
        ];

        foreach ($products as $product) {
            $currentStock = $product->stock;

            for ($i = 0; $i < 7; $i++) {
                $isIn = rand(0, 1);
                $qty  = rand(1, 15);

                if (!$isIn && $currentStock < $qty) {
                    $isIn = true;
                }

                $type        = $isIn ? 'in' : 'out';
                $stockBefore = $currentStock;
                $currentStock += $type === 'in' ? $qty : -$qty;

                DB::table('stock_movements')->insert([
                    'product_id'   => $product->id,
                    'user_id'      => $userIds[array_rand($userIds)],
                    'type'         => $type,
                    'quantity'     => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $currentStock,
                    'notes'        => $notes[$type][array_rand($notes[$type])],
                    'created_at'   => now()->subDays(rand(0, 30)),
                ]);
            }

            DB::table('products')
                ->where('id', $product->id)
                ->update(['stock' => $currentStock]);
        }
    }
}