@extends('layouts.app')

@section('title', 'Stock Movements')
@section('breadcrumb', 'Stock Movements')


@section('content')

{{-- ── Page wrapper — datatable x-data ─────────────────────────── --}}
<div
    x-data="datatable({
        apiEndpoint: '{{ route('stock-movements.index') }}',
        perPage: 10,
        columns: [],
    })"
    x-init="init()"
    class="datatable"
>

    {{-- ── Header bar ───────────────────────────────────────────── --}}
    <div class="datatable-header">
        <h2>Stock Movements</h2>

        <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
            {{-- Search --}}
            <div class="datatable-search-wrap">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Cari produk / SKU..."
                    autocomplete="off"
                />
            </div>

            {{-- Add Transaction — opens modal via modal.js openCreate() --}}
            <button
                class="datatable-add-btn"
                @click="$dispatch('open-create-movement')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="2.5" stroke="currentColor" style="width:14px;height:14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Add Transaction
            </button>
        </div>
    </div>

    {{-- ── Table card ───────────────────────────────────────────── --}}
    <div class="datatable-card">
        <div class="datatable-table-wrap">
            <table class="datatable-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Before → After</th>
                        <th>Notes</th>
                        <th>User</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- Loading state --}}
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="datatable-loading">Memuat data...</td>
                        </tr>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="!loading && data.length === 0">
                        <tr>
                            <td colspan="8" class="datatable-empty">
                                <span class="datatable-empty-icon"></span>
                                <span class="datatable-empty-text">Belum ada transaksi</span>
                                <span class="datatable-empty-sub">Klik "Add Transaction" untuk memulai</span>
                            </td>
                        </tr>
                    </template>

                    {{-- Rows --}}
                    <template x-if="!loading && data.length > 0">
                        <template x-for="(row, i) in data" :key="row.id">
                            <tr>
                                {{-- No --}}
                                <td x-text="numberStart + i + 1"></td>

                                {{-- Product --}}
                                <td>
                                    <div style="font-weight:600;font-size:.875rem;" x-text="row.product_name"></div>
                                    <div style="font-size:.75rem;color:var(--text-muted);font-family:var(--font-mono);" x-text="row.product_sku"></div>
                                </td>

                                {{-- Type badge --}}
                                <td>
                                    <span
                                        class="badge"
                                        :class="row.type_class"
                                        style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;"
                                        x-text="row.type_label"
                                    ></span>
                                </td>

                                {{-- Qty — signed, coloured --}}
                                <td style="text-align:right;font-family:var(--font-mono);font-weight:700;">
                                    <span
                                        :style="row.type === 'in' ? 'color:var(--success)' : (row.type === 'out' ? 'color:var(--danger)' : 'color:var(--warning)')"
                                        x-text="(row.type === 'in' ? '+' : (row.type === 'out' ? '-' : '±')) + Number(row.quantity).toLocaleString('id-ID')"
                                    ></span>
                                </td>

                                {{-- Before → After --}}
                                <td style="text-align:right;font-family:var(--font-mono);font-size:.8125rem;">
                                    <span style="color:var(--text-muted);" x-text="Number(row.stock_before).toLocaleString('id-ID')"></span>
                                    <span style="color:var(--text-muted);margin:0 4px;">→</span>
                                    <span style="font-weight:700;" x-text="Number(row.stock_after).toLocaleString('id-ID')"></span>
                                </td>

                                {{-- Notes --}}
                                <td style="font-size:.8125rem;color:var(--text-secondary);max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <span x-text="row.notes || '—'"></span>
                                </td>

                                {{-- User --}}
                                <td style="font-size:.8125rem;" x-text="row.user_name"></td>

                                {{-- Date --}}
                                <td style="font-size:.8125rem;color:var(--text-muted);white-space:nowrap;" x-text="row.created_at"></td>
                            </tr>
                        </template>
                    </template>

                </tbody>
            </table>
        </div>

        {{-- ── Pagination — uses datatable.js changePage() ─────── --}}
        <template x-if="lastPage > 1">
            <div class="datatable-pagination">
                <button @click="changePage(page - 1)" :disabled="page <= 1">‹</button>

                <template x-for="p in lastPage" :key="p">
                    <button
                        :class="{ 'active': p === page }"
                        @click="changePage(p)"
                        x-text="p"
                    ></button>
                </template>

                <button @click="changePage(page + 1)" :disabled="page >= lastPage">›</button>
            </div>
        </template>

    </div>{{-- .datatable-card --}}

</div>{{-- datatable x-data --}}


{{-- ================================================================
     MODAL — uses modal.js API exactly:
       config.endpoint      → '/stock-movements'
       config.createTitle   → 'Add Transaction'
       config.defaultForm() → { product_id:'', type:'in', quantity:1, new_stock:'', notes:'' }
       submit()             → POST to /stock-movements/store
       open-create-movement → custom event → calls openCreate()
     ================================================================ --}}
<div
    x-data="modal({
        endpoint:     '/stock-movements',
        createTitle:  'Add Transaction',
        defaultForm:  () => ({
            product_id: '',
            type:       'in',
            quantity:   1,
            new_stock:  '',
            notes:      '',
        }),
    })"
    x-init="init()"
    @open-create-movement.window="openCreate()"
    @keydown.escape.window="close()"
>
    {{-- ── Backdrop + Box ─────────────────────────────────────── --}}
    <div
        class="modal-backdrop"
        x-show="open"
        x-cloak
        @click.self="close()"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            class="modal-box"
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >

            {{-- ── Header ─────────────────────────────────────── --}}
            <div class="modal-header">
                <h3 class="modal-title" x-text="title"></h3>
                <button class="modal-close-btn" @click="close()" type="button" aria-label="Close"></button>
            </div>

            {{-- ── Body ───────────────────────────────────────── --}}
            <div class="modal-body">
                <form id="movement-form" @submit.prevent="submit()">
                    <div class="modal-form-group">

                        {{-- ── Delete confirm ─────────────────── --}}
                        <template x-if="mode === 'delete'">
                            <div class="modal-delete-confirm">
                                <div class="modal-delete-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="2" stroke="currentColor" style="width:28px;height:28px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                    </svg>
                                </div>
                                <span class="modal-delete-text">Hapus transaksi ini?</span>
                                <span class="modal-delete-sub">Tindakan ini tidak dapat dibatalkan.</span>
                            </div>
                        </template>

                        {{-- ── Create / Edit form ─────────────── --}}
                        <template x-if="mode !== 'delete'">
                            <div>

                                {{-- Product --}}
                                <div class="modal-field">
                                    <label>
                                        Produk
                                        <span class="modal-required">*</span>
                                    </label>
                                    <select
                                        x-model="form.product_id"
                                        :disabled="loading"
                                        @change="delete errors.product_id"
                                    >
                                        <option value="">— Pilih produk —</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}">
                                                [{{ $p->sku }}] {{ $p->name }} — Stok: {{ $p->stock }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <template x-if="errors.product_id">
                                        <span class="modal-field-error" x-text="errors.product_id[0]"></span>
                                    </template>
                                </div>

                                {{-- Type --}}
                                <div class="modal-field">
                                    <label>
                                        Tipe Transaksi
                                        <span class="modal-required">*</span>
                                    </label>
                                    <select
                                        x-model="form.type"
                                        :disabled="loading"
                                        @change="delete errors.type"
                                    >
                                        <option value="in">Stock In</option>
                                        <option value="out">Stock Out</option>
                                        {{-- Adjustment: hanya tampil untuk admin --}}
                                        @if($isAdmin)
                                            <option value="adjustment">Adjustment</option>
                                        @endif
                                    </select>
                                    <template x-if="errors.type">
                                        <span class="modal-field-error" x-text="errors.type[0]"></span>
                                    </template>
                                </div>

                                {{-- Quantity — tampil untuk in / out --}}
                                <template x-if="form.type === 'in' || form.type === 'out'">
                                    <div class="modal-field">
                                        <label>
                                            Jumlah
                                            <span class="modal-required">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            x-model.number="form.quantity"
                                            :disabled="loading"
                                            min="1"
                                            max="999999"
                                            placeholder="Contoh: 10"
                                            @input="delete errors.quantity"
                                        />
                                        <template x-if="errors.quantity">
                                            <span class="modal-field-error" x-text="errors.quantity[0]"></span>
                                        </template>
                                    </div>
                                </template>

                                {{-- New Stock — hanya untuk adjustment (admin) --}}
                                @if($isAdmin)
                                    <template x-if="form.type === 'adjustment'">
                                        <div class="modal-field">
                                            <label>
                                                Stok Baru
                                                <span class="modal-required">*</span>
                                            </label>
                                            <input
                                                type="number"
                                                x-model.number="form.new_stock"
                                                :disabled="loading"
                                                min="0"
                                                placeholder="Masukkan jumlah stok baru"
                                                @input="delete errors.new_stock"
                                            />
                                            <span style="font-size:.75rem;color:var(--text-muted);display:block;margin-top:.25rem;">
                                                Stok produk akan di-set ke nilai ini secara langsung.
                                            </span>
                                            <template x-if="errors.new_stock">
                                                <span class="modal-field-error" x-text="errors.new_stock[0]"></span>
                                            </template>
                                        </div>
                                    </template>
                                @endif

                                {{-- Notes --}}
                                <div class="modal-field">
                                    <label>Catatan</label>
                                    <textarea
                                        x-model="form.notes"
                                        :disabled="loading"
                                        rows="2"
                                        placeholder="Contoh: PO #1234, retur pelanggan, koreksi stok (opsional)"
                                        @input="delete errors.notes"
                                    ></textarea>
                                    <template x-if="errors.notes">
                                        <span class="modal-field-error" x-text="errors.notes[0]"></span>
                                    </template>
                                </div>

                            </div>
                        </template>

                    </div>{{-- .modal-form-group --}}
                </form>
            </div>{{-- .modal-body --}}

            {{-- ── Footer ─────────────────────────────────────── --}}
            <div class="modal-footer">
                <button
                    class="modal-btn-cancel"
                    type="button"
                    @click="close()"
                    :disabled="loading"
                >
                    Batal
                </button>

                <button
                    class="modal-btn-submit"
                    :class="{ 'is-delete': mode === 'delete' }"
                    type="submit"
                    form="movement-form"
                    :disabled="loading"
                >
                    <template x-if="loading">
                        <span style="display:inline-flex;align-items:center;gap:6px;">
                            <svg style="width:14px;height:14px;animation:spin .6s linear infinite;"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path style="opacity:.75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                            Menyimpan...
                        </span>
                    </template>
                    <template x-if="!loading">
                        <span x-text="mode === 'delete' ? 'Ya, Hapus' : 'Simpan Transaksi'"></span>
                    </template>
                </button>
            </div>

        </div>{{-- .modal-box --}}
    </div>{{-- .modal-backdrop --}}

</div>{{-- modal x-data --}}

@endsection

@push('scripts')
<script>
// spin keyframe (untuk loading spinner di button)
if (!document.getElementById('spin-kf')) {
    const s = document.createElement('style');
    s.id = 'spin-kf';
    s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(s);
}
</script>
@endpush