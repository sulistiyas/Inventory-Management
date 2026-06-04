@extends('layouts.app')

@section('title', 'Kasir POS')

@push('styles')
<style>
/* ── POS layout ────────────────────────────────────────────────── */
.pos-shell {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.25rem;
    height: calc(100vh - var(--navbar-h) - 2.5rem);
    overflow: hidden;
}

/* ── Kolom kiri ─────────────────────────────────────────────────── */
.pos-left {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    overflow: hidden;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

/* ── Tab bar ─────────────────────────────────────────────────────── */
.pos-tabs {
    display: flex;
    border-bottom: 1.5px solid var(--border);
    padding: 0 1.25rem;
    flex-shrink: 0;
}

.pos-tab {
    padding: 0.875rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-muted);
    border: none;
    background: none;
    cursor: pointer;
    border-bottom: 2.5px solid transparent;
    margin-bottom: -1.5px;
    transition: color var(--transition), border-color var(--transition);
    font-family: var(--font-body);
}

.pos-tab:hover { color: var(--text-secondary); }

.pos-tab.active {
    color: var(--accent);
    border-bottom-color: var(--accent);
}

/* ── Search produk ────────────────────────────────────────────── */
.pos-search-wrap {
    padding: 0 1.25rem;
    flex-shrink: 0;
}

.pos-search {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.5rem;
    border: 1.5px solid var(--border);
    border-radius: 999px;
    font-family: var(--font-body);
    font-size: 0.875rem;
    color: var(--text-primary);
    background: var(--bg-body);
    outline: none;
    transition: border-color var(--transition), box-shadow var(--transition);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 0.75rem center;
    background-size: 16px;
}

.pos-search:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-glow);
}

/* ── Grid produk ──────────────────────────────────────────────── */
.pos-products {
    flex: 1;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
    gap: 0.75rem;
    padding: 0 1.25rem 1.25rem;
    align-content: start;
}

.product-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 0.875rem;
    cursor: pointer;
    transition: border-color var(--transition), box-shadow var(--transition), transform 0.15s;
    text-align: left;
}

.product-card:hover:not(:disabled) {
    border-color: var(--accent);
    box-shadow: 0 4px 12px var(--accent-glow);
    transform: translateY(-1px);
}

.product-card:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.product-card-name {
    font-weight: 700;
    font-size: 0.875rem;
    color: var(--text-primary);
    line-height: 1.3;
    margin-bottom: 3px;
}

.product-card-sku {
    font-family: var(--font-mono);
    font-size: 0.73rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.product-card-price {
    font-family: var(--font-mono);
    font-weight: 700;
    font-size: 0.9375rem;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.product-card-stock {
    font-size: 0.73rem;
    font-weight: 600;
}

.stock-ok    { color: var(--success); }
.stock-low   { color: var(--warning); }
.stock-empty { color: var(--danger); }

/* ── History table (tab riwayat) ──────────────────────────── */
.pos-history {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.pos-history-toolbar {
    display: flex;
    gap: 0.5rem;
    padding: 0 1.25rem 0.75rem;
    flex-shrink: 0;
}

.pos-history-toolbar input {
    flex: 1;
    padding: 0.5rem 0.875rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: var(--font-body);
    font-size: 0.8125rem;
    color: var(--text-primary);
    outline: none;
    transition: border-color var(--transition);
}

.pos-history-toolbar input:focus { border-color: var(--accent); }

.pos-history-table-wrap {
    flex: 1;
    overflow-y: auto;
}

.pos-history-pagination {
    display: flex;
    gap: 4px;
    padding: 0.75rem 1.25rem;
    border-top: 1px solid var(--border);
    flex-shrink: 0;
}

.pos-history-pagination button {
    min-width: 30px;
    height: 30px;
    padding: 0 6px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: var(--bg-card);
    font-size: 0.8125rem;
    font-family: var(--font-body);
    cursor: pointer;
    color: var(--text-secondary);
    transition: background var(--transition), border-color var(--transition);
}

.pos-history-pagination button:hover:not(:disabled) {
    border-color: var(--accent);
    color: var(--accent);
}

.pos-history-pagination button.active {
    background: var(--accent);
    border-color: var(--accent);
    color: #451a03;
    font-weight: 700;
}

.pos-history-pagination button:disabled { opacity: 0.35; cursor: not-allowed; }

/* ── Kolom kanan: Keranjang ────────────────────────────────── */
.pos-cart {
    display: flex;
    flex-direction: column;
    gap: 0;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.pos-cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}

.pos-cart-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
}

.pos-clear-btn {
    font-size: 0.75rem;
    color: var(--danger);
    background: none;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    transition: background var(--transition);
}

.pos-clear-btn:hover { background: var(--danger-bg); }
.pos-clear-btn:disabled { opacity: 0.35; cursor: default; }

/* ── Customer search ────────────────────────────────────────── */
.pos-customer-wrap {
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid var(--border);
    position: relative;
    flex-shrink: 0;
}

.pos-customer-input {
    width: 100%;
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: var(--font-body);
    font-size: 0.8125rem;
    color: var(--text-primary);
    outline: none;
    transition: border-color var(--transition);
}

.pos-customer-input:focus { border-color: var(--accent); }

.pos-customer-clear {
    position: absolute;
    right: 1.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 1rem;
    line-height: 1;
    padding: 0;
}

.pos-customer-dropdown {
    position: absolute;
    z-index: 50;
    top: calc(100% - 0.75rem);
    left: 1.25rem;
    right: 1.25rem;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    max-height: 180px;
    overflow-y: auto;
}

.pos-customer-option {
    padding: 0.5rem 0.875rem;
    cursor: pointer;
    transition: background var(--transition);
}

.pos-customer-option:hover { background: var(--accent-light); }

/* ── Cart items ─────────────────────────────────────────────── */
.pos-cart-items {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
    padding: 0.5rem 0;
}

.pos-cart-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 120px;
    color: var(--text-muted);
    font-size: 0.875rem;
    gap: 0.5rem;
}

.pos-cart-empty-icon { font-size: 2.25rem; }

.cart-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.5rem 1.25rem;
    border-bottom: 1px solid var(--border);
    transition: background var(--transition);
}

