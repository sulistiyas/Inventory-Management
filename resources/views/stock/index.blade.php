@extends('layouts.app')

@section('title', 'Manajemen Stok')
@section('breadcrumb', 'Manajemen Stok')

@push('styles')
<style>
/* ── Stock-specific styles ──────────────────────────────── */
.stock-type-in  { color: var(--success); }
.stock-type-out { color: var(--danger); }

.product-select-option {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 12px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); cursor: pointer;
    transition: border-color var(--transition), background var(--transition);
    margin-bottom: 6px;
}
.product-select-option:hover  { border-color: var(--accent); background: var(--accent-light); }
.product-select-option.selected { border-color: var(--accent); background: var(--accent-light); }
.product-select-option.low-stock { border-left: 3px solid var(--warning); }

.stock-indicator {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 600; font-family: var(--font-mono);
    padding: 3px 8px; border-radius: 20px;
}
.stock-indicator.ok  { background: var(--success-bg); color: #065F46; }
.stock-indicator.low { background: var(--warning-bg); color: #92400E; }
.stock-indicator.out { background: var(--danger-bg);  color: #991B1B; }

.qty-input-wrapper {
    display: flex; align-items: center; gap: 0;
    border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden;
}
.qty-btn {
    width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;
    background: var(--bg-body); border: none; cursor: pointer;
    font-size: 18px; font-weight: 600; color: var(--text-secondary);
    transition: background var(--transition), color var(--transition);
    flex-shrink: 0;
}
.qty-btn:hover { background: var(--border); color: var(--text-primary); }
.qty-input {
    flex: 1; border: none; outline: none; text-align: center;
    font-size: 15px; font-weight: 700; font-family: var(--font-mono);
    color: var(--text-primary); background: transparent; padding: 0 6px;
    min-width: 0;
}

.tab-nav {
    display: flex; gap: 0;
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    overflow: hidden; background: var(--bg-body);
}
.tab-btn {
    flex: 1; padding: 8px 20px; font-size: 13.5px; font-weight: 600;
    border: none; cursor: pointer; background: transparent;
    color: var(--text-muted); font-family: var(--font-body);
    transition: background var(--transition), color var(--transition);
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.tab-btn.active { background: var(--bg-card); color: var(--text-primary); }
.tab-btn.active.in  { color: var(--success); }
.tab-btn.active.out { color: var(--danger); }
.tab-btn svg { width: 15px; height: 15px; }
</style>
@endpush

@section('content')
<div
    x-data="stockManager()"
    x-init="init()"
    @keydown.escape.window="close()"
>

{{-- ── Page header ──────────────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Manajemen Stok</h1>
        <p class="page-subtitle">Pantau pergerakan stok masuk dan keluar</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button @click="openStockIn()" class="btn btn-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Stok Masuk
        </button>
        <button @click="openStockOut()" class="btn btn-secondary btn-sm" style="color:var(--danger); border-color:var(--danger-bg);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
            Stok Keluar
        </button>
        <a href="{{ route('stock.in') }}" class="btn btn-secondary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            Full Form
        </a>
    </div>
</div>

{{-- ── Summary cards ────────────────────────────────────────────── --}}
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 20px;">

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Masuk Hari Ini</span>
            <span class="stat-card-icon green">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </span>
        </div>
        <div class="stat-card-value">{{ number_format($summary['today_in']) }}</div>
        <div class="stat-card-footer">
            <span class="text-muted text-sm">unit masuk · {{ now()->format('d M Y') }}</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Keluar Hari Ini</span>
            <span class="stat-card-icon red">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
            </span>
        </div>
        <div class="stat-card-value">{{ number_format($summary['today_out']) }}</div>
        <div class="stat-card-footer">
            <span class="text-muted text-sm">unit keluar · {{ $summary['today_count'] }} transaksi</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Produk Stok Rendah</span>
            <span class="stat-card-icon amber">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </span>
        </div>
        <div class="stat-card-value {{ $summary['low_stock_count'] > 0 ? 'stock-type-out' : '' }}">
            {{ number_format($summary['low_stock_count']) }}
        </div>
        <div class="stat-card-footer">
            @if($summary['low_stock_count'] > 0)
                <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" style="color:var(--warning); font-size:12px; font-weight:600; text-decoration:none;">Lihat produk →</a>
            @else
                <span class="text-muted text-sm">Semua stok aman</span>
            @endif
        </div>
    </div>

</div>

{{-- ── Filter bar ──────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:16px; padding:16px 20px;">
    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">

        {{-- Search --}}
        <div style="position:relative; flex:1; min-width:200px;">
            <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:var(--text-muted);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" x-model="filters.search" @input.debounce.400ms="loadData()"
                   placeholder="Cari produk / SKU..."
                   style="width:100%; padding:8px 12px 8px 34px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13.5px; outline:none; font-family:var(--font-body);" />
        </div>

        {{-- Type filter --}}
        <select x-model="filters.type" @change="loadData()"
                style="padding:8px 32px 8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13.5px; background:var(--bg-card); font-family:var(--font-body); outline:none; appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2394A3B8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 9l6 6 6-6'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 8px center; background-size:16px;">
            <option value="">Semua Tipe</option>
            <option value="in">Stok Masuk</option>
            <option value="out">Stok Keluar</option>
        </select>

        {{-- Date from --}}
        <input type="date" x-model="filters.date_from" @change="loadData()"
               style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:var(--font-body); outline:none;" />

        {{-- Date to --}}
        <input type="date" x-model="filters.date_to" @change="loadData()"
               style="padding:8px 12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; font-family:var(--font-body); outline:none;" />

        {{-- Clear --}}
        <button @click="clearFilters()" class="btn btn-secondary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            Reset
        </button>
    </div>
</div>

{{-- ── Movements datatable ─────────────────────────────────────── --}}
<div class="card">

    {{-- Loading skeleton --}}
    <template x-if="isLoading">
        <div style="padding:20px;">
            <template x-for="i in 8" :key="i">
                <div style="display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--border);">
                    <div class="skeleton" style="width:40px; height:20px; border-radius:4px;"></div>
                    <div class="skeleton" style="width:120px; height:20px; border-radius:4px;"></div>
                    <div class="skeleton" style="flex:1; height:20px; border-radius:4px;"></div>
                    <div class="skeleton" style="width:80px; height:20px; border-radius:4px;"></div>
                    <div class="skeleton" style="width:60px; height:20px; border-radius:4px;"></div>
                </div>
            </template>
        </div>
    </template>

    {{-- Table --}}
    <template x-if="!isLoading">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipe</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Sebelum</th>
                        <th style="text-align:right;">Sesudah</th>
                        <th>Operator</th>
                        <th>Waktu</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in tableData" :key="row.id">
                        <tr>
                            <td class="text-mono text-muted text-sm" x-text="row.id"></td>

                            <td>
                                <span class="badge" :class="row.type_class" style="gap:5px;">
                                    <span style="width:6px; height:6px; border-radius:50%; background:currentColor; flex-shrink:0;"></span>
                                    <span x-text="row.type_label"></span>
                                </span>
                            </td>

                            <td>
                                <div style="font-weight:600; font-size:13.5px;" x-text="row.product?.name ?? '—'"></div>
                                <div class="text-mono text-sm text-muted" x-text="row.product?.sku ?? ''"></div>
                            </td>

                            <td>
                                <span class="badge badge-secondary" x-text="row.product?.category ?? '—'"></span>
                            </td>

                            <td style="text-align:right;">
                                <span
                                    class="text-mono font-semibold"
                                    :style="row.type === 'in' ? 'color:var(--success)' : 'color:var(--danger)'"
                                    x-text="(row.type === 'in' ? '+' : '-') + Number(row.quantity).toLocaleString('id-ID')"
                                ></span>
                            </td>

                            <td style="text-align:right;" class="text-mono text-muted text-sm" x-text="Number(row.stock_before).toLocaleString('id-ID')"></td>
                            <td style="text-align:right;" class="text-mono font-semibold" x-text="Number(row.stock_after).toLocaleString('id-ID')"></td>

                            <td style="font-size:13px;" x-text="row.user?.name ?? '—'"></td>

                            <td>
                                <div style="font-size:12px; color:var(--text-muted);" x-text="row.created_at_human" :title="row.created_at_formatted"></div>
                            </td>

                            <td style="text-align:right;">
                                <button
                                    @click="openDetail(row.id)"
                                    class="btn btn-secondary btn-sm"
                                    style="padding:4px 10px;"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="tableData.length === 0">
                        <tr>
                            <td colspan="10" style="text-align:center; padding:48px 20px; color:var(--text-muted);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:40px; height:40px; margin:0 auto 10px; display:block; opacity:0.3;"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7m16 0v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5m16 0h-2.586a1 1 0 0 0-.707.293l-2.414 2.414a1 1 0 0 1-.707.293h-3.172a1 1 0 0 1-.707-.293l-2.414-2.414A1 1 0 0 0 6.586 13H4"/></svg>
                                <div style="font-weight:600; font-size:14px;">Belum ada transaksi</div>
                                <div style="font-size:12px; margin-top:4px;">Mulai dengan klik "Stok Masuk" atau "Stok Keluar"</div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>

    {{-- Pagination --}}
    <template x-if="lastPage > 1">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-top:1px solid var(--border); flex-wrap:wrap; gap:10px;">
            <div style="font-size:12px; color:var(--text-muted);">
                Menampilkan <strong x-text="(currentPage - 1) * perPage + 1"></strong>–<strong x-text="Math.min(currentPage * perPage, total)"></strong>
                dari <strong x-text="total"></strong> transaksi
            </div>
            <div style="display:flex; gap:4px;">
                <button class="datatable-page-btn datatable-page-btn--nav" @click="changePage(currentPage - 1)" :disabled="currentPage <= 1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </button>
                <template x-for="p in pageNumbers" :key="p">
                    <button class="datatable-page-btn" :class="{ 'active': p === currentPage }" @click="changePage(p)" x-text="p"></button>
                </template>
                <button class="datatable-page-btn datatable-page-btn--nav" @click="changePage(currentPage + 1)" :disabled="currentPage >= lastPage">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>
        </div>
    </template>

</div>{{-- .card --}}

{{-- ================================================================
     MODAL: Stock In / Stock Out (tab-based, single modal)
     ================================================================ --}}
<div
    x-show="isOpen"
    x-cloak
    class="modal-backdrop"
    @click.self="close()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div
        class="modal-panel modal-size-md"
        @click.stop
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        {{-- Header --}}
        <div class="modal-header">
            <div class="modal-header-icon" :class="activeTab === 'in' ? 'create' : 'delete'">
                <template x-if="activeTab === 'in'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </template>
                <template x-if="activeTab === 'out'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                </template>
            </div>
            <div class="modal-title-group">
                <div class="modal-title" x-text="activeTab === 'in' ? 'Transaksi Stok Masuk' : 'Transaksi Stok Keluar'"></div>
                <div class="modal-subtitle" x-text="activeTab === 'in' ? 'Tambah stok produk ke gudang' : 'Kurangi stok produk dari gudang'"></div>
            </div>
            <button class="modal-close-btn" @click="close()" :disabled="isSubmitting" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form @submit.prevent="handleSubmit()">
            <div class="modal-body">

                {{-- Tab switcher --}}
                <div class="tab-nav" style="margin-bottom:20px;">
                    <button type="button" class="tab-btn" :class="{ 'active in': activeTab === 'in' }" @click="switchTab('in')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Stok Masuk
                    </button>
                    <button type="button" class="tab-btn" :class="{ 'active out': activeTab === 'out' }" @click="switchTab('out')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                        Stok Keluar
                    </button>
                </div>

                {{-- Global error --}}
                <template x-if="globalError">
                    <div class="modal-global-error" style="margin-bottom:16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                        <span x-text="globalError"></span>
                    </div>
                </template>

                <div class="modal-form">

                    {{-- Product selector --}}
                    <div class="form-group">
                        <label class="form-label">
                            Produk <span class="form-label-required">*</span>
                        </label>

                        {{-- Search product --}}
                        <div style="position:relative; margin-bottom:8px;">
                            <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:var(--text-muted);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <input type="text" x-model="productSearch" @input="filterProducts()"
                                   placeholder="Cari nama atau SKU produk..."
                                   class="form-input" style="padding-left:32px;" />
                        </div>

                        {{-- Product list --}}
                        <div style="max-height:180px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius-sm); padding:6px;">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <div
                                    class="product-select-option"
                                    :class="{
                                        'selected':  form.product_id == product.id,
                                        'low-stock': product.is_low
                                    }"
                                    @click="selectProduct(product)"
                                >
                                    <div>
                                        <div style="font-size:13.5px; font-weight:600;" x-text="product.name"></div>
                                        <div style="font-size:11px; color:var(--text-muted);">
                                            <span class="text-mono" x-text="product.sku"></span>
                                            <span x-text="product.category ? ' · ' + product.category : ''"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <span
                                            class="stock-indicator"
                                            :class="product.stock === 0 ? 'out' : (product.is_low ? 'low' : 'ok')"
                                            x-text="product.stock + ' unit'"
                                        ></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="filteredProducts.length === 0">
                                <div style="text-align:center; padding:16px; color:var(--text-muted); font-size:13px;">
                                    Produk tidak ditemukan
                                </div>
                            </template>
                        </div>

                        <template x-if="errors.product_id">
                            <div class="form-error" x-text="errors.product_id[0]"></div>
                        </template>

                        {{-- Selected product info bar --}}
                        <template x-if="selectedProduct">
                            <div style="margin-top:8px; padding:10px 12px; background:var(--bg-body); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:space-between; font-size:13px;">
                                <span style="font-weight:600; color:var(--text-primary);" x-text="selectedProduct.name"></span>
                                <span>
                                    Stok saat ini:
                                    <strong class="text-mono"
                                        :style="selectedProduct.is_low ? 'color:var(--warning)' : 'color:var(--success)'"
                                        x-text="selectedProduct.stock + ' unit'"
                                    ></strong>
                                </span>
                            </div>
                        </template>
                    </div>

                    {{-- Quantity with +/- buttons --}}
                    <div class="form-group">
                        <label class="form-label">
                            Jumlah <span class="form-label-required">*</span>
                        </label>
                        <div class="qty-input-wrapper" :class="{ 'has-error': errors.quantity }">
                            <button type="button" class="qty-btn" @click="decrement()" :disabled="form.quantity <= 1">−</button>
                            <input
                                type="number"
                                class="qty-input"
                                x-model.number="form.quantity"
                                @input="delete errors.quantity"
                                min="1"
                                max="999999"
                            />
                            <button type="button" class="qty-btn" @click="increment()">+</button>
                        </div>

                        {{-- Stock out warning --}}
                        <template x-if="activeTab === 'out' && selectedProduct && form.quantity > selectedProduct.stock">
                            <div class="form-error" style="color:var(--warning);">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                Melebihi stok tersedia (<span x-text="selectedProduct.stock"></span> unit)
                            </div>
                        </template>

                        <template x-if="errors.quantity">
                            <div class="form-error" x-text="errors.quantity[0]"></div>
                        </template>

                        {{-- Preview stock result --}}
                        <template x-if="selectedProduct && form.quantity > 0">
                            <div style="margin-top:6px; font-size:12px; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                                <span>Stok setelah transaksi:</span>
                                <strong class="text-mono"
                                    :style="stockResult < 0 ? 'color:var(--danger)' : (stockResult <= selectedProduct.min_stock ? 'color:var(--warning)' : 'color:var(--success)')"
                                    x-text="stockResult + ' unit'"
                                ></strong>
                            </div>
                        </template>
                    </div>

                    {{-- Notes --}}
                    <div class="form-group">
                        <label class="form-label">Catatan</label>
                        <textarea
                            class="form-textarea"
                            x-model="form.notes"
                            placeholder="Contoh: PO #12345, retur dari pelanggan, dll. (opsional)"
                            rows="2"
                            :disabled="isSubmitting"
                        ></textarea>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" @click="close()" :disabled="isSubmitting">Batal</button>
                <button
                    type="submit"
                    class="btn btn-sm"
                    :class="activeTab === 'in' ? 'btn-primary' : 'btn-danger'"
                    :disabled="isSubmitting || !form.product_id"
                >
                    <span x-show="isSubmitting" class="btn-spinner"></span>
                    <template x-if="!isSubmitting">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </template>
                    <span x-text="isSubmitting ? 'Menyimpan...' : (activeTab === 'in' ? 'Simpan Stok Masuk' : 'Simpan Stok Keluar')"></span>
                </button>
            </div>
        </form>

    </div>
</div>

{{-- ================================================================
     MODAL: Movement Detail (view only)
     ================================================================ --}}
<div
    x-show="detailOpen"
    x-cloak
    class="modal-backdrop"
    @click.self="detailOpen = false"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="modal-panel modal-size-md" @click.stop
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
    >
        <div class="modal-header">
            <div class="modal-header-icon view">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
            </div>
            <div class="modal-title-group">
                <div class="modal-title">Detail Transaksi</div>
                <div class="modal-subtitle text-mono" x-text="'#' + (detailData?.id ?? '')"></div>
            </div>
            <button class="modal-close-btn" @click="detailOpen = false" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="modal-body" x-show="detailData">
            <template x-if="detailLoading">
                <div style="text-align:center; padding:40px; color:var(--text-muted);">Memuat...</div>
            </template>
            <template x-if="!detailLoading && detailData">
                <div style="display:flex; flex-direction:column; gap:0;">
                    <template x-for="[label, value, mono] in detailRows()" :key="label">
                        <div style="display:flex; gap:12px; padding:11px 0; border-bottom:1px solid var(--border); align-items:center;">
                            <span style="width:120px; font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.04em; flex-shrink:0;" x-text="label"></span>
                            <span :class="mono ? 'text-mono' : ''" style="font-size:13.5px; color:var(--text-primary); word-break:break-word;" x-text="value || '—'"></span>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <div class="modal-footer">
            <a :href="'/stock/' + detailData?.id" class="btn btn-secondary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Lihat Halaman Penuh
            </a>
            <button class="btn btn-secondary btn-sm" @click="detailOpen = false">Tutup</button>
        </div>
    </div>
</div>

</div>{{-- x-data --}}
@endsection