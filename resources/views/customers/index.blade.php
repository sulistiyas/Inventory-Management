@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')

<div
    x-data="customerManager()"
    x-init="init()"
    @edit-customer.window="handleEdit($event.detail)"
    @delete-customer.window="handleDelete($event.detail)"
>

    {{-- ── Modal scope ─────────────────────────────────────────────────── --}}
    <div
        x-data="modal({
            endpoint:     '/customers',
            createTitle:  'Tambah Pelanggan',
            editTitle:    'Edit Pelanggan',
            deleteTitle:  'Hapus Pelanggan',
            defaultForm:  () => ({
                id:            null,
                name:          '',
                phone:         '',
                vehicle_plate: '',
                vehicle_type:  '',
                address:       '',
            })
        })"
        x-init="init()"
        @fill-form.window="
            mode = 'edit';
            title = 'Edit Pelanggan';
            form = { ...$event.detail };
            id = $event.detail.id;
            method = 'PUT';
            errors = {};
            open = true;
        "
        @open-delete.window="openDelete($event.detail)"
    >

        {{-- ── Datatable scope ─────────────────────────────────────────── --}}
        <div
            x-data="datatable({
                apiEndpoint: '{{ route('customers.list') }}',
                columns: [
                    { key: 'name',          label: 'Nama'       },
                    { key: 'phone',         label: 'No. HP'     },
                    { key: 'vehicle_plate', label: 'Plat'       },
                    { key: 'vehicle_type',  label: 'Kendaraan'  },
                    { key: 'created_at',    label: 'Terdaftar'  },
                ]
            })"
            class="datatable"
            @refresh-datatable.window="fetchData()"
        >

            {{-- Header --}}
            <div class="datatable-header">
                <h2>Data Pelanggan</h2>
                <div class="datatable-search-wrap">
                    <input type="text" x-model="search" placeholder="Cari nama, HP, atau plat...">
                </div>
                <button class="datatable-add-btn" @click="openCreate()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Tambah Pelanggan
                </button>
            </div>

            {{-- Table --}}
            <div class="datatable-card">
                <div class="datatable-table-wrap">
                    <table class="datatable-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Plat</th>
                                <th>Kendaraan</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                            <template x-if="loading">
                                <tr><td colspan="7" class="datatable-loading">Memuat data...</td></tr>
                            </template>

                            <template x-if="!loading && data.length === 0">
                                <tr>
                                    <td colspan="7" class="datatable-empty">
                                        <span class="datatable-empty-icon"></span>
                                        <span class="datatable-empty-text">Belum ada pelanggan</span>
                                        <span class="datatable-empty-sub">Klik "Tambah Pelanggan" untuk memulai.</span>
                                    </td>
                                </tr>
                            </template>

                            <template x-for="(item, index) in data" :key="item.id">
                                <tr>
                                    <td x-text="numberStart + index + 1"></td>
                                    <td>
                                        <div style="font-weight:600;font-size:.875rem;" x-text="item.name"></div>
                                    </td>
                                    <td style="font-size:.8125rem;" x-text="item.phone || '—'"></td>
                                    <td>
                                        <template x-if="item.vehicle_plate">
                                            <span style="font-family:var(--font-mono);font-weight:600;font-size:.8125rem;background:var(--bg-subtle);padding:2px 8px;border-radius:4px;" x-text="item.vehicle_plate"></span>
                                        </template>
                                        <template x-if="!item.vehicle_plate">
                                            <span style="color:var(--text-muted);">—</span>
                                        </template>
                                    </td>
                                    <td style="font-size:.8125rem;" x-text="item.vehicle_type || '—'"></td>
                                    <td style="font-size:.8125rem;color:var(--text-muted);" x-text="item.created_at"></td>
                                    <td>
                                        <button class="dt-btn dt-btn-edit"
                                            @click="$dispatch('edit-customer', item)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        <button class="dt-btn dt-btn-delete"
                                            @click="$dispatch('delete-customer', item)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>
                                            Hapus
                                        </button>
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

        {{-- ── Modal form ────────────────────────────────────────────────── --}}
        <x-modal>
            <div class="modal-form-group">

                <template x-if="mode === 'delete'">
                    <div class="modal-delete-confirm">
                        <div class="modal-delete-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="2" stroke="currentColor" style="width:28px;height:28px;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                        </div>
                        <span class="modal-delete-text">Hapus pelanggan ini?</span>
                        <span class="modal-delete-sub" x-text="'Nama: ' + (form.name ?? '')"></span>
                    </div>
                </template>

                <template x-if="mode !== 'delete'">
                    <div>

                        <div class="modal-field">
                            <label>Nama <span class="modal-required">*</span></label>
                            <input type="text" x-model="form.name" placeholder="Nama lengkap pelanggan">
                            <template x-if="errors.name">
                                <span class="modal-field-error" x-text="errors.name[0]"></span>
                            </template>
                        </div>

                        <div class="modal-field-row">
                            <div>
                                <label>No. HP</label>
                                <input type="text" x-model="form.phone" placeholder="08xx...">
                                <template x-if="errors.phone">
                                    <span class="modal-field-error" x-text="errors.phone[0]"></span>
                                </template>
                            </div>
                            <div>
                                <label>Plat Kendaraan</label>
                                <input type="text" x-model="form.vehicle_plate" placeholder="B 1234 ABC"
                                       style="text-transform:uppercase;">
                                <template x-if="errors.vehicle_plate">
                                    <span class="modal-field-error" x-text="errors.vehicle_plate[0]"></span>
                                </template>
                            </div>
                        </div>

                        <div class="modal-field">
                            <label>Tipe Kendaraan</label>
                            <input type="text" x-model="form.vehicle_type" placeholder="Contoh: Honda Beat 2020">
                            <template x-if="errors.vehicle_type">
                                <span class="modal-field-error" x-text="errors.vehicle_type[0]"></span>
                            </template>
                        </div>

                        <div class="modal-field">
                            <label>Alamat</label>
                            <textarea x-model="form.address" rows="2" placeholder="Alamat pelanggan (opsional)"></textarea>
                        </div>

                    </div>
                </template>

            </div>
        </x-modal>

    </div>{{-- modal scope --}}

</div>{{-- customerManager --}}

@endsection