.cart-item:hover { background: var(--bg-body); }

.cart-item-info { flex: 1; min-width: 0; }

.cart-item-name {
    font-weight: 600;
    font-size: 0.8125rem;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cart-item-price {
    font-family: var(--font-mono);
    font-size: 0.75rem;
    color: var(--text-muted);
}

.qty-ctrl {
    display: flex;
    align-items: center;
    gap: 4px;
}

.qty-btn {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 1.5px solid var(--border);
    background: var(--bg-body);
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color var(--transition), color var(--transition);
    font-weight: 700;
}

.qty-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
}

.qty-value {
    width: 26px;
    text-align: center;
    font-weight: 700;
    font-size: 0.875rem;
    color: var(--text-primary);
}

.cart-item-subtotal {
    font-family: var(--font-mono);
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--text-primary);
    width: 76px;
    text-align: right;
    flex-shrink: 0;
}

.cart-item-remove {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 1rem;
    line-height: 1;
    padding: 2px;
    border-radius: 3px;
    transition: color var(--transition), background var(--transition);
}

.cart-item-remove:hover {
    color: var(--danger);
    background: var(--danger-bg);
}

/* ── Discount & tax fields ─────────────────────────────────── */
.pos-discount-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-top: 1px solid var(--border);
    flex-shrink: 0;
}

.pos-field-label {
    font-size: 0.73rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 3px;
}

.pos-number-input {
    width: 100%;
    padding: 0.4375rem 0.625rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: var(--font-mono);
    font-size: 0.8125rem;
    color: var(--text-primary);
    outline: none;
    transition: border-color var(--transition);
}

.pos-number-input:focus { border-color: var(--accent); }

/* ── Summary ────────────────────────────────────────────────── */
.pos-summary {
    background: var(--bg-body);
    border-top: 1px solid var(--border);
    padding: 0.875rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex-shrink: 0;
}

.pos-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.8125rem;
    color: var(--text-secondary);
}

.pos-summary-row.total {
    font-size: 1.0625rem;
    font-weight: 800;
    color: var(--text-primary);
    padding-top: 6px;
    margin-top: 2px;
    border-top: 1.5px solid var(--border-dark);
}

.pos-summary-val { font-family: var(--font-mono); }
.pos-summary-val.discount { color: var(--success); }

