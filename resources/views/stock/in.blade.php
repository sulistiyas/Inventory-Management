@extends('layouts.app')

@section('title', 'Stock In')
@section('breadcrumb', 'Stock In')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/modal.css') }}">
@endpush

@section('content')

{{-- ── Page Header ─────────────────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <a href="{{ route('stock.index') }}"
               style="font-size:13px;color:var(--text-muted);text-decoration:none;display:flex;align-items:center;gap:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor" style="width:14px;height:14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Manajemen Stok
            </a>
            <span style="color:var(--border-dark);">/</span>
            <span style="font-size:13px;color:var(--text-secondary);">Stock In</span>
        </div>
        <h1 class="page-title">Transaksi Stock In</h1>
        <p class="page-subtitle">Tambah stok produk ke gudang</p>
    </div>
</div>

{{-- ── Form ─────────────────────────────────────────────────────────── --}}
<div style="max-width:600px;"
     x-data="stockForm('in', @js($products))"
     x-init="init()"
>
    <div class="card">
        <div class="card-body">
            <form @submit.prevent="submit('/api/stock/in', '{{ route('stock.index') }}')">

                {{-- Global error --}}
                <template x-if="globalError">
                    <div class="modal-global-error" style="margin-bottom:16px;">
                        <span x-text="globalError"></span>
                    </div>
                </template>

                {{-- =========================
                     PRODUCT SELECT (INDEX STYLE)
                     ========================= --}}
                <div class="modal-form-group">
                    <label>
                        Produk <span class="modal-required">*</span>
                    </label>

                    {{-- Search --}}
                    <input
                        type="text"
                        x-model="productSearch"
                        @input="filterProducts()"
                        placeholder="Cari nama atau SKU..."
                    />

                    {{-- List --}}
                    <div style="max-height:180px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius-sm); padding:6px; margin-top:6px;">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div
                                class="product-select-option"
                                :class="{ 'selected': form.product_id == product.id }"
                                @click="selectProduct(product)"
                            >
                                <div>
                                    <div style="font-weight:600;" x-text="product.name"></div>
                                    <div class="text-muted text-sm">
                                        <span class="text-mono" x-text="product.sku"></span>
                                    </div>
                                </div>
                                <div>
                                    <span class="stock-indicator"
                                          :class="product.stock === 0 ? 'out' : (product.is_low ? 'low' : 'ok')"
                                          x-text="product.stock + ' unit'">
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template x-if="filteredProducts.length === 0">
                            <div class="text-muted text-sm" style="padding:12px; text-align:center;">
                                Produk tidak ditemukan
                            </div>
                        </template>
                    </div>

                    <template x-if="errors.product_id">
                        <span class="modal-field-error" x-text="errors.product_id[0]"></span>
                    </template>

                    {{-- Selected info --}}
                    <template x-if="selectedProduct">
                        <div style="margin-top:8px;">
                            Stok saat ini:
                            <strong class="text-mono"
                                    :style="selectedProduct.isLow ? 'color:var(--warning)' : 'color:var(--success)'"
                                    x-text="selectedProduct.stock + ' unit'">
                            </strong>
                        </div>
                    </template>
                </div>

                {{-- =========================
                     QUANTITY
                     ========================= --}}
                <div class="modal-form-group">
                    <label>
                        Jumlah Masuk <span class="modal-required">*</span>
                    </label>

                    <div class="qty-input-wrapper">
                        <button type="button" class="qty-btn" @click="decrement()">−</button>
                        <input type="number"
                               x-model.number="form.quantity"
                               min="1">
                        <button type="button" class="qty-btn" @click="increment()">+</button>
                    </div>

                    <template x-if="errors.quantity">
                        <span class="modal-field-error" x-text="errors.quantity[0]"></span>
                    </template>

                    {{-- Preview --}}
                    <template x-if="selectedProduct">
                        <div style="margin-top:6px; font-size:12px;">
                            Stok setelah:
                            <strong class="text-mono"
                                    style="color:var(--success)"
                                    x-text="stockAfter + ' unit'">
                            </strong>
                        </div>
                    </template>
                </div>

                {{-- =========================
                     NOTES
                     ========================= --}}
                <div class="modal-form-group">
                    <label>Catatan</label>
                    <textarea x-model="form.notes"></textarea>
                </div>

                {{-- =========================
                     SUBMIT
                     ========================= --}}
                <div style="display:flex; gap:8px; margin-top:20px;">
                    <a href="{{ route('stock.index') }}" class="btn btn-secondary">
                        Batal
                    </a>

                    <button type="submit"
                            class="btn btn-primary"
                            :disabled="loading || !form.product_id"
                            style="flex:1;">
                        <span x-show="loading">Menyimpan...</span>
                        <span x-show="!loading">Simpan Stock In</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection