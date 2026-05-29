@extends('layouts.app')

@section('title', 'Work Order Servis')

@section('content')

<div
    x-data="serviceOrder()"
    x-init="init()"
    @keydown.escape.window="closeForm(); closeDetail(); closeStatus()"
>

    {{-- ── Datatable scope ──────────────────────────────────────────────── --}}
    <div
        x-data="datatable({
            apiEndpoint: '{{ route('service-orders.list') }}',
            perPage: 10,
            filters: { status: '', date_from: '', date_to: '' },
            columns: [],
        })"
        class="datatable"
        @refresh-datatable.window="fetchData()"
    >

        {{-- Header --}}
        <div class="datatable-header">
            <h2>Work Order Servis</h2>

            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">

                <div class="datatable-search-wrap">
                    <input type="text" x-model="search" placeholder="Cari no. WO, plat, nama...">
                </div>

                <select style="height:36px;padding:0 10px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;"
                    x-model="filters.status" @change="page=1;fetchData()">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">Dikerjakan</option>
                    <option value="done">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>

                <input type="date" x-model="filters.date_from"
                    @change="page=1;fetchData()"
                    style="height:36px;padding:0 8px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">

                <input type="date" x-model="filters.date_to"
                    @change="page=1;fetchData()"
                    style="height:36px;padding:0 8px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">

                <button class="datatable-add-btn" @click="openCreate()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Buat Work Order
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="datatable-card">
            <div class="datatable-table-wrap">
                <table class="datatable-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No. WO</th>
                            <th>Pelanggan</th>
                            <th>Plat</th>
                            <th>Keluhan</th>
                            <th>Status</th>
                            <th>Mekanik</th>
                            <th>Jasa</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                        <template x-if="loading">
                            <tr><td colspan="10" class="datatable-loading">Memuat data...</td></tr>
                        </template>

                        <template x-if="!loading && data.length === 0">
                            <tr>
                                <td colspan="10" class="datatable-empty">
                                    <span class="datatable-empty-icon"></span>
                                    <span class="datatable-empty-text">Belum ada work order</span>
                                    <span class="datatable-empty-sub">Klik "Buat Work Order" untuk memulai.</span>
                                </td>
                            </tr>
                        </template>

                        <template x-for="(item, index) in data" :key="item.id">
                            <tr>
                                <td x-text="numberStart + index + 1"></td>
                                <td>
                                    <span style="font-family:var(--font-mono);font-size:.8125rem;font-weight:600;" x-text="item.order_no"></span>
                                </td>
                                <td style="font-weight:500;" x-text="item.customer_name"></td>
                                <td>
                                    <span style="font-family:var(--font-mono);font-size:.8125rem;background:var(--bg-subtle);padding:2px 8px;border-radius:4px;" x-text="item.vehicle_plate"></span>
                                </td>
                                <td style="font-size:.8125rem;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="item.complaint"></td>
                                <td>
                                    <span class="badge"
                                        :class="statusClass(item.status)"
                                        style="padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;"
                                        x-text="statusLabel(item.status)">
                                    </span>
                                </td>
                                <td style="font-size:.8125rem;" x-text="item.handler || '—'"></td>
                                <td style="font-size:.8125rem;font-family:var(--font-mono);"
                                    x-text="'Rp ' + Number(item.service_fee).toLocaleString('id-ID')"></td>
                                <td style="font-size:.8125rem;color:var(--text-muted);white-space:nowrap;" x-text="item.created_at"></td>
                                <td style="white-space:nowrap;">
                                    <button class="dt-btn dt-btn-edit" @click="openDetail(item.id)">Detail</button>
                                    <template x-if="item.status !== 'done' && item.status !== 'cancelled'">
                                        <button class="dt-btn" style="background:var(--warning-bg,#fef3c7);color:var(--warning,#d97706);border:1px solid var(--warning-border,#fde68a);"
                                            @click="openStatusModal(item)">
                                            Update Status
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>

                    </tbody>
                </table>
            </div>

            <template x-if="lastPage > 1">
                <div class="datatable-pagination">
                    <button @click="changePage(page - 1)" :disabled="page <= 1">‹</button>
                    <template x-for="p in lastPage" :key="p">
                        <button :class="{ 'active': p === page }" @click="changePage(p)" x-text="p"></button>
                    </template>
                    <button @click="changePage(page + 1)" :disabled="page >= lastPage">›</button>
                </div>
            </template>
        </div>{{-- datatable-card --}}

    </div>{{-- datatable --}}


    {{-- ================================================================
         MODAL: Buat Work Order
    ================================================================ --}}
    <template x-if="formOpen">
        <div class="modal-backdrop" @click.self="closeForm()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="modal-box" style="max-width:680px;" @click.stop>

                <div class="modal-header">
                    <h3 class="modal-title">Buat Work Order</h3>
                    <button class="modal-close-btn" @click="closeForm()" type="button"></button>
                </div>

                <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
                    <form @submit.prevent="submitForm()">

                        {{-- Global error --}}
                        <template x-if="globalError">
                            <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:6px;font-size:.875rem;margin-bottom:1rem;" x-text="globalError"></div>
                        </template>

                        {{-- Pelanggan --}}
                        <div class="modal-field" style="position:relative;">
                            <label>Pelanggan <span class="modal-required">*</span></label>
                            <input type="text" x-model="customerSearch"
                                   @input="filterCustomers()"
                                   placeholder="Cari nama, HP, atau plat kendaraan..."
                                   autocomplete="off">
                            <template x-if="errors.customer_id">
                                <span class="modal-field-error" x-text="errors.customer_id[0]"></span>
                            </template>
                            <template x-if="filteredCustomers.length && customerSearch.length > 0 && !selectedCustomer">
                                <div style="position:absolute;z-index:50;top:100%;left:0;right:0;background:var(--bg);border:1px solid var(--border);border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);">
                                    <template x-for="c in filteredCustomers.slice(0,8)" :key="c.id">
                                        <div style="padding:8px 12px;cursor:pointer;font-size:.875rem;"
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

                        <div class="modal-field-row">
                            <div>
                                <label>Plat Kendaraan <span class="modal-required">*</span></label>
                                <input type="text" x-model="form.vehicle_plate"
                                       placeholder="B 1234 ABC" style="text-transform:uppercase;">
                                <template x-if="errors.vehicle_plate">
                                    <span class="modal-field-error" x-text="errors.vehicle_plate[0]"></span>
                                </template>
                            </div>
                            <div>
                                <label>Tipe Kendaraan</label>
                                <input type="text" x-model="form.vehicle_type" placeholder="Honda Beat 2020">
                            </div>
                        </div>

                        <div class="modal-field">
                            <label>Keluhan <span class="modal-required">*</span></label>
                            <textarea x-model="form.complaint" rows="2" placeholder="Deskripsi keluhan pelanggan..."></textarea>
                            <template x-if="errors.complaint">
                                <span class="modal-field-error" x-text="errors.complaint[0]"></span>
                            </template>
                        </div>

                        <div class="modal-field">
                            <label>Diagnosa</label>
                            <textarea x-model="form.diagnosis" rows="2" placeholder="Diagnosa mekanik (opsional)..."></textarea>
                        </div>

                        <div class="modal-field-row">
                            <div>
                                <label>Biaya Jasa (Rp)</label>
                                <input type="number" x-model="form.service_fee" min="0" placeholder="0">
                            </div>
                            <div>
                                <label>Diskon (Rp)</label>
                                <input type="number" x-model="form.discount" min="0" placeholder="0">
                            </div>
                        </div>

                        {{-- Spare Parts --}}
                        <div class="modal-field" style="margin-top:1rem;">
                            <label>Spare Part</label>

                            <div style="position:relative;margin-bottom:.5rem;">
                                <input type="text" x-model="productSearch"
                                       @input="filterProducts()"
                                       placeholder="Cari dan tambah spare part..."
                                       autocomplete="off">
                                <template x-if="filteredProducts.length && productSearch.length > 0">
                                    <div style="position:absolute;z-index:50;top:100%;left:0;right:0;background:var(--bg);border:1px solid var(--border);border-radius:6px;max-height:180px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);">
                                        <template x-for="p in filteredProducts.slice(0,8)" :key="p.id">
                                            <div style="padding:8px 12px;cursor:pointer;font-size:.875rem;display:flex;justify-content:space-between;align-items:center;"
                                                 @mousedown.prevent="addItem(p)"
                                                 @mouseover="$el.style.background='var(--bg-subtle)'"
                                                 @mouseleave="$el.style.background=''">
                                                <div>
                                                    <span style="font-weight:500;" x-text="p.name"></span>
                                                    <span style="font-size:.75rem;color:var(--text-muted);margin-left:6px;" x-text="p.sku"></span>
                                                </div>
                                                <span style="font-size:.75rem;color:var(--text-muted);" x-text="'Stok: ' + p.stock"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <template x-if="items.length > 0">
                                <table style="width:100%;font-size:.8125rem;border-collapse:collapse;">
                                    <thead>
                                        <tr style="border-bottom:1px solid var(--border);">
                                            <th style="text-align:left;padding:4px 6px;">Part</th>
                                            <th style="text-align:right;padding:4px 6px;">Harga</th>
                                            <th style="text-align:center;padding:4px 6px;">Qty</th>
                                            <th style="text-align:right;padding:4px 6px;">Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, idx) in items" :key="idx">
                                            <tr style="border-bottom:1px solid var(--border-light,#f3f4f6);">
                                                <td style="padding:6px;">
                                                    <div x-text="item.product_name"></div>
                                                    <div style="color:var(--text-muted);font-size:.75rem;" x-text="item.product_sku"></div>
                                                </td>
                                                <td style="text-align:right;padding:6px;font-family:var(--font-mono);" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></td>
                                                <td style="text-align:center;padding:6px;">
                                                    <input type="number" x-model.number="item.qty" min="1"
                                                           style="width:60px;text-align:center;padding:2px 6px;border:1px solid var(--border);border-radius:4px;">
                                                </td>
                                                <td style="text-align:right;padding:6px;font-family:var(--font-mono);font-weight:600;" x-text="'Rp ' + Number(item.qty * item.price).toLocaleString('id-ID')"></td>
                                                <td style="padding:6px;">
                                                    <button type="button" @click="removeItem(idx)"
                                                            style="color:var(--danger,#dc2626);background:none;border:none;cursor:pointer;font-size:1rem;">✕</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" style="text-align:right;padding:6px;font-weight:600;">Total Parts:</td>
                                            <td style="text-align:right;padding:6px;font-family:var(--font-mono);font-weight:700;" x-text="'Rp ' + Number(itemsSubtotal).toLocaleString('id-ID')"></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </template>
                        </div>

                        {{-- Grand Total --}}
                        <div style="background:var(--bg-subtle);border-radius:8px;padding:12px 16px;margin-top:1rem;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:600;">Estimasi Total</span>
                            <span style="font-size:1.125rem;font-weight:700;font-family:var(--font-mono);" x-text="formatRp(grandTotal)"></span>
                        </div>

                        <div class="modal-field" style="margin-top:1rem;">
                            <label>Catatan</label>
                            <textarea x-model="form.notes" rows="2" placeholder="Catatan tambahan (opsional)..."></textarea>
                        </div>

                        <div class="modal-footer" style="padding:0;margin-top:1.25rem;">
                            <button type="button" class="modal-btn-cancel" @click="closeForm()" :disabled="formLoading">Batal</button>
                            <button type="submit" class="modal-btn-submit" :disabled="formLoading">
                                <template x-if="formLoading"><span>Menyimpan...</span></template>
                                <template x-if="!formLoading"><span>Buat Work Order</span></template>
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </template>


    {{-- ================================================================
         MODAL: Detail Work Order
    ================================================================ --}}
    <template x-if="detailOpen">
        <div class="modal-backdrop" @click.self="closeDetail()">
            <div class="modal-box" style="max-width:600px;" @click.stop>

                <div class="modal-header">
                    <h3 class="modal-title">Detail Work Order</h3>
                    <button class="modal-close-btn" @click="closeDetail()" type="button"></button>
                </div>

                <div class="modal-body">
                    <template x-if="detailLoading">
                        <div style="text-align:center;padding:2rem;color:var(--text-muted);">Memuat...</div>
                    </template>

                    <template x-if="!detailLoading && detailData">
                        <div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;font-size:.875rem;margin-bottom:1rem;">
                                <div><span style="color:var(--text-muted);">No. WO</span><br><strong style="font-family:var(--font-mono);" x-text="detailData.order_no"></strong></div>
                                <div><span style="color:var(--text-muted);">Status</span><br>
                                    <span class="badge" :class="statusClass(detailData.status)"
                                          style="padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;"
                                          x-text="statusLabel(detailData.status)"></span>
                                </div>
                                <div><span style="color:var(--text-muted);">Pelanggan</span><br><span x-text="detailData.customer?.name ?? '—'"></span></div>
                                <div><span style="color:var(--text-muted);">No. HP</span><br><span x-text="detailData.customer?.phone ?? '—'"></span></div>
                                <div><span style="color:var(--text-muted);">Plat</span><br><strong style="font-family:var(--font-mono);" x-text="detailData.vehicle_plate"></strong></div>
                                <div><span style="color:var(--text-muted);">Kendaraan</span><br><span x-text="detailData.vehicle_type ?? '—'"></span></div>
                                <div><span style="color:var(--text-muted);">Mekanik</span><br><span x-text="detailData.handler?.name ?? '—'"></span></div>
                                <div><span style="color:var(--text-muted);">Selesai</span><br><span x-text="detailData.finished_at ?? '—'"></span></div>
                            </div>

                            <div style="margin-bottom:.75rem;">
                                <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:2px;">Keluhan</div>
                                <div style="font-size:.875rem;" x-text="detailData.complaint"></div>
                            </div>

                            <template x-if="detailData.diagnosis">
                                <div style="margin-bottom:.75rem;">
                                    <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:2px;">Diagnosa</div>
                                    <div style="font-size:.875rem;" x-text="detailData.diagnosis"></div>
                                </div>
                            </template>

                            <template x-if="detailData.items?.length">
                                <div style="margin-bottom:.75rem;">
                                    <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:6px;">Spare Part</div>
                                    <table style="width:100%;font-size:.8125rem;border-collapse:collapse;">
                                        <thead>
                                            <tr style="border-bottom:1px solid var(--border);">
                                                <th style="text-align:left;padding:4px 0;">Part</th>
                                                <th style="text-align:right;padding:4px 0;">Qty</th>
                                                <th style="text-align:right;padding:4px 0;">Harga</th>
                                                <th style="text-align:right;padding:4px 0;">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="item in detailData.items" :key="item.product_id">
                                                <tr style="border-bottom:1px solid var(--border-light,#f3f4f6);">
                                                    <td style="padding:4px 0;" x-text="item.product_name"></td>
                                                    <td style="text-align:right;padding:4px 0;" x-text="item.qty"></td>
                                                    <td style="text-align:right;padding:4px 0;font-family:var(--font-mono);" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></td>
                                                    <td style="text-align:right;padding:4px 0;font-family:var(--font-mono);font-weight:600;" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <div style="background:var(--bg-subtle);border-radius:8px;padding:12px 16px;font-size:.875rem;">
                                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                    <span style="color:var(--text-muted);">Biaya Jasa</span>
                                    <span style="font-family:var(--font-mono);" x-text="'Rp ' + Number(detailData.service_fee).toLocaleString('id-ID')"></span>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                    <span style="color:var(--text-muted);">Diskon</span>
                                    <span style="font-family:var(--font-mono);" x-text="'- Rp ' + Number(detailData.discount).toLocaleString('id-ID')"></span>
                                </div>
                                <div style="display:flex;justify-content:space-between;font-weight:700;margin-top:6px;padding-top:6px;border-top:1px solid var(--border);">
                                    <span>Total</span>
                                    <span style="font-family:var(--font-mono);font-size:1rem;" x-text="'Rp ' + Number(detailData.grand_total).toLocaleString('id-ID')"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

            </div>
        </div>
    </template>


    {{-- ================================================================
         MODAL: Update Status
    ================================================================ --}}
    <template x-if="statusOpen">
        <div class="modal-backdrop" @click.self="closeStatus()">
            <div class="modal-box" style="max-width:400px;" @click.stop>

                <div class="modal-header">
                    <h3 class="modal-title">Update Status</h3>
                    <button class="modal-close-btn" @click="closeStatus()" type="button"></button>
                </div>

                <div class="modal-body">
                    <div style="margin-bottom:1rem;font-size:.875rem;">
                        <span style="color:var(--text-muted);">Work Order:</span>
                        <strong style="font-family:var(--font-mono);margin-left:6px;" x-text="statusTarget?.order_no"></strong>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:.5rem;">
                        <template x-for="opt in availableStatuses(statusTarget?.status)" :key="opt.value">
                            <button
                                @click="newStatus = opt.value"
                                :style="newStatus === opt.value
                                    ? 'background:var(--primary);color:#fff;border:2px solid var(--primary);'
                                    : 'background:var(--bg);border:2px solid var(--border);color:var(--text);'"
                                style="padding:10px 16px;border-radius:8px;font-size:.875rem;font-weight:500;cursor:pointer;text-align:left;transition:.15s;"
                                x-text="opt.label">
                            </button>
                        </template>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="modal-btn-cancel" @click="closeStatus()" :disabled="statusLoading">Batal</button>
                    <button type="button" class="modal-btn-submit" @click="submitStatus()"
                            :disabled="statusLoading || !newStatus">
                        <template x-if="statusLoading"><span>Menyimpan...</span></template>
                        <template x-if="!statusLoading"><span>Simpan Status</span></template>
                    </button>
                </div>

            </div>
        </div>
    </template>

</div>{{-- serviceOrder --}}

@endsection