/* ── Bayar button ───────────────────────────────────────────── */
.pos-pay-btn {
    margin: 0 1.25rem 1.25rem;
    width: calc(100% - 2.5rem);
    padding: 0.875rem;
    border: none;
    border-radius: var(--radius-md);
    background: var(--accent);
    color: #451a03;
    font-family: var(--font-body);
    font-size: 1rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 12px var(--accent-glow);
    transition: background var(--transition), box-shadow var(--transition), transform 0.15s;
    flex-shrink: 0;
}

.pos-pay-btn:hover:not(:disabled) {
    background: var(--accent-dark);
    box-shadow: 0 6px 16px rgba(245,158,11,0.4);
    transform: translateY(-1px);
}

.pos-pay-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

/* ── Payment modal internals ────────────────────────────────── */
.pay-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    align-items: center;
}

.pay-select, .pay-amount {
    padding: 0.5rem 0.75rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: var(--font-body);
    font-size: 0.8125rem;
    color: var(--text-primary);
    outline: none;
    width: 100%;
    transition: border-color var(--transition);
}

.pay-select:focus, .pay-amount:focus { border-color: var(--accent); }

.pay-remove-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 1.125rem;
    padding: 2px 4px;
    border-radius: 4px;
    transition: color var(--transition);
}

.pay-remove-btn:hover:not(:disabled) { color: var(--danger); }
.pay-remove-btn:disabled { opacity: 0.3; }

.add-payment-link {
    font-size: 0.8125rem;
    color: var(--info);
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
    padding: 0;
}

.pay-summary-box {
    background: var(--bg-body);
    border-radius: var(--radius-md);
    padding: 0.875rem 1rem;
    margin-top: 1rem;
}

.pay-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    margin-bottom: 4px;
    color: var(--text-secondary);
}

.pay-summary-row.total {
    font-size: 1.0625rem;
    font-weight: 800;
    color: var(--text-primary);
    padding-top: 6px;
    margin-top: 4px;
    border-top: 1.5px solid var(--border-dark);
}

.pay-change-positive { color: var(--success); font-weight: 700; }
.pay-insufficient { color: var(--danger); font-size: 0.8125rem; margin-top: 4px; }

/* ── Receipt print ──────────────────────────────────────────── */
/* @media print {
    body > *:not(#receipt-area) { display: none !important; }
    #receipt-area { display: block !important; font-family: monospace; }
} */
</style>
@endpush

@section('content')

<div
    x-data="posKasir({ paymentMethods: {{ $paymentMethods->toJson() }} })"
    x-init="init()"
    @keydown.escape.window="closePayment(); closeDetail(); closeReceipt()"
>

<div class="pos-shell">

    {{-- ================================================================
         KOLOM KIRI
    ================================================================ --}}
    <div class="pos-left">

        {{-- Tab bar --}}
        <div class="pos-tabs">
            <button
                class="pos-tab"
                :class="{ active: activeTab === 'products' }"
                @click="setTab('products')">
                Produk
            </button>
            <button
                class="pos-tab"
                :class="{ active: activeTab === 'history' }"
                @click="setTab('history')">
                Riwayat Transaksi
            </button>
        </div>

        {{-- ── TAB: Produk ───────────────────────────────────────── --}}
        <template x-if="activeTab === 'products'">
            <div style="display:contents;">

                {{-- Search --}}
                <div class="pos-search-wrap">
                    <input
                        type="text"
                        class="pos-search"
                        x-model="productSearch"
                        @input="searchProducts()"
                        placeholder="Cari nama atau SKU produk...">
                </div>

                {{-- Grid produk --}}
                <div class="pos-products">
                    <template x-for="p in filteredProducts" :key="p.id">
                        <button
                            class="product-card"
                            @click="addToCart(p)"
                            :disabled="p.stock <= 0">
                            <div class="product-card-name" x-text="p.name"></div>
                            <div class="product-card-sku" x-text="p.sku"></div>
                            <div class="product-card-price" x-text="formatRp(p.price)"></div>
                            <div
                                class="product-card-stock"
                                :class="p.stock <= 0 ? 'stock-empty' : (p.stock <= 5 ? 'stock-low' : 'stock-ok')"
                                x-text="p.stock <= 0 ? 'Stok habis' : 'Stok: ' + p.stock">
                            </div>
                        </button>
                    </template>

                    <template x-if="filteredProducts.length === 0">
                        <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);">
                            <div style="font-size:1.75rem;margin-bottom:.5rem;">🔍</div>
                            <div style="font-size:.875rem;">Produk tidak ditemukan</div>
                        </div>
                    </template>
                </div>

            </div>
        </template>

        {{-- ── TAB: Riwayat ──────────────────────────────────────── --}}
        <template x-if="activeTab === 'history'">
            <div class="pos-history">

                {{-- Toolbar --}}
                <div class="pos-history-toolbar">
                    <input
                        type="text"
                        x-model="historySearch"
                        @input.debounce.400ms="historyPage=1; fetchHistory()"
                        placeholder="Cari no. invoice atau pelanggan...">
                    <input
                        type="date"
                        x-model="historyDateFrom"
                        @change="historyPage=1; fetchHistory()"
                        style="width:140px;padding:.5rem .625rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:var(--font-body);font-size:.8125rem;outline:none;">
                </div>

                {{-- Table --}}
                <div class="pos-history-table-wrap">
                    <table class="datatable-table" style="min-width:unset;">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th style="text-align:right;">Total</th>
                                <th>Kasir</th>
                                <th>Waktu</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                            <template x-if="historyLoading">
                                <tr><td colspan="6" class="datatable-loading">Memuat...</td></tr>
                            </template>

                            <template x-if="!historyLoading && historyData.length === 0">
                                <tr>
                                    <td colspan="6" class="datatable-empty">
                                        <span class="datatable-empty-icon"></span>
                                        <span class="datatable-empty-text">Belum ada transaksi</span>
                                    </td>
                                </tr>
                            </template>

                            <template x-for="row in historyData" :key="row.id">
                                <tr>
                                    <td style="font-family:var(--font-mono);font-size:.78rem;font-weight:600;" x-text="row.invoice_no"></td>
                                    <td style="font-size:.8125rem;" x-text="row.customer"></td>
                                    <td style="font-family:var(--font-mono);font-weight:700;font-size:.8125rem;text-align:right;" x-text="formatRp(row.grand_total)"></td>
                                    <td style="font-size:.8125rem;" x-text="row.cashier"></td>
                                    <td style="font-size:.75rem;color:var(--text-muted);" x-text="row.sold_at"></td>
                                    <td style="white-space:nowrap;">
                                        <button class="dt-btn dt-btn-edit" @click="openDetail(row.id)">
                                            Detail
                                        </button>
                                        <button
                                            class="dt-btn"
                                            style="background:var(--info-bg);color:var(--info);border:1px solid var(--info);"
                                            @click="printFromHistory(row.id)"
                                            title="Cetak struk">
                                            🖨
                                        </button>
                                    </td>
                                </tr>
                            </template>

                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <template x-if="historyLastPage > 1">
                    <div class="pos-history-pagination">
                        <button @click="historyChangePage(historyPage - 1)" :disabled="historyPage <= 1">‹</button>
                        <template x-for="p in historyLastPage" :key="p">
                            <button
                                :class="{ active: p === historyPage }"
                                @click="historyChangePage(p)"
                                x-text="p">
                            </button>
                        </template>
                        <button @click="historyChangePage(historyPage + 1)" :disabled="historyPage >= historyLastPage">›</button>
                    </div>
                </template>

            </div>
        </template>

    </div>{{-- pos-left --}}


    {{-- ================================================================
         KOLOM KANAN: Keranjang
    ================================================================ --}}
    <div class="pos-cart">

        {{-- Header --}}
        <div class="pos-cart-header">
            <span class="pos-cart-title">🛒 Keranjang</span>
            <button
                class="pos-clear-btn"
                @click="clearCart()"
                :disabled="!cart.length">
                Kosongkan
            </button>
        </div>

        {{-- Pelanggan --}}
        <div class="pos-customer-wrap">
            <input
                type="text"
                class="pos-customer-input"
                x-model="customerSearch"
                @input="filterCustomers()"
                @focus="filterCustomers()"
                @blur.debounce.200ms="showCustomerDropdown = false"
                placeholder="Cari pelanggan (opsional)..."
                autocomplete="off">

            <template x-if="selectedCustomer">
                <button class="pos-customer-clear" @click="clearCustomer()">✕</button>
            </template>

            <template x-if="showCustomerDropdown">
                <div class="pos-customer-dropdown">
                    <template x-for="c in filteredCustomers" :key="c.id">
                        <div
                            class="pos-customer-option"
                            @mousedown.prevent="selectCustomer(c)">
                            <div style="font-weight:600;font-size:.8125rem;" x-text="c.name"></div>
                            <div style="font-size:.73rem;color:var(--text-muted);"
                                 x-text="[c.vehicle_plate, c.phone].filter(Boolean).join(' · ')"></div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Items --}}
        <div class="pos-cart-items">

            <template x-if="cart.length === 0">
                <div class="pos-cart-empty">
                    <span class="pos-cart-empty-icon">🛒</span>
                    <span>Keranjang kosong</span>
                    <span style="font-size:.75rem;color:var(--text-muted);">Klik produk untuk menambahkan</span>
                </div>
            </template>

            <template x-for="(item, idx) in cart" :key="item.product_id">
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name" x-text="item.product_name"></div>
                        <div class="cart-item-price" x-text="formatRp(item.price)"></div>
                    </div>
                    <div class="qty-ctrl">
                        <button class="qty-btn" @click="decreaseQty(idx)">−</button>
                        <span class="qty-value" x-text="item.qty"></span>
                        <button class="qty-btn" @click="increaseQty(idx)">+</button>
                    </div>
                    <div class="cart-item-subtotal" x-text="formatRp(item.qty * item.price)"></div>
                    <button class="cart-item-remove" @click="removeFromCart(idx)">✕</button>
                </div>
            </template>

        </div>

        {{-- Diskon & Pajak --}}
        <div class="pos-discount-row">
            <div>
                <span class="pos-field-label">Diskon (Rp)</span>
                <input class="pos-number-input" type="number" x-model="discount" min="0" placeholder="0">
            </div>
            <div>
                <span class="pos-field-label">Pajak (Rp)</span>
                <input class="pos-number-input" type="number" x-model="tax" min="0" placeholder="0">
            </div>
        </div>

        {{-- Summary --}}
        <div class="pos-summary">
            <div class="pos-summary-row">
                <span>Subtotal</span>
                <span class="pos-summary-val" x-text="formatRp(subtotal)"></span>
            </div>
            <template x-if="parseFloat(discount) > 0">
                <div class="pos-summary-row">
                    <span>Diskon</span>
                    <span class="pos-summary-val discount" x-text="'− ' + formatRp(discount)"></span>
                </div>
            </template>
            <template x-if="parseFloat(tax) > 0">
                <div class="pos-summary-row">
                    <span>Pajak</span>
                    <span class="pos-summary-val" x-text="'+ ' + formatRp(tax)"></span>
                </div>
            </template>
            <div class="pos-summary-row total">
                <span>Total</span>
                <span class="pos-summary-val" x-text="formatRp(grandTotal)"></span>
            </div>
        </div>

        {{-- Bayar button --}}
        <button
            class="pos-pay-btn"
            @click="openPayment()"
            :disabled="!cart.length">
            💳 Bayar
        </button>

    </div>{{-- pos-cart --}}

