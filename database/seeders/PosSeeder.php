<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\ServiceOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = DB::table('users')->insertGetId([
            'name'       => 'Owner Bengkel',
            'email'      => 'owner@warehouse.test',
            'password'   => Hash::make('password'),
            'role'       => 'owner',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // DB::statement("UPDATE users SET role = 'owner' WHERE id = ?", [$ownerId]);

        // ── Payment methods (kalau belum ada dari migration seed) ──────────────
        $existingMethods = DB::table('payment_methods')->count();
        if ($existingMethods === 0) {
            DB::table('payment_methods')->insert([
                ['name' => 'Cash',          'type' => 'cash',     'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Transfer Bank', 'type' => 'transfer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'QRIS',          'type' => 'qris',     'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $paymentMethodIds = DB::table('payment_methods')->pluck('id', 'name');
        $cashMethodId     = $paymentMethodIds['Cash'] ?? 1;

        // ── Customers ──────────────────────────────────────────────────────────
        $customers = [
            ['name' => 'Budi Santoso',    'phone' => '081234567890', 'vehicle_plate' => 'B 1234 ABC', 'vehicle_type' => 'Honda Beat 2020'],
            ['name' => 'Siti Rahma',      'phone' => '082345678901', 'vehicle_plate' => 'D 5678 DEF', 'vehicle_type' => 'Yamaha Mio 2019'],
            ['name' => 'Ahmad Fauzi',     'phone' => '083456789012', 'vehicle_plate' => 'B 9012 GHI', 'vehicle_type' => 'Honda Vario 125 2021'],
            ['name' => 'Dewi Lestari',    'phone' => '084567890123', 'vehicle_plate' => 'F 3456 JKL', 'vehicle_type' => 'Yamaha NMAX 2022'],
            ['name' => 'Eko Prasetyo',    'phone' => '085678901234', 'vehicle_plate' => 'B 7890 MNO', 'vehicle_type' => 'Honda Supra X 125 2018'],
            ['name' => 'Fitri Handayani', 'phone' => '086789012345', 'vehicle_plate' => 'T 2345 PQR', 'vehicle_type' => 'Honda PCX 160 2023'],
            ['name' => 'Gilang Ramadan',  'phone' => '087890123456', 'vehicle_plate' => 'BE 6789 STU', 'vehicle_type' => 'Yamaha Aerox 155 2022'],
            ['name' => 'Heni Marlina',    'phone' => '088901234567', 'vehicle_plate' => 'B 1357 VWX', 'vehicle_type' => 'Honda Genio 2021'],
            ['name' => 'Irwan Kusuma',    'phone' => '089012345678', 'vehicle_plate' => 'D 2468 YZA', 'vehicle_type' => 'Kawasaki KLX 150 2020'],
            ['name' => 'Joko Widodo',     'phone' => '081123456789', 'vehicle_plate' => 'H 3579 BCD', 'vehicle_type' => 'Honda CBR 150R 2021'],
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->insert([
                'name'          => $customer['name'],
                'phone'         => $customer['phone'],
                'vehicle_plate' => $customer['vehicle_plate'],
                'vehicle_type'  => $customer['vehicle_type'],
                'address'       => 'Jl. Demo No. ' . rand(1, 100) . ', Jakarta',
                'created_at'    => now()->subDays(rand(10, 90)),
                'updated_at'    => now(),
            ]);
        }

        $customerIds = DB::table('customers')->pluck('id')->toArray();
        $productData = DB::table('products')->where('is_active', true)->get();
        $userIds     = DB::table('users')->pluck('id')->toArray();
        $adminId     = DB::table('users')->where('role', 'admin')->value('id');
        $staffId     = DB::table('users')->where('role', 'staff')->value('id');

        // ── Service Orders ─────────────────────────────────────────────────────
        $complaints = [
            'Mesin susah distarter pagi hari',
            'Rem depan kurang pakem',
            'Lampu depan mati',
            'Suara mesin kasar saat akselerasi',
            'Rantai kendur dan berbunyi',
            'Shock depan bocor oli',
            'Busi sering mati',
            'Speedometer tidak berfungsi',
            'AC / kipas radiator panas berlebih',
            'Karburator banjir',
        ];

        $statuses = ['pending', 'pending', 'in_progress', 'in_progress', 'done', 'done', 'done', 'cancelled'];

        for ($i = 0; $i < 15; $i++) {
            $customerId   = $customerIds[array_rand($customerIds)];
            $customer     = DB::table('customers')->where('id', $customerId)->first();
            $status       = $statuses[array_rand($statuses)];
            $serviceFee   = rand(3, 15) * 10000;
            $createdAt    = now()->subDays(rand(0, 14))->subHours(rand(0, 8));
            $seqNum       = str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $orderNo      = 'SRV-' . $createdAt->format('Ymd') . '-' . $seqNum;

            $orderId = DB::table('service_orders')->insertGetId([
                'order_no'      => $orderNo,
                'customer_id'   => $customerId,
                'vehicle_plate' => $customer->vehicle_plate,
                'vehicle_type'  => $customer->vehicle_type,
                'complaint'     => $complaints[array_rand($complaints)],
                'diagnosis'     => $status !== 'pending' ? 'Setelah diperiksa, ditemukan kerusakan pada ' . fake()->words(3, true) : null,
                'service_fee'   => $serviceFee,
                'discount'      => rand(0, 1) ? rand(1, 3) * 5000 : 0,
                'status'        => $status,
                'handled_by'    => $staffId ?? $adminId,
                'finished_at'   => $status === 'done' ? $createdAt->addHours(rand(1, 4)) : null,
                'created_at'    => $createdAt,
                'updated_at'    => now(),
            ]);

            // Tambah 1-3 spare part ke WO
            $usedProducts = $productData->random(rand(1, 3));
            foreach ($usedProducts as $product) {
                $qty = rand(1, 3);
                DB::table('service_order_items')->insert([
                    'service_order_id' => $orderId,
                    'product_id'       => $product->id,
                    'qty'              => $qty,
                    'price'            => $product->price,
                    'subtotal'         => $qty * $product->price,
                    'created_at'       => $createdAt,
                    'updated_at'       => now(),
                ]);

                // Kalau done, kurangi stok
                if ($status === 'done') {
                    $stockBefore = $product->stock;
                    $stockAfter  = max(0, $stockBefore - $qty);

                    DB::table('stock_movements')->insert([
                        'product_id'   => $product->id,
                        'user_id'      => $staffId ?? $adminId,
                        'type'         => 'out',
                        'quantity'     => $qty,
                        'stock_before' => $stockBefore,
                        'stock_after'  => $stockAfter,
                        'notes'        => "Dipakai untuk work order {$orderNo}",
                        'created_at'   => $createdAt->addHours(rand(1, 3)),
                        'updated_at'   => now(),
                    ]);

                    DB::table('products')
                        ->where('id', $product->id)
                        ->decrement('stock', $qty);

                    // Refresh data supaya stok akurat di loop berikutnya
                    $productData = DB::table('products')->where('is_active', true)->get();
                }
            }
        }

        // ── Sales (transaksi POS) ──────────────────────────────────────────────
        $invoiceCounter = 1;

        for ($day = 14; $day >= 0; $day--) {
            $txPerDay = rand(3, 8);

            for ($t = 0; $t < $txPerDay; $t++) {
                $soldAt     = now()->subDays($day)->setHour(rand(8, 20))->setMinute(rand(0, 59));
                $customerId = rand(0, 1) ? $customerIds[array_rand($customerIds)] : null;
                $cashierId  = $staffId ?? $adminId;
                $seqNum     = str_pad($invoiceCounter++, 4, '0', STR_PAD_LEFT);
                $invoiceNo  = 'INV-' . $soldAt->format('Ymd') . '-' . $seqNum;

                // Pilih 1-4 produk random
                $selectedProducts = $productData->random(rand(1, 4));
                $subtotal         = 0;
                $saleItemsData    = [];

                foreach ($selectedProducts as $product) {

                // Refresh stok terbaru
                $currentProduct = DB::table('products')
                    ->where('id', $product->id)
                    ->first();
                
                // Skip kalau stok habis
                if ($currentProduct->stock <= 0) {
                    continue;
                }

                

                // Qty tidak boleh melebihi stok
                $maxQty = min(3, $currentProduct->stock);

                $qty = rand(1, $maxQty);

                $lineTotal = $qty * $currentProduct->price;

                $subtotal += $lineTotal;

                $saleItemsData[] = [
                    'product_id' => $currentProduct->id,
                    'qty'        => $qty,
                    'price'      => $currentProduct->price,
                    'cost_price' => $currentProduct->cost_price ?? null,
                    'subtotal'   => $lineTotal,
                ];
            }

                if (empty($saleItemsData) || $subtotal <= 0) {
                    continue;
                }

                // $discount   = rand(0, 1) ? rand(1, 5) * 5000 : 0;
                // $grandTotal = $subtotal - $discount + $tax;
                
                $discountPercent = rand(0, 20); // 0-20%
                
                $discount = round($subtotal * ($discountPercent / 100));
                
                $tax        = 0;
                $grandTotal = $subtotal - $discount + $tax;

                // Buat Sale
                $saleId = DB::table('sales')->insertGetId([
                    'invoice_no'  => $invoiceNo,
                    'user_id'     => $cashierId,
                    'customer_id' => $customerId,
                    'subtotal'    => $subtotal,
                    'discount'    => $discount,
                    'tax'         => $tax,
                    'grand_total' => $grandTotal,
                    'status'      => 'paid',
                    'sold_at'     => $soldAt,
                    'created_at'  => $soldAt,
                    'updated_at'  => $soldAt,
                ]);

                // Insert sale items + kurangi stok
                foreach ($saleItemsData as $item) {
                    DB::table('sale_items')->insert(array_merge($item, [
                        'sale_id'    => $saleId,
                        'created_at' => $soldAt,
                        'updated_at' => $soldAt,
                    ]));

                    $currentProduct = DB::table('products')->where('id', $item['product_id'])->first();
                    $stockBefore    = $currentProduct->stock;
                    $stockAfter = $stockBefore - $item['qty'];

                    DB::table('stock_movements')->insert([
                        'product_id'   => $item['product_id'],
                        'user_id'      => $cashierId,
                        'type'         => 'out',
                        'quantity'     => $item['qty'],
                        'stock_before' => $stockBefore,
                        'stock_after'  => $stockAfter,
                        'notes'        => "Terjual via invoice {$invoiceNo}",
                        'created_at'   => $soldAt,
                        'updated_at'   => $soldAt,
                    ]);

                    DB::table('products')
                        ->where('id', $item['product_id'])
                        ->decrement('stock', $item['qty']);

                    $productData = DB::table('products')->where('is_active', true)->get();
                }

                // Pilih metode bayar (80% cash, 15% QRIS, 5% transfer)
                $rand = rand(1, 100);
                $paymentMethodId = $rand <= 80
                    ? ($paymentMethodIds['Cash']          ?? $cashMethodId)
                    : ($rand <= 95
                        ? ($paymentMethodIds['QRIS']       ?? $cashMethodId)
                        : ($paymentMethodIds['Transfer Bank'] ?? $cashMethodId));

                DB::table('sale_payments')->insert([
                    'sale_id'           => $saleId,
                    'payment_method_id' => $paymentMethodId,
                    'amount'            => $grandTotal + ($paymentMethodId === $cashMethodId ? rand(0, 5) * 1000 : 0),
                    'change_amount'     => $paymentMethodId === $cashMethodId ? rand(0, 5) * 1000 : 0,
                    'created_at'        => $soldAt,
                    'updated_at'        => $soldAt,
                ]);
            }
        }

        $this->command->info('✅ POS seeder selesai:');
        $this->command->info('   • 1 user owner (owner@warehouse.test / password)');
        $this->command->info('   • ' . count($customers) . ' customers');
        $this->command->info('   • 15 service orders (berbagai status)');
        $this->command->info('   • ~' . ($invoiceCounter - 1) . ' transaksi penjualan (14 hari terakhir)');
    }
}