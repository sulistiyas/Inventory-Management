@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')

<div
    x-data="modal({
        endpoint: '/suppliers',
        createTitle: 'Tambah Supplier',
        editTitle: 'Edit Supplier',
        deleteTitle: 'Hapus Supplier',
        defaultForm: () => ({
            name: '',
            phone: '',
            email: '',
            city: '',
            address: '',
            is_active: true
        })
    })"
    @open-edit.window="openEdit($event.detail)"
    @open-delete.window="openDelete($event.detail)"
>

    <div
        x-data="datatable({
            apiEndpoint: '{{ route('suppliers.list') }}',
            columns: [
                { key: 'name',      label: 'Supplier Name' },
                { key: 'phone',     label: 'Phone'         },
                { key: 'email',     label: 'Email'         },
                { key: 'city',      label: 'City'          },
                { key: 'is_active', label: 'Status'        }
            ]
        })"
        class="datatable datatable--supplier"
    >

        {{-- HEADER --}}
        <div class="datatable-header">
            <h2>Supplier List</h2>

            <div class="datatable-search-wrap">
                <input type="text" x-model="search" placeholder="Search suppliers...">
            </div>

            <button class="datatable-add-btn" @click="openCreate()">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Supplier
            </button>
        </div>

        {{-- TABLE CARD --}}
        <div class="datatable-card">
            <div class="datatable-table-wrap">
            <table class="datatable-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Supplier Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    {{-- Loading --}}
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="datatable-loading">Loading...</td>
                        </tr>
                    </template>

                    {{-- Empty --}}
                    <template x-if="!loading && data.length === 0">
                        <tr>
                            <td colspan="7" class="datatable-empty">
                                <span class="datatable-empty-icon"></span>
                                <span class="datatable-empty-text">Data tidak ditemukan</span>
                                <span class="datatable-empty-sub">Coba ubah kata kunci pencarian Anda.</span>
                            </td>
                        </tr>
                    </template>

                    {{-- Rows --}}
                    <template x-for="(item, index) in data" :key="item.id">
                        <tr>
                            <td x-text="numberStart + index + 1"></td>
                            <td>
                                <span style="font-weight:600;color:var(--text-primary,#0F172A)" x-text="item.name"></span>
                            </td>
                            <td x-text="item.phone ?? '-'"></td>
                            <td x-text="item.email ?? '-'"></td>
                            <td x-text="item.city ?? '-'"></td>

                            {{-- Status badge --}}
                            <td>
                                <span
                                    x-text="item.is_active ? 'Active' : 'Inactive'"
                                    :style="item.is_active
                                        ? 'background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7'
                                        : 'background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5'"
                                    style="display:inline-flex;align-items:center;gap:0.3rem;
                                           padding:0.2rem 0.625rem;border-radius:999px;
                                           font-size:0.72rem;font-weight:700;letter-spacing:0.03em;"
                                >
                                </span>
                            </td>

                            <td>
                                <button
                                    class="dt-btn dt-btn-edit"
                                    @click="$dispatch('open-edit', item)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    Edit
                                </button>

                                <button
                                    class="dt-btn dt-btn-delete"
                                    @click="$dispatch('open-delete', item)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                        <path d="M10 11v6M14 11v6"></path>
                                    </svg>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </template>

                </tbody>
            </table>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="datatable-pagination">
            <button @click="changePage(page - 1)" :disabled="page <= 1">← Prev</button>

            <template x-for="p in lastPage" :key="p">
                <button
                    @click="changePage(p)"
                    :class="{ 'active': p === page }"
                    x-text="p">
                </button>
            </template>

            <button @click="changePage(page + 1)" :disabled="page >= lastPage">Next →</button>
        </div>

    </div>{{-- /datatable --}}


    {{-- ════════════════════════════════════════════════════
         MODAL FORM
         ════════════════════════════════════════════════════ --}}

    <x-modal>
        <div class="modal-form-group">

            <div class="modal-field">
                <label>Supplier Name <span class="modal-required">*</span></label>
                <input type="text" x-model="form.name" placeholder="Nama supplier...">
                <template x-if="errors.name">
                    <span class="modal-field-error" x-text="errors.name[0]"></span>
                </template>
            </div>

            <div class="modal-field-row">
                <div>
                    <label>Phone <span class="modal-required">*</span></label>
                    <input type="text" x-model="form.phone" placeholder="08xx-xxxx-xxxx">
                    <template x-if="errors.phone">
                        <span class="modal-field-error" x-text="errors.phone[0]"></span>
                    </template>
                </div>
                <div>
                    <label>City</label>
                    <input type="text" x-model="form.city" placeholder="Kota...">
                    <template x-if="errors.city">
                        <span class="modal-field-error" x-text="errors.city[0]"></span>
                    </template>
                </div>
            </div>

            <div class="modal-field">
                <label>Email</label>
                <input type="email" x-model="form.email" placeholder="email@supplier.com">
                <template x-if="errors.email">
                    <span class="modal-field-error" x-text="errors.email[0]"></span>
                </template>
            </div>

            <div class="modal-field">
                <label>Address</label>
                <textarea x-model="form.address" rows="2" placeholder="Alamat lengkap..."></textarea>
                <template x-if="errors.address">
                    <span class="modal-field-error" x-text="errors.address[0]"></span>
                </template>
            </div>

            <div class="modal-toggle-row">
                <span class="modal-toggle-label">Status</span>
                <button
                    type="button"
                    class="modal-toggle"
                    :class="{ 'modal-toggle--on': form.is_active }"
                    @click="form.is_active = !form.is_active"
                    role="switch"
                    :aria-checked="String(form.is_active)"
                >
                    <span class="modal-toggle__thumb"></span>
                </button>
                <span
                    class="modal-toggle-text"
                    :class="form.is_active ? 'modal-toggle-text--on' : 'modal-toggle-text--off'"
                    x-text="form.is_active ? 'Active' : 'Inactive'"
                ></span>
            </div>

        </div>
    </x-modal>

</div>{{-- /modal wrapper --}}

@endsection