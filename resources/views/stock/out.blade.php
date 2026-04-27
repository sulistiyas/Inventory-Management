@extends('layouts.app')

@section('title', 'Stock Out')
@section('breadcrumb', 'Stock Out')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/modal.css') }}">
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Transaksi Stock Out</h1>
        <p class="page-subtitle">Keluarkan stok produk dari gudang</p>
    </div>
</div>

<div style="max-width:600px;"
     x-data="stockForm('out', @js($products))"
     x-init="init()"
>

    <div class="card">
        <div class="card-body">

            <form @submit.prevent="submit('/api/stock/out', '{{ route('stock.index') }}')">

                {{-- GLOBAL ERROR --}}
                <template x-if="globalError">
                    <div class="modal-global-error" style="margin-bottom:16px;">
                        <span x-text="globalError"></span>
                    </div>
                </template>

                {{-- =========================
                     PRODUCT SELECT
                     ========================= --}}
                <div class="modal-form-group">
                    <label>
                        Produk <span class="modal-required">*</span>
                    </label>

                    {{-- SEARCH --}}
                    <input
                        type="text"
                        x-model="productSearch"
                        @input="filterProducts()"
                        placeholder="Cari nama atau SKU..."
                    />

                    {{-- LIST --}}
                    <div style="max-height:180px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius-sm); padding:6px; margin-top:6px;">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div
                                class="product-select-option"
                                :class="{
                                    'selected': form.product_id == product.id,
                                    'low-stock': product.is_low
                                }"
                                @click="selectProduct(product)"
                            >
                                <div>
                                    <div style="font-weight:600;" x-text="product.name"></div>
                                    <div class="text-muted text-sm">
                                        <span class="text-mono" x-text="product.sku"></span>
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
                            <div class="text-muted text-sm" style="padding:12px; text-align:center;">
                                Produk tidak ditemukan
                            </div>
                        </template>
                    </div>

                    {{-- ERROR --}}
                    <template x-if="errors.product_id">
                        <span class="modal-field-error" x-text="errors.product_id[0]"></span>
                    </template>

                    {{-- SELECTED INFO --}}
                    <template x-if="selectedProduct">
                        <div style="margin-top:8px;">
                            Stok saat ini:
                            <strong class="text-mono"
                                    :style="selectedProduct.stock === 0
                                        ? 'color:var(--danger)'
                                        : (selectedProduct.isLow ? 'color:var(--warning)' : 'color:var(--success)')"
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
                        Jumlah Keluar <span class="modal-required">*</span>
                    </label>

                    <div class="qty-input-wrapper">
                        <button type="button"
                                class="qty-btn"
                                @click="decrement()"
                                :disabled="form.quantity <= 1">
                            −
                        </button>

                        <input
                            type="number"
                            x-model.number="form.quantity"
                            min="1"
                        >

                        <button type="button"
                                class="qty-btn"
                                @click="increment()"
                                :disabled="selectedProduct && form.quantity >= selectedProduct.stock">
                            +
                        </button>
                    </div>

                    {{-- ERROR --}}
                    <template x-if="errors.quantity">
                        <span class="modal-field-error" x-text="errors.quantity[0]"></span>
                    </template>

                    {{-- WARNING MELEBIHI STOK --}}
                    <template x-if="selectedProduct && form.quantity > selectedProduct.stock">
                        <span class="modal-field-error" style="color:var(--warning);">
                            Melebihi stok tersedia (<span x-text="selectedProduct.stock"></span> unit)
                        </span>
                    </template>

                    {{-- PREVIEW --}}
                    <template x-if="selectedProduct && form.quantity > 0">
                        <div style="margin-top:6px; font-size:12px;">
                            Stok setelah:
                            <strong class="text-mono"
                                    :style="stockAfter < 0
                                        ? 'color:var(--danger)'
                                        : (stockAfter <= selectedProduct.min_stock ? 'color:var(--warning)' : 'color:var(--success)')"
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
                    <textarea
                        x-model="form.notes"
                        placeholder="Contoh: pengiriman ke pelanggan, dll."
                    ></textarea>
                </div>

                {{-- =========================
                     SUBMIT
                     ========================= --}}
                <div style="display:flex; gap:8px; margin-top:20px;">
                    <a href="{{ route('stock.index') }}" class="btn btn-secondary">
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn"
                        style="flex:1; background:var(--danger); color:#fff;"
                        :disabled="loading || !form.product_id || (selectedProduct && form.quantity > selectedProduct.stock)"
                    >
                        <span x-show="loading">Menyimpan...</span>
                        <span x-show="!loading">Simpan Stock Out</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection