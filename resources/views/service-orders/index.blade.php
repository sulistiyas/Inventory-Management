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
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="modal-box" style="max-width:680px;" @click.stop>

                <div class="modal-header">
                    <h3 class="modal-title">🔧 Buat Work Order</h3>
                    <button class="modal-close-btn" @click="closeForm()" type="button"></button>
                </div>

                <div class="modal-body">
                    <form @submit.prevent="submitForm()">
                    <div class="modal-form-group">

                        {{-- Global error --}}
                        <template x-if="globalError">
                            <div style="background:var(--danger-bg);border:1px solid var(--danger);color:var(--danger);padding:10px 14px;border-radius:var(--radius-sm);font-size:.875rem;margin-bottom:1rem;" x-text="globalError"></div>
                        </template>

                        {{-- ── Pelanggan ──────────────────────────────────── --}}
                        <div class="modal-field" style="position:relative;">
                            <label>Pelanggan <span class="modal-required">*</span></label>
                            <input
                                type="text"
                                x-model="customerSearch"
                                @input="filterCustomers()"
                                @focus="if(customerSearch && !selectedCustomer) filterCustomers()"
                                @blur.debounce.200ms="if(selectedCustomer) filteredCustomers=[]"
                                placeholder="Cari nama, HP, atau plat kendaraan..."
                                autocomplete="off">
                            <template x-if="errors.customer_id">
                                <span class="modal-field-error" x-text="errors.customer_id[0]"></span>
                            </template>

                            {{-- Dropdown pelanggan --}}
                            <template x-if="filteredCustomers.length && customerSearch.length > 0 && !selectedCustomer">
                                <div style="position:absolute;z-index:100;top:100%;left:0;right:0;background:var(--bg-card);border:1.5px solid var(--accent);border-radius:var(--radius-md);max-height:200px;overflow-y:auto;box-shadow:var(--shadow-md);margin-top:2px;">
                                    <template x-for="c in filteredCustomers.slice(0,8)" :key="c.id">
                                        <div
                                            style="padding:10px 14px;cursor:pointer;transition:background var(--transition);border-bottom:1px solid var(--border);"
                                            @mousedown.prevent="selectCustomer(c)"
                                            @mouseover="$el.style.background='var(--accent-light)'"
                                            @mouseleave="$el.style.background=''">
                                            <div style="font-weight:600;font-size:.875rem;color:var(--text-primary);" x-text="c.name"></div>
                                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:1px;font-family:var(--font-mono);" x-text="[c.vehicle_plate, c.phone].filter(Boolean).join(' · ')"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Badge pelanggan terpilih --}}
                            <template x-if="selectedCustomer">
                                <div style="display:flex;align-items:center;gap:8px;margin-top:6px;padding:8px 12px;background:var(--accent-light);border-radius:var(--radius-sm);border:1px solid var(--accent);">
                                    <div style="flex:1;">
                                        <span style="font-weight:600;font-size:.8125rem;color:#451a03;" x-text="selectedCustomer.name"></span>
                                        <span style="font-size:.75rem;color:#92400E;margin-left:8px;font-family:var(--font-mono);" x-text="selectedCustomer.vehicle_plate ?? ''"></span>
                                    </div>
                                    <button type="button" @click="selectedCustomer=null; form.customer_id=''; form.vehicle_plate=''; form.vehicle_type=''; customerSearch=''; filteredCustomers=allCustomers;"
                                        style="background:none;border:none;cursor:pointer;color:#92400E;font-size:1rem;line-height:1;padding:0;">✕</button>
                                </div>
                            </template>
                        </div>

                        {{-- ── Plat & Tipe Kendaraan ──────────────────────── --}}
                        <div class="modal-field-row">
                            <div>
                                <label>Plat Kendaraan <span class="modal-required">*</span></label>
                                <input type="text" x-model="form.vehicle_plate"
                                    placeholder="B 1234 ABC"
                                    style="text-transform:uppercase;font-family:var(--font-mono);">
                                <template x-if="errors.vehicle_plate">
                                    <span class="modal-field-error" x-text="errors.vehicle_plate[0]"></span>
                                </template>
                            </div>
                            <div>
                                <label>Tipe Kendaraan</label>
                                <input type="text" x-model="form.vehicle_type" placeholder="Honda Beat 2020">
                            </div>
                        </div>

                        {{-- ── Keluhan ─────────────────────────────────────── --}}
                        <div class="modal-field">
                            <label>Keluhan <span class="modal-required">*</span></label>
                            <textarea x-model="form.complaint" rows="2" placeholder="Deskripsi keluhan pelanggan..."></textarea>
                            <template x-if="errors.complaint">
                                <span class="modal-field-error" x-text="errors.complaint[0]"></span>
                            </template>
                        </div>

                        {{-- ── Diagnosa ────────────────────────────────────── --}}
                        <div class="modal-field">
                            <label>Diagnosa</label>
                            <textarea x-model="form.diagnosis" rows="2" placeholder="Diagnosa mekanik (opsional)..."></textarea>
                        </div>

                        {{-- ── Biaya Jasa & Diskon ─────────────────────────── --}}
                        <div class="modal-field-row">
                            <div>
                                <label>Biaya Jasa (Rp)</label>
                                <input type="number" x-model="form.service_fee" min="0" placeholder="0" style="font-family:var(--font-mono);">
                            </div>
                            <div>
                                <label>Diskon (Rp)</label>
                                <input type="number" x-model="form.discount" min="0" placeholder="0" style="font-family:var(--font-mono);">
                            </div>
                        </div>

                        {{-- ── Spare Part ──────────────────────────────────── --}}
                        <div class="modal-field">
                            <label>Spare Part</label>

                            {{-- Search spare part --}}
                            <div style="position:relative;">
                                <input
                                    type="text"
                                    x-model="productSearch"
                                    @input="filterProducts()"
                                    placeholder="Cari dan tambah spare part..."
                                    autocomplete="off">

                                <template x-if="filteredProducts.length && productSearch.length > 0">
                                    <div style="position:absolute;z-index:100;top:100%;left:0;right:0;background:var(--bg-card);border:1.5px solid var(--accent);border-radius:var(--radius-md);max-height:200px;overflow-y:auto;box-shadow:var(--shadow-md);margin-top:2px;">
                                        <template x-for="p in filteredProducts.slice(0,8)" :key="p.id">
                                            <div
                                                style="padding:10px 14px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);transition:background var(--transition);"
                                                @mousedown.prevent="addItem(p)"
                                                @mouseover="$el.style.background='var(--accent-light)'"
                                                @mouseleave="$el.style.background=''">
                                                <div>
                                                    <span style="font-weight:600;font-size:.875rem;color:var(--text-primary);" x-text="p.name"></span>
                                                    <span style="font-size:.75rem;color:var(--text-muted);margin-left:6px;font-family:var(--font-mono);" x-text="p.sku"></span>
                                                </div>
                                                <div style="text-align:right;flex-shrink:0;margin-left:12px;">
                                                    <div style="font-family:var(--font-mono);font-size:.8125rem;font-weight:700;color:var(--text-primary);" x-text="'Rp ' + Number(p.price).toLocaleString('id-ID')"></div>
                                                    <div :class="p.stock <= 0 ? 'stock-indicator out' : (p.stock <= 5 ? 'stock-indicator low' : 'stock-indicator ok')"
                                                        style="margin-top:2px;"
                                                        x-text="'Stok: ' + p.stock"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            {{-- Tabel spare part yang ditambahkan --}}
                            <template x-if="items.length > 0">
                                <div style="margin-top:.75rem;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;">
                                    <table style="width:100%;font-size:.8125rem;border-collapse:collapse;">
                                        <thead>
                                            <tr style="background:var(--bg-body);">
                                                <th style="text-align:left;padding:8px 12px;font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Part</th>
                                                <th style="text-align:right;padding:8px 12px;font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Harga</th>
                                                <th style="text-align:center;padding:8px 12px;font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Qty</th>
                                                <th style="text-align:right;padding:8px 12px;font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;">Subtotal</th>
                                                <th style="width:32px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(item, idx) in items" :key="idx">
                                                <tr style="border-top:1px solid var(--border);">
                                                    <td style="padding:8px 12px;">
                                                        <div style="font-weight:600;color:var(--text-primary);" x-text="item.product_name"></div>
                                                        <div style="color:var(--text-muted);font-size:.73rem;font-family:var(--font-mono);" x-text="item.product_sku"></div>
                                                    </td>
                                                    <td style="text-align:right;padding:8px 12px;font-family:var(--font-mono);color:var(--text-secondary);" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></td>
                                                    <td style="text-align:center;padding:8px 12px;">
                                                        <div style="display:flex;align-items:center;gap:0;border:1.5px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;width:104px;margin:0 auto;">
                                                            <button
                                                                type="button"
                                                                @click="item.qty > 1 ? item.qty-- : removeItem(idx)"
                                                                style="width:32px;height:34px;flex-shrink:0;background:var(--bg-body);border:none;border-right:1px solid var(--border);cursor:pointer;font-size:1rem;font-weight:700;color:var(--text-secondary);display:flex;align-items:center;justify-content:center;transition:background var(--transition);"
                                                                @mouseover="$el.style.background='var(--border)'"
                                                                @mouseleave="$el.style.background='var(--bg-body)'">−</button>
                                                            <span
                                                                style="flex:1;text-align:center;font-size:.875rem;font-weight:700;font-family:var(--font-mono);color:var(--text-primary);background:#fff;padding:0 4px;line-height:34px;"
                                                                x-text="item.qty"></span>
                                                            <button
                                                                type="button"
                                                                @click="item.qty++"
                                                                style="width:32px;height:34px;flex-shrink:0;background:var(--bg-body);border:none;border-left:1px solid var(--border);cursor:pointer;font-size:1rem;font-weight:700;color:var(--text-secondary);display:flex;align-items:center;justify-content:center;transition:background var(--transition);"
                                                                @mouseover="$el.style.background='var(--border)'"
                                                                @mouseleave="$el.style.background='var(--bg-body)'">+</button>
                                                        </div>
                                                    </td>
                                                    <td style="text-align:right;padding:8px 12px;font-family:var(--font-mono);font-weight:700;color:var(--text-primary);" x-text="'Rp ' + Number(item.qty * item.price).toLocaleString('id-ID')"></td>
                                                    <td style="padding:8px 6px;text-align:center;">
                                                        <button type="button" @click="removeItem(idx)"
                                                                style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;width:24px;height:24px;border-radius:4px;display:flex;align-items:center;justify-content:center;transition:background var(--transition),color var(--transition);"
                                                                @mouseover="$el.style.background='var(--danger-bg)';$el.style.color='var(--danger)'"
                                                                @mouseleave="$el.style.background='';$el.style.color='var(--text-muted)'">✕</button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot>
                                            <tr style="background:var(--bg-body);border-top:1px solid var(--border);">
                                                <td colspan="3" style="text-align:right;padding:8px 12px;font-size:.8125rem;font-weight:600;color:var(--text-secondary);">Total Parts:</td>
                                                <td style="text-align:right;padding:8px 12px;font-family:var(--font-mono);font-weight:700;color:var(--text-primary);" x-text="'Rp ' + Number(itemsSubtotal).toLocaleString('id-ID')"></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </template>
                        </div>

                        {{-- ── Grand Total ─────────────────────────────────── --}}
                        <div style="background:var(--accent-light);border:1.5px solid var(--accent);border-radius:var(--radius-md);padding:12px 16px;margin-top:1rem;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:600;font-size:.875rem;color:#451a03;">Estimasi Total</span>
                            <span style="font-size:1.125rem;font-weight:800;font-family:var(--font-mono);color:#451a03;" x-text="formatRp(grandTotal)"></span>
                        </div>

                        {{-- ── Catatan ─────────────────────────────────────── --}}
                        <div class="modal-field">
                            <label>Catatan</label>
                            <textarea x-model="form.notes" rows="2" placeholder="Catatan tambahan (opsional)..."></textarea>
                        </div>

                    </div>{{-- modal-form-group --}}
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="modal-btn-cancel" @click="closeForm()" :disabled="formLoading">Batal</button>
                    <button type="button" class="modal-btn-submit" @click="submitForm()" :disabled="formLoading">
                        <template x-if="formLoading"><span>Menyimpan...</span></template>
                        <template x-if="!formLoading"><span>Buat Work Order</span></template>
                    </button>
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
                    <div style="margin-bottom:1.25rem;font-size:.875rem;background:var(--bg-body);padding:10px 14px;border-radius:var(--radius-sm);">
                        <span style="color:var(--text-muted);">Work Order:</span>
                        <strong style="font-family:var(--font-mono);margin-left:8px;" x-text="statusTarget?.order_no"></strong>
                    </div>

                    {{-- 
                        FIX: Tidak pakai x-for — render tiap tombol secara eksplisit
                        berdasarkan status saat ini.
                        
                        Root cause bug: x-for dengan @click="newStatus = opt.value" 
                        menyebabkan Alpine re-evaluate list → destroy+recreate DOM element
                        yang sedang di-click → event tidak selesai diproses → tombol
                        tampak "hilang" lalu muncul lagi tapi state tidak update.
                        
                        Fix: render tombol langsung dengan x-show per status transition,
                        tidak pakai x-for sama sekali.
                    --}}

                    <div style="display:flex;flex-direction:column;gap:.625rem;">

                        {{-- pending → in_progress --}}
                        <template x-if="statusTarget?.status === 'pending'">
                            <div style="display:flex;flex-direction:column;gap:.625rem;">
                                <button
                                    type="button"
                                    @click.prevent="newStatus = 'in_progress'"
                                    :style="newStatus === 'in_progress'
                                        ? 'background:var(--info-bg);color:var(--info);border:2px solid var(--info);font-weight:700;'
                                        : 'background:var(--bg-card);border:2px solid var(--border);color:var(--text-secondary);'"
                                    style="padding:12px 16px;border-radius:var(--radius-sm);font-size:.875rem;cursor:pointer;text-align:left;transition:all var(--transition);font-family:var(--font-body);display:flex;align-items:center;gap:10px;">
                                    <span style="font-size:1.125rem;">🔧</span>
                                    <div>
                                        <div style="font-weight:600;">Mulai Kerjakan</div>
                                        <div style="font-size:.75rem;opacity:.75;">Status berubah ke: Dikerjakan</div>
                                    </div>
                                    <template x-if="newStatus === 'in_progress'">
                                        <span style="margin-left:auto;font-size:1rem;">✓</span>
                                    </template>
                                </button>
                                <button
                                    type="button"
                                    @click.prevent="newStatus = 'cancelled'"
                                    :style="newStatus === 'cancelled'
                                        ? 'background:var(--danger-bg);color:var(--danger);border:2px solid var(--danger);font-weight:700;'
                                        : 'background:var(--bg-card);border:2px solid var(--border);color:var(--text-secondary);'"
                                    style="padding:12px 16px;border-radius:var(--radius-sm);font-size:.875rem;cursor:pointer;text-align:left;transition:all var(--transition);font-family:var(--font-body);display:flex;align-items:center;gap:10px;">
                                    <span style="font-size:1.125rem;">✕</span>
                                    <div>
                                        <div style="font-weight:600;">Batalkan</div>
                                        <div style="font-size:.75rem;opacity:.75;">Status berubah ke: Dibatalkan</div>
                                    </div>
                                    <template x-if="newStatus === 'cancelled'">
                                        <span style="margin-left:auto;font-size:1rem;">✓</span>
                                    </template>
                                </button>
                            </div>
                        </template>

                        {{-- in_progress → done / cancelled --}}
                        <template x-if="statusTarget?.status === 'in_progress'">
                            <div style="display:flex;flex-direction:column;gap:.625rem;">
                                <button
                                    type="button"
                                    @click.prevent="newStatus = 'done'"
                                    :style="newStatus === 'done'
                                        ? 'background:var(--success-bg);color:#065F46;border:2px solid var(--success);font-weight:700;'
                                        : 'background:var(--bg-card);border:2px solid var(--border);color:var(--text-secondary);'"
                                    style="padding:12px 16px;border-radius:var(--radius-sm);font-size:.875rem;cursor:pointer;text-align:left;transition:all var(--transition);font-family:var(--font-body);display:flex;align-items:center;gap:10px;">
                                    <span style="font-size:1.125rem;">✅</span>
                                    <div>
                                        <div style="font-weight:600;">Selesai</div>
                                        <div style="font-size:.75rem;opacity:.75;">Stok spare part akan dikurangi</div>
                                    </div>
                                    <template x-if="newStatus === 'done'">
                                        <span style="margin-left:auto;font-size:1rem;">✓</span>
                                    </template>
                                </button>
                                <button
                                    type="button"
                                    @click.prevent="newStatus = 'cancelled'"
                                    :style="newStatus === 'cancelled'
                                        ? 'background:var(--danger-bg);color:var(--danger);border:2px solid var(--danger);font-weight:700;'
                                        : 'background:var(--bg-card);border:2px solid var(--border);color:var(--text-secondary);'"
                                    style="padding:12px 16px;border-radius:var(--radius-sm);font-size:.875rem;cursor:pointer;text-align:left;transition:all var(--transition);font-family:var(--font-body);display:flex;align-items:center;gap:10px;">
                                    <span style="font-size:1.125rem;">✕</span>
                                    <div>
                                        <div style="font-weight:600;">Batalkan</div>
                                        <div style="font-size:.75rem;opacity:.75;">Status berubah ke: Dibatalkan</div>
                                    </div>
                                    <template x-if="newStatus === 'cancelled'">
                                        <span style="margin-left:auto;font-size:1rem;">✓</span>
                                    </template>
                                </button>
                            </div>
                        </template>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="modal-btn-cancel" @click="closeStatus()" :disabled="statusLoading">
                        Batal
                    </button>
                    <button
                        type="button"
                        class="modal-btn-submit"
                        @click="submitStatus()"
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