</div>{{-- pos-shell --}}


{{-- ================================================================
     MODAL: Pembayaran
================================================================ --}}
<template x-if="paymentOpen">
    <div class="modal-backdrop" @click.self="closePayment()"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="modal-box" style="max-width:420px;" @click.stop>

            <div class="modal-header">
                <h3 class="modal-title">Pembayaran</h3>
                <button class="modal-close-btn" @click="closePayment()" type="button"></button>
            </div>

            <div class="modal-body">

                {{-- Tagihan --}}
                <div style="background:var(--accent-light);border:1.5px solid var(--accent);border-radius:var(--radius-md);padding:12px 16px;margin-bottom:1.25rem;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.875rem;font-weight:600;color:#451a03;">Total Tagihan</span>
                    <span style="font-family:var(--font-mono);font-size:1.25rem;font-weight:800;color:#451a03;" x-text="formatRp(grandTotal)"></span>
                </div>

                {{-- Error --}}
                <template x-if="globalError">
                    <div style="background:var(--danger-bg);border:1px solid var(--danger);color:var(--danger);padding:10px 14px;border-radius:var(--radius-sm);font-size:.875rem;margin-bottom:1rem;" x-text="globalError"></div>
                </template>

                {{-- Label --}}
                <div style="font-size:.73rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.625rem;">Metode Pembayaran</div>

                {{-- Baris pembayaran --}}
                <template x-for="(p, idx) in payments" :key="idx">
                    <div style="margin-bottom:.625rem;">

                        <div class="pay-row">
                            {{-- Pilih metode --}}
                            <select
                                class="pay-select"
                                x-model="p.payment_method_id"
                                @change="onPaymentMethodChange(idx)">
                                <option value="">— Pilih metode —</option>
                                <template x-for="m in paymentMethods" :key="m.id">
                                    <option :value="m.id" x-text="m.name"></option>
                                </template>
                            </select>

                            {{-- Nominal dibayar --}}
                            <div style="position:relative;">
                                <span style="position:absolute;left:8px;top:50%;transform:translateY(-50%);font-size:.75rem;color:var(--text-muted);font-family:var(--font-mono);pointer-events:none;">Rp</span>
                                <input
                                    type="number"
                                    class="pay-amount"
                                    style="padding-left:28px;"
                                    x-model.number="p.amount"
                                    min="0"
                                    :placeholder="isCash(p.payment_method_id) ? 'Nominal diterima' : formatRp(grandTotal).replace('Rp\u00a0','')">
                            </div>

                            {{-- Hapus baris --}}
                            <button
                                class="pay-remove-btn"
                                @click="removePaymentRow(idx)"
                                :disabled="payments.length <= 1">✕</button>
                        </div>

                        {{-- Hint per baris --}}
                        <template x-if="p.payment_method_id">
                            <div style="font-size:.73rem;margin-top:3px;padding-left:2px;"
                                 :style="isCash(p.payment_method_id)
                                    ? 'color:var(--text-muted)'
                                    : 'color:var(--info)'">
                                <template x-if="isCash(p.payment_method_id)">
                                    <span>💵 Ketik nominal uang yang diterima dari customer</span>
                                </template>
                                <template x-if="!isCash(p.payment_method_id)">
                                    <span>📲 Nominal disesuaikan otomatis dengan tagihan</span>
                                </template>
                            </div>
                        </template>

                    </div>
                </template>

                {{-- Tambah metode --}}
                <button class="add-payment-link" @click="addPaymentRow()" style="margin-bottom:1rem;">
                    + Tambah metode bayar (split payment)
                </button>

                {{-- Summary --}}
                <div class="pay-summary-box">
                    <div class="pay-summary-row">
                        <span>Total Dibayar</span>
                        <span style="font-family:var(--font-mono);" x-text="formatRp(totalPaid)"></span>
                    </div>

                    {{-- Kembalian — hanya tampil kalau ada cash --}}
                    <template x-if="payments.some(p => isCash(p.payment_method_id))">
                        <div class="pay-summary-row" :class="change > 0 ? 'pay-change-positive' : ''">
                            <span>Kembalian (Cash)</span>
                            <span style="font-family:var(--font-mono);" x-text="formatRp(change)"></span>
                        </div>
                    </template>

                    {{-- Kurang bayar --}}
                    <template x-if="!isPaymentSufficient && totalPaid > 0">
                        <div style="display:flex;justify-content:space-between;color:var(--danger);font-size:.875rem;margin-top:4px;">
                            <span>⚠ Kurang</span>
                            <span style="font-family:var(--font-mono);font-weight:700;" x-text="formatRp(grandTotal - totalPaid)"></span>
                        </div>
                    </template>

                </div>

            </div>

            <div class="modal-footer">
                <button class="modal-btn-cancel" @click="closePayment()" :disabled="isSubmitting">Batal</button>
                <button
                    class="modal-btn-submit"
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
        <div class="modal-box" style="max-width:380px;" @click.stop>

            <div class="modal-header">
                <h3 class="modal-title">✅ Transaksi Berhasil</h3>
                <button class="modal-close-btn" @click="closeReceipt()" type="button"></button>
            </div>

            <div class="modal-body" id="receipt-area">
                <template x-if="receiptData">
                    <div style="font-family:var(--font-mono);font-size:.8125rem;">

                        <div style="text-align:center;margin-bottom:1rem;">
                            <div style="font-weight:700;font-size:.9375rem;letter-spacing:.05em;">STRUK PEMBAYARAN</div>
                            <div style="color:var(--text-muted);font-size:.8125rem;" x-text="receiptData.invoice_no"></div>
                            <div style="color:var(--text-muted);font-size:.75rem;" x-text="receiptData.sold_at"></div>
                        </div>

                        <template x-if="receiptData.customer">
                            <div style="margin-bottom:.5rem;padding-bottom:.5rem;border-bottom:1px dashed var(--border);">
                                <span style="color:var(--text-muted);">Pelanggan: </span>
                                <span x-text="receiptData.customer?.name"></span>
                            </div>
                        </template>

                        <div style="border-bottom:1px dashed var(--border);padding-bottom:.5rem;margin-bottom:.5rem;">
                            <template x-for="item in receiptData.items" :key="item.product_id">
                                <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                                    <span x-text="item.product_name + ' ×' + item.qty"></span>
                                    <span x-text="formatRp(item.subtotal)"></span>
                                </div>
                            </template>
                        </div>

                        <div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                            <span>Subtotal</span>
                            <span x-text="formatRp(receiptData.subtotal)"></span>
                        </div>
                        <template x-if="receiptData.discount > 0">
                            <div style="display:flex;justify-content:space-between;margin-bottom:2px;color:var(--success);">
                                <span>Diskon</span>
                                <span x-text="'− ' + formatRp(receiptData.discount)"></span>
                            </div>
                        </template>
                        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:.9375rem;border-top:1px dashed var(--border);padding-top:6px;margin-top:4px;">
                            <span>TOTAL</span>
                            <span x-text="formatRp(receiptData.grand_total)"></span>
                        </div>

                        <div style="border-top:1px dashed var(--border);margin-top:.5rem;padding-top:.5rem;">
                            <template x-for="p in receiptData.payments" :key="p.method">
                                <div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                                    <span x-text="p.method"></span>
                                    <span x-text="formatRp(p.amount)"></span>
                                </div>
                            </template>
                            <template x-if="receiptData.payments?.[0]?.change_amount > 0">
                                <div style="display:flex;justify-content:space-between;color:var(--success);font-weight:600;">
                                    <span>Kembalian</span>
                                    <span x-text="formatRp(receiptData.payments[0].change_amount)"></span>
                                </div>
                            </template>
                        </div>

                        <div style="text-align:center;margin-top:.875rem;color:var(--text-muted);font-size:.75rem;">
                            Terima kasih! 🙏
                        </div>
                    </div>
                </template>
            </div>

            <div class="modal-footer">
                <button class="modal-btn-cancel" @click="closeReceipt()">Tutup</button>
                <button class="modal-btn-submit" @click="printReceipt()">🖨 Print Struk</button>
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
                    <div style="text-align:center;padding:2.5rem;color:var(--text-muted);">Memuat...</div>
                </template>

                <template x-if="!detailLoading && detailData">
                    <div style="font-size:.875rem;">

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                            <div>
                                <div style="font-size:.73rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Invoice</div>
                                <div style="font-family:var(--font-mono);font-weight:700;" x-text="detailData.invoice_no"></div>
                            </div>
                            <div>
                                <div style="font-size:.73rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Kasir</div>
                                <div x-text="detailData.cashier"></div>
                            </div>
                            <div>
                                <div style="font-size:.73rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Pelanggan</div>
                                <div x-text="detailData.customer?.name ?? 'Umum'"></div>
                            </div>
                            <div>
                                <div style="font-size:.73rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Waktu</div>
                                <div x-text="detailData.sold_at"></div>
                            </div>
                        </div>

                        <table class="datatable-table" style="min-width:unset;margin-bottom:.75rem;">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th style="text-align:right;">Qty</th>
                                    <th style="text-align:right;">Harga</th>
                                    <th style="text-align:right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in detailData.items" :key="item.product_id">
                                    <tr>
                                        <td x-text="item.product_name"></td>
                                        <td style="text-align:right;" x-text="item.qty"></td>
                                        <td style="text-align:right;font-family:var(--font-mono);" x-text="formatRp(item.price)"></td>
                                        <td style="text-align:right;font-family:var(--font-mono);font-weight:700;" x-text="formatRp(item.subtotal)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:800;border-top:1.5px solid var(--border-dark);padding-top:8px;">
                            <span>Total</span>
                            <span style="font-family:var(--font-mono);" x-text="formatRp(detailData.grand_total)"></span>
                        </div>

                        <div style="margin-top:.75rem;">
                            <div style="font-size:.73rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Pembayaran</div>
                            <template x-for="p in detailData.payments" :key="p.method">
                                <div style="display:flex;justify-content:space-between;font-size:.8125rem;margin-bottom:3px;">
                                    <span style="color:var(--text-secondary);" x-text="p.method"></span>
                                    <span style="font-family:var(--font-mono);" x-text="formatRp(p.amount)"></span>
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
@endsection
