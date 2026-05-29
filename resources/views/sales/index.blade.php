@extends('layouts.app')

@section('title', 'Kasir POS')

@section('content')

{{-- Pass paymentMethods dari controller ke Alpine via JSON --}}
<div
    x-data="posKasir()"
    x-init="
        paymentMethods = {{ $paymentMethods->toJson() }};
        init();
    "
    @keydown.escape.window="closePayment(); closeDetail(); closeReceipt()"
>

<div style="display:grid;grid-template-columns:1fr 400px;gap:1.25rem;height:calc(100vh - 140px);">

    {{-- ================================================================
         KOLOM KIRI: Produk + Riwayat
    ================================================================ --}}
    <div style="display:flex;flex-direction:column;gap:1rem;overflow:hidden;">

        {{-- ── Tab header --}}
        <div style="display:flex;gap:0;border-bottom:2px solid var(--border);">
            <button
                @click="$refs.tab.value='products'"
                :style="$refs.tab?.value !== 'history' ? 'border-bottom:2px solid var(--primary);color:var(--primary);margin-bottom:-2px;' : ''"
                style="padding:8px 20px;font-size:.875rem;font-weight:600;background:none;border:none;cursor:pointer;">
                Produk
            </button>
            <button
                @click="$refs.tab.value='history'"
                :style="$refs.tab?.value === 'history' ? 'border-bottom:2px solid var(--primary);color:var(--primary);margin-bottom:-2px;' : ''"
                style="padding:8px 20px;font-size:.875rem;font-weight:600;background:none;border:none;cursor:pointer;">
                Riwayat Transaksi
            </button>
            <input type="hidden" x-ref="tab" value="products">
        </div>

        {{-- ── Tab: Produk --}}
        <template x-if="!$refs.tab || $refs.tab.value !== 'history'">
        <div style="flex:1;overflow:hidden;display:flex;flex-direction:column;gap:.75rem;">

            {{-- Search produk --}}
            <div style="position:relative;">
                <input
                    type="text"
                    x-model="productSearch"
                    @input="searchProducts()"
                    placeholder="Cari nama atau SKU produk..."
                    style="width:100%;padding:9px 14px;border:1px solid var(--border);border-radius:8px;font-size:.875rem;">
            </div>

            {{-- Grid produk --}}
            <div style="flex:1;overflow-y:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;align-content:start;">
                <template x-for="p in filteredProducts" :key="p.id">
                    <button
                        @click="addToCart(p)"
                        :disabled="p.stock <= 0"
                        :style="p.stock <= 0 ? 'opacity:.5;cursor:not-allowed;' : 'cursor:pointer;'"
                        style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:12px;text-align:left;transition:.15s;"
                        @mouseover="if($el.style.cursor!=='not-allowed') $el.style.borderColor='var(--primary)'"
                        @mouseleave="$el.style.borderColor='var(--border)'">
                        <div style="font-weight:600;font-size:.875rem;line-height:1.3;margin-bottom:4px;" x-text="p.name"></div>
                        <div style="font-size:.75rem;color:var(--text-muted);font-family:var(--font-mono);margin-bottom:6px;" x-text="p.sku"></div>
                        <div style="font-weight:700;font-size:.9375rem;font-family:var(--font-mono);" x-text="'Rp ' + Number(p.price).toLocaleString('id-ID')"></div>
                        <div style="font-size:.75rem;margin-top:4px;"
                             :style="p.stock <= 0 ? 'color:var(--danger,#dc2626)' : (p.stock <= 5 ? 'color:var(--warning,#d97706)' : 'color:var(--success,#16a34a)')"
                             x-text="'Stok: ' + p.stock"></div>
                    </button>
                </template>

                <template x-if="filteredProducts.length === 0">
                    <div style="grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-muted);font-size:.875rem;">
                        Produk tidak ditemukan
                    </div>
                </template>
            </div>
        </div>
        </template>

        {{-- ── Tab: Riwayat --}}
        <template x-if="$refs.tab?.value === 'history'">
        <div style="flex:1;overflow:hidden;"
             x-data="datatable({ apiEndpoint: '{{ route('sales.list') }}', perPage:15, filters:{status:'paid'} })"
             x-init="init()">
            <div class="datatable-card" style="height:100%;display:flex;flex-direction:column;">
                <div style="padding:.75rem 1rem;border-bottom:1px solid var(--border);display:flex;gap:.5rem;">
                    <input type="text" x-model="search" placeholder="Cari invoice..."
                           style="flex:1;padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
                    <input type="date" x-model="filters.date_from" @change="page=1;fetchData()"
                           style="padding:6px 8px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
                </div>
                <div style="flex:1;overflow-y:auto;">
                    <table class="datatable-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Kasir</th>
                                <th>Waktu</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="loading">
                                <tr><td colspan="6" class="datatable-loading">Memuat...</td></tr>
                            </template>
                            <template x-if="!loading && data.length === 0">
                                <tr><td colspan="6" class="datatable-empty">Belum ada transaksi</td></tr>
                            </template>
                            <template x-for="row in data" :key="row.id">
                                <tr>
                                    <td style="font-family:var(--font-mono);font-size:.8rem;" x-text="row.invoice_no"></td>
                                    <td style="font-size:.8125rem;" x-text="row.customer"></td>
                                    <td style="font-family:var(--font-mono);font-weight:600;font-size:.8125rem;" x-text="'Rp '+Number(row.grand_total).toLocaleString('id-ID')"></td>
                                    <td style="font-size:.8125rem;" x-text="row.cashier"></td>
                                    <td style="font-size:.75rem;color:var(--text-muted);" x-text="row.sold_at"></td>
                                    <td>
                                        <button class="dt-btn dt-btn-edit" @click="openDetail(row.id)">Detail</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <template x-if="lastPage > 1">
                    <div class="datatable-pagination" style="padding:.5rem;">
                        <button @click="changePage(page-1)" :disabled="page<=1">‹</button>
                        <template x-for="p in lastPage" :key="p">
                            <button :class="{'active':p===page}" @click="changePage(p)" x-text="p"></button>
                        </template>
                        <button @click="changePage(page+1)" :disabled="page>=lastPage">›</button>
                    </div>
                </template>
            </div>
        </div>
        </template>

    </div>{{-- kolom kiri --}}


    {{-- ================================================================
         KOLOM KANAN: Keranjang + Pelanggan
    ================================================================ --}}
    <div style="display:flex;flex-direction:column;gap:.75rem;background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:1rem;overflow:hidden;">

        {{-- Judul --}}
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:1rem;font-weight:700;margin:0;">Keranjang</h3>
            <button @click="clearCart()" style="font-size:.75rem;color:var(--danger,#dc2626);background:none;border:none;cursor:pointer;"
                    :style="cart.length ? '' : 'opacity:.3;cursor:default;'"
                    :disabled="!cart.length">
                Kosongkan
            </button>
        </div>

        {{-- Pelanggan --}}
        <div style="position:relative;">
            <input type="text" x-model="customerSearch"
                   @input="filterCustomers()"
                   placeholder="Pelanggan (opsional)..."
                   style="width:100%;padding:7px 12px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
            <template x-if="selectedCustomer">
                <button @click="clearCustomer()"
                        style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;">✕</button>
            </template>
            <template x-if="filteredCustomers.length && customerSearch.length > 0 && !selectedCustomer">
                <div style="position:absolute;z-index:50;top:100%;left:0;right:0;background:var(--bg);border:1px solid var(--border);border-radius:6px;max-height:150px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);">
                    <template x-for="c in filteredCustomers.slice(0,6)" :key="c.id">
                        <div style="padding:7px 12px;cursor:pointer;font-size:.8125rem;"
                             @mousedown.prevent="selectCustomer(c)"
                             @mouseover="$el.style.background='var(--bg-subtle)'"
                             @mouseleave="$el.style.background=''">
                            <div style="font-weight:500;" x-text="c.name"></div>
                            <div style="font-size:.75rem;color:var(--text-muted);" x-text="(c.vehicle_plate ?? '') + ' · ' + (c.phone ?? '')"></div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Item keranjang --}}
        <div style="flex:1;overflow-y:auto;min-height:0;">

            <template x-if="cart.length === 0">
                <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.875rem;">
                    <div style="font-size:2rem;margin-bottom:.5rem;">🛒</div>
                    Keranjang kosong.<br>Klik produk untuk menambahkan.
                </div>
            </template>

            <template x-for="(item, index) in cart" :key="item.product_id">
                <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem 0;border-bottom:1px solid var(--border-light,#f3f4f6);">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:500;font-size:.8125rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="item.product_name"></div>
                        <div style="font-size:.75rem;font-family:var(--font-mono);" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:4px;">
                        <button @click="decreaseQty(index)"
                                style="width:24px;height:24px;border-radius:4px;border:1px solid var(--border);background:var(--bg-subtle);cursor:pointer;font-size:.875rem;display:flex;align-items:center;justify-content:center;">−</button>
                        <span style="width:28px;text-align:center;font-size:.875rem;font-weight:600;" x-text="item.qty"></span>
                        <button @click="increaseQty(index)"
                                style="width:24px;height:24px;border-radius:4px;border:1px solid var(--border);background:var(--bg-subtle);cursor:pointer;font-size:.875rem;display:flex;align-items:center;justify-content:center;">+</button>
                    </div>
                    <div style="width:80px;text-align:right;font-size:.8125rem;font-family:var(--font-mono);font-weight:600;" x-text="'Rp '+Number(item.qty*item.price).toLocaleString('id-ID')"></div>
                    <button @click="removeFromCart(index)" style="color:var(--danger,#dc2626);background:none;border:none;cursor:pointer;font-size:1rem;padding:0 4px;">✕</button>
                </div>
            </template>
        </div>

        {{-- Diskon & Tax --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.8125rem;">
            <div>
                <label style="display:block;margin-bottom:3px;color:var(--text-muted);">Diskon (Rp)</label>
                <input type="number" x-model="discount" min="0"
                       style="width:100%;padding:6px 8px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
            </div>
            <div>
                <label style="display:block;margin-bottom:3px;color:var(--text-muted);">Pajak (Rp)</label>
                <input type="number" x-model="tax" min="0"
                       style="width:100%;padding:6px 8px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
            </div>
        </div>

        {{-- Summary --}}
        <div style="background:var(--bg-subtle);border-radius:8px;padding:10px 14px;font-size:.8125rem;">
            <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                <span style="color:var(--text-muted);">Subtotal</span>
                <span style="font-family:var(--font-mono);" x-text="'Rp ' + Number(subtotal).toLocaleString('id-ID')"></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                <span style="color:var(--text-muted);">Diskon</span>
                <span style="font-family:var(--font-mono);color:var(--success,#16a34a);" x-text="'- Rp ' + Number(discount||0).toLocaleString('id-ID')"></span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--text-muted);">Pajak</span>
                <span style="font-family:var(--font-mono);" x-text="'+ Rp ' + Number(tax||0).toLocaleString('id-ID')"></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1rem;margin-top:6px;padding-top:6px;border-top:1px solid var(--border);">
                <span>Total</span>
                <span style="font-family:var(--font-mono);" x-text="'Rp ' + Number(grandTotal).toLocaleString('id-ID')"></span>
            </div>
        </div>

        {{-- Tombol bayar --}}
        <button
            @click="openPayment()"
            :disabled="!cart.length"
            style="width:100%;padding:12px;border-radius:8px;border:none;background:var(--primary,#2563eb);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;transition:.15s;"
            :style="!cart.length ? 'opacity:.5;cursor:not-allowed;' : ''">
            💳 Bayar
        </button>

    </div>{{-- kolom kanan --}}

</div>{{-- grid --}}


{{-- ================================================================
     MODAL: Pembayaran
================================================================ --}}
<template x-if="paymentOpen">
    <div class="modal-backdrop" @click.self="closePayment()">
        <div class="modal-box" style="max-width:440px;" @click.stop>

            <div class="modal-header">
                <h3 class="modal-title">Pembayaran</h3>
                <button class="modal-close-btn" @click="closePayment()" type="button"></button>
            </div>

            <div class="modal-body">

                <template x-if="globalError">
                    <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:6px;font-size:.875rem;margin-bottom:1rem;" x-text="globalError"></div>
                </template>

                {{-- Ringkasan --}}
                <div style="background:var(--bg-subtle);border-radius:8px;padding:12px 14px;margin-bottom:1rem;font-size:.875rem;">
                    <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.125rem;">
                        <span>Total Tagihan</span>
                        <span style="font-family:var(--font-mono);" x-text="'Rp ' + Number(grandTotal).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                {{-- Metode pembayaran --}}
                <div style="margin-bottom:1rem;">
                    <label style="font-size:.875rem;font-weight:600;display:block;margin-bottom:.5rem;">Metode Pembayaran</label>

                    <template x-for="(p, idx) in payments" :key="idx">
                        <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:.5rem;margin-bottom:.5rem;align-items:center;">
                            <select x-model="p.payment_method_id"
                                    style="padding:7px 10px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
                                <option value="">— Pilih —</option>
                                <template x-for="m in paymentMethods" :key="m.id">
                                    <option :value="m.id" x-text="m.name"></option>
                                </template>
                            </select>
                            <input type="number" x-model.number="p.amount" min="0"
                                   style="padding:7px 10px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;"
                                   placeholder="Nominal">
                            <button @click="removePaymentRow(idx)"
                                    :disabled="payments.length <= 1"
                                    style="color:var(--danger,#dc2626);background:none;border:none;cursor:pointer;font-size:1.125rem;"
                                    :style="payments.length<=1 ? 'opacity:.3;cursor:default;' : ''">✕</button>
                        </div>
                    </template>

                    <button @click="addPaymentRow()"
                            style="font-size:.8125rem;color:var(--primary);background:none;border:none;cursor:pointer;padding:0;">
                        + Tambah metode bayar
                    </button>
                </div>

                {{-- Kembalian --}}
                <div style="display:flex;justify-content:space-between;font-size:.875rem;margin-bottom:.25rem;">
                    <span style="color:var(--text-muted);">Total Dibayar</span>
                    <span style="font-family:var(--font-mono);" x-text="'Rp ' + Number(totalPaid).toLocaleString('id-ID')"></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:700;"
                     :style="change > 0 ? 'color:var(--success,#16a34a)' : ''">
                    <span>Kembalian</span>
                    <span style="font-family:var(--font-mono);" x-text="'Rp ' + Number(change).toLocaleString('id-ID')"></span>
                </div>

                <template x-if="!isPaymentSufficient && totalPaid > 0">
                    <div style="color:var(--danger,#dc2626);font-size:.8125rem;margin-top:.5rem;">
                        Kurang: <span style="font-family:var(--font-mono);font-weight:600;" x-text="'Rp ' + Number(grandTotal - totalPaid).toLocaleString('id-ID')"></span>
                    </div>
                </template>

            </div>

            <div class="modal-footer">
                <button type="button" class="modal-btn-cancel" @click="closePayment()" :disabled="isSubmitting">Batal</button>
                <button type="button" class="modal-btn-submit"
                        @click="submitSale()"
                        :disabled="isSubmitting || !isPaymentSufficient">
                    <template x-if="isSubmitting"><span>Memproses...</span></template>
                    <template x-if="!isSubmitting"><span>✓ Konfirmasi Bayar</span></template>
                </button>
            </div>

        </div>
    </div>
</template>


{{-- ================================================================
     MODAL: Struk (Receipt)
================================================================ --}}
<template x-if="receiptOpen">
    <div class="modal-backdrop" @click.self="closeReceipt()">
        <div class="modal-box" style="max-width:360px;" @click.stop>

            <div class="modal-header">
                <h3 class="modal-title">Transaksi Berhasil ✓</h3>
                <button class="modal-close-btn" @click="closeReceipt()" type="button"></button>
            </div>

            <div class="modal-body" id="receipt-print-area">
                <template x-if="receiptData">
                    <div style="font-size:.8125rem;font-family:var(--font-mono);">

                        <div style="text-align:center;margin-bottom:.75rem;">
                            <div style="font-weight:700;font-size:.9375rem;">STRUK PEMBAYARAN</div>
                            <div style="color:var(--text-muted);" x-text="receiptData.invoice_no"></div>
                            <div style="color:var(--text-muted);font-size:.75rem;" x-text="receiptData.sold_at"></div>
                        </div>

                        <template x-if="receiptData.customer">
                            <div style="margin-bottom:.5rem;padding-bottom:.5rem;border-bottom:1px dashed var(--border);">
                                <span style="color:var(--text-muted);">Pelanggan: </span>
                                <span x-text="receiptData.customer?.name"></span>
                            </div>
                        </template>

                        <div style="margin-bottom:.5rem;padding-bottom:.5rem;border-bottom:1px dashed var(--border);">
                            <template x-for="item in receiptData.items" :key="item.product_id">
                                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                                    <span style="flex:1;" x-text="item.product_name + ' x' + item.qty"></span>
                                    <span x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                        </div>

                        <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><span x-text="'Rp ' + Number(receiptData.subtotal).toLocaleString('id-ID')"></span></div>
                        <template x-if="receiptData.discount > 0">
                            <div style="display:flex;justify-content:space-between;"><span>Diskon</span><span x-text="'- Rp ' + Number(receiptData.discount).toLocaleString('id-ID')"></span></div>
                        </template>
                        <template x-if="receiptData.tax > 0">
                            <div style="display:flex;justify-content:space-between;"><span>Pajak</span><span x-text="'+ Rp ' + Number(receiptData.tax).toLocaleString('id-ID')"></span></div>
                        </template>
                        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:.9375rem;margin-top:4px;padding-top:4px;border-top:1px dashed var(--border);">
                            <span>TOTAL</span><span x-text="'Rp ' + Number(receiptData.grand_total).toLocaleString('id-ID')"></span>
                        </div>

                        <div style="margin-top:.5rem;padding-top:.5rem;border-top:1px dashed var(--border);">
                            <template x-for="p in receiptData.payments" :key="p.method">
                                <div style="display:flex;justify-content:space-between;">
                                    <span x-text="p.method"></span>
                                    <span x-text="'Rp ' + Number(p.amount).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                            <template x-if="receiptData.payments?.[0]?.change_amount > 0">
                                <div style="display:flex;justify-content:space-between;margin-top:3px;">
                                    <span>Kembalian</span>
                                    <span x-text="'Rp ' + Number(receiptData.payments[0].change_amount).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                        </div>

                        <div style="text-align:center;margin-top:.75rem;color:var(--text-muted);font-size:.75rem;">Terima kasih!</div>
                    </div>
                </template>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-btn-cancel" @click="closeReceipt()">Tutup</button>
                <button type="button" class="modal-btn-submit" @click="printReceipt()">🖨 Print Struk</button>
            </div>

        </div>
    </div>
</template>


{{-- ================================================================
     MODAL: Detail Transaksi
================================================================ --}}
<template x-if="detailOpen">
    <div class="modal-backdrop" @click.self="closeDetail()">
        <div class="modal-box" style="max-width:500px;" @click.stop>
            <div class="modal-header">
                <h3 class="modal-title">Detail Transaksi</h3>
                <button class="modal-close-btn" @click="closeDetail()" type="button"></button>
            </div>
            <div class="modal-body">
                <template x-if="detailLoading">
                    <div style="text-align:center;padding:2rem;color:var(--text-muted);">Memuat...</div>
                </template>
                <template x-if="!detailLoading && detailData">
                    <div style="font-size:.875rem;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                            <div><span style="color:var(--text-muted);">Invoice</span><br><strong style="font-family:var(--font-mono);" x-text="detailData.invoice_no"></strong></div>
                            <div><span style="color:var(--text-muted);">Kasir</span><br><span x-text="detailData.cashier"></span></div>
                            <div><span style="color:var(--text-muted);">Pelanggan</span><br><span x-text="detailData.customer?.name ?? 'Umum'"></span></div>
                            <div><span style="color:var(--text-muted);">Waktu</span><br><span x-text="detailData.sold_at"></span></div>
                        </div>
                        <table style="width:100%;font-size:.8125rem;border-collapse:collapse;margin-bottom:.75rem;">
                            <thead><tr style="border-bottom:1px solid var(--border);">
                                <th style="text-align:left;padding:4px 0;">Item</th>
                                <th style="text-align:right;padding:4px 0;">Qty</th>
                                <th style="text-align:right;padding:4px 0;">Subtotal</th>
                            </tr></thead>
                            <tbody>
                                <template x-for="item in detailData.items" :key="item.product_id">
                                    <tr style="border-bottom:1px solid var(--border-light,#f3f4f6);">
                                        <td style="padding:4px 0;" x-text="item.product_name"></td>
                                        <td style="text-align:right;padding:4px 0;" x-text="item.qty"></td>
                                        <td style="text-align:right;padding:4px 0;font-family:var(--font-mono);" x-text="'Rp '+Number(item.subtotal).toLocaleString('id-ID')"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1rem;">
                            <span>Total</span>
                            <span style="font-family:var(--font-mono);" x-text="'Rp '+Number(detailData.grand_total).toLocaleString('id-ID')"></span>
                        </div>
                        <div style="margin-top:.75rem;">
                            <template x-for="p in detailData.payments" :key="p.method">
                                <div style="display:flex;justify-content:space-between;font-size:.8125rem;">
                                    <span style="color:var(--text-muted);" x-text="p.method"></span>
                                    <span style="font-family:var(--font-mono);" x-text="'Rp '+Number(p.amount).toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

</div>{{-- posKasir --}}

@push('styles')
<style>
@media print {
    body > *:not(#receipt-print-area) { display: none !important; }
    #receipt-print-area { display: block !important; }
}
</style>
@endpush

@endsection
