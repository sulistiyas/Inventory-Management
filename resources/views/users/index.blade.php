@extends('layouts.app')

@section('title', 'User Management')
@section('breadcrumb', 'User Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/datatable.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/modal.css') }}">
@endpush

@section('content')

{{-- ── Datatable wrapper ──────────────────────────────────────────── --}}
<div
    x-data="datatable({
        apiEndpoint: '{{ route('users.list') }}',
        perPage: 10,
        filters: {
            role: ''
        }
    })">

    {{-- ── Page Header ─────────────────────────────────────────────── --}}
    <div class="page-header" style="margin-bottom:1.25rem;">
        <div>
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">
                Total:
                <strong>{{ $stats['total'] }}</strong> user
                &nbsp;·&nbsp;
                <strong>{{ $stats['admin'] }}</strong> admin
                &nbsp;·&nbsp;
                <strong>{{ $stats['staff'] }}</strong> staff
            </p>
        </div>
        <button
            class="datatable-add-btn"
            @click="$dispatch('open-create-user')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke-width="2.5" stroke="currentColor" style="width:14px;height:14px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah User
        </button>
    </div>

    {{-- ── Filter bar ───────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;">

        {{-- Search --}}
        <div class="datatable-search-wrap" style="flex:1;min-width:200px;">
            <input
                type="text"
                x-model="search"
                placeholder="Cari nama atau email..."
                autocomplete="off"
            />
        </div>

        {{-- Role filter --}}
        <select
            x-model="filters.role"
            style="padding:.5rem 2rem .5rem .75rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.875rem;background:var(--bg-card);font-family:var(--font-body);outline:none;appearance:none;background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2394A3B8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 9l6 6 6-6'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 8px center;background-size:16px;"
        >
            <option value="">Semua Role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select>

    </div>

    {{-- ── Datatable card ───────────────────────────────────────────── --}}
    <div class="datatable-card">
        <div class="datatable-table-wrap">
            <table class="datatable-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- Loading --}}
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="datatable-loading">Memuat data...</td>
                        </tr>
                    </template>

                    {{-- Empty --}}
                    <template x-if="!loading && data.length === 0">
                        <tr>
                            <td colspan="7" class="datatable-empty">
                                <span class="datatable-empty-icon"></span>
                                <span class="datatable-empty-text">Belum ada user</span>
                                <span class="datatable-empty-sub">Klik "Tambah User" untuk memulai</span>
                            </td>
                        </tr>
                    </template>

                    {{-- Rows --}}
                    <template x-if="!loading && data.length > 0">
                        <template x-for="(row, i) in data" :key="row.id">
                            <tr :style="!row.is_active ? 'opacity:.55' : ''">

                                {{-- No --}}
                                <td x-text="numberStart + i + 1"></td>

                                {{-- Avatar + Nama --}}
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;background:linear-gradient(135deg,var(--accent) 0%,#92400E 100%);"
                                             x-text="row.name.charAt(0).toUpperCase()">
                                        </div>
                                        <div>
                                            <div style="font-weight:600;font-size:.875rem;" x-text="row.name"></div>
                                            <div x-show="row.is_me"
                                                 style="font-size:.7rem;color:var(--accent);font-weight:600;">
                                                (Anda)
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Email --}}
                                <td style="font-size:.8125rem;color:var(--text-secondary);"
                                    x-text="row.email"></td>

                                {{-- Role badge --}}
                                <td>
                                    <span
                                        class="badge"
                                        :class="row.role === 'admin' ? 'badge-info' : 'badge-secondary'"
                                        x-text="row.role_label"
                                    ></span>
                                </td>

                                {{-- Status toggle --}}
                                <td>
                                    <button
                                        @click="toggle(`/users/toggle-active/${row.id}`, row)"
                                        :disabled="row.is_me"
                                        :title="row.is_me ? 'Tidak dapat mengubah akun sendiri' : (row.is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan')"
                                        style="border:none;background:none;cursor:pointer;padding:0;"
                                        :style="row.is_me ? 'cursor:not-allowed;opacity:.5' : ''"
                                    >
                                        <span
                                            class="badge"
                                            :class="row.is_active ? 'badge-success' : 'badge-secondary'"
                                            x-text="row.is_active ? 'Aktif' : 'Nonaktif'"
                                        ></span>
                                    </button>
                                </td>

                                {{-- Date --}}
                                <td style="font-size:.8125rem;color:var(--text-muted);"
                                    x-text="row.created_at"></td>

                                {{-- Actions --}}
                                <td style="text-align:right;white-space:nowrap;">
                                    <button
                                        @click="edit(row)"
                                        class="dt-btn dt-btn-edit"
                                        title="Edit"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button
                                        @click="delete(row)"
                                        class="dt-btn dt-btn-delete"
                                        :disabled="row.is_me"
                                        :style="row.is_me ? 'opacity:.4;cursor:not-allowed' : ''"
                                        title="Hapus"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </td>

                            </tr>
                        </template>
                    </template>

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
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
     MODAL — pakai modal.js
     endpoint: '/users'
     store  → POST /users/store
     update → PUT  /users/update/{id}
     delete → DELETE /users/destroy/{id}
     ================================================================ --}}
<div
    x-data="modal({
        endpoint:    '/users',
        createTitle: 'Tambah User',
        editTitle:   'Edit User',
        deleteTitle: 'Hapus User',
        defaultForm: () => ({
            name:      '',
            email:     '',
            password:  '',
            role:      'staff',
            is_active: true,
        }),
    })"
    x-init="init()"
    @open-create-user.window="openCreate()"
    @keydown.escape.window="close()"
>
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
            {{-- Header --}}
            <div class="modal-header">
                <h3 class="modal-title" x-text="title"></h3>
                <button class="modal-close-btn" @click="close()" type="button" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <form id="user-form" @submit.prevent="submit()">
                    <div class="modal-form-group">

                        {{-- ── DELETE confirm ──────────────────────── --}}
                        <template x-if="mode === 'delete'">
                            <div class="modal-delete-confirm">
                                <div class="modal-delete-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="2" stroke="currentColor" style="width:28px;height:28px;">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                    </svg>
                                </div>
                                <span class="modal-delete-text">Hapus user ini?</span>
                                <span class="modal-delete-sub">
                                    <strong x-text="form.name"></strong> akan dihapus permanen.
                                </span>
                            </div>
                        </template>

                        {{-- ── CREATE / EDIT form ───────────────────── --}}
                        <template x-if="mode !== 'delete'">
                            <div>

                                {{-- Nama --}}
                                <div class="modal-field">
                                    <label>
                                        Nama Lengkap
                                        <span class="modal-required">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        x-model="form.name"
                                        @input="delete errors.name"
                                        :disabled="loading"
                                        placeholder="Contoh: Budi Santoso"
                                        autocomplete="off"
                                    />
                                    <template x-if="errors.name">
                                        <span class="modal-field-error" x-text="errors.name[0]"></span>
                                    </template>
                                </div>

                                {{-- Email --}}
                                <div class="modal-field">
                                    <label>
                                        Email
                                        <span class="modal-required">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        x-model="form.email"
                                        @input="delete errors.email"
                                        :disabled="loading"
                                        placeholder="budi@contoh.com"
                                        autocomplete="off"
                                    />
                                    <template x-if="errors.email">
                                        <span class="modal-field-error" x-text="errors.email[0]"></span>
                                    </template>
                                </div>

                                {{-- Password --}}
                                <div class="modal-field">
                                    <label>
                                        Password
                                        <template x-if="mode === 'create'">
                                            <span class="modal-required">*</span>
                                        </template>
                                        <template x-if="mode === 'edit'">
                                            <span style="font-weight:400;color:var(--text-muted);font-size:.75rem;">
                                                (kosongkan jika tidak diubah)
                                            </span>
                                        </template>
                                    </label>
                                    <input
                                        type="password"
                                        x-model="form.password"
                                        @input="delete errors.password"
                                        :disabled="loading"
                                        placeholder="Min. 8 karakter, huruf + angka"
                                        autocomplete="new-password"
                                    />
                                    <template x-if="errors.password">
                                        <span class="modal-field-error" x-text="errors.password[0]"></span>
                                    </template>
                                </div>

                                {{-- Role + Status — 2 kolom --}}
                                <div class="modal-field-row">

                                    {{-- Role --}}
                                    <div>
                                        <label>
                                            Role
                                            <span class="modal-required">*</span>
                                        </label>
                                        <select
                                            x-model="form.role"
                                            @change="delete errors.role"
                                            :disabled="loading"
                                        >
                                            <option value="staff">Staff</option>
                                            <option value="admin">Administrator</option>
                                        </select>
                                        <template x-if="errors.role">
                                            <span class="modal-field-error" x-text="errors.role[0]"></span>
                                        </template>
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <label>Status</label>
                                        <div class="modal-toggle-row">
                                            <span class="modal-toggle-label">Aktif</span>
                                            <button
                                                type="button"
                                                class="modal-toggle"
                                                :class="{ 'modal-toggle--on': form.is_active }"
                                                @click="form.is_active = !form.is_active"
                                                :disabled="loading"
                                                :aria-checked="form.is_active"
                                                role="switch"
                                            >
                                                <span class="modal-toggle__thumb"></span>
                                            </button>
                                            <span
                                                class="modal-toggle-text"
                                                :class="form.is_active ? 'modal-toggle-text--on' : 'modal-toggle-text--off'"
                                                x-text="form.is_active ? 'Ya' : 'Tidak'"
                                            ></span>
                                        </div>
                                    </div>

                                </div>{{-- .modal-field-row --}}

                                {{-- Role info --}}
                                <template x-if="form.role === 'admin'">
                                    <div style="margin-top:8px;padding:8px 12px;background:var(--info-bg);border-radius:var(--radius-sm);font-size:.75rem;color:#1E40AF;">
                                        ⚠ Admin memiliki akses penuh termasuk User Management dan Adjustment stok.
                                    </div>
                                </template>

                            </div>
                        </template>

                    </div>
                </form>
            </div>

            {{-- Footer --}}
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
                    form="user-form"
                    :disabled="loading"
                >
                    <template x-if="loading">
                        <span style="display:inline-flex;align-items:center;gap:6px;">
                            <svg style="width:14px;height:14px;animation:spin .6s linear infinite;"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity:.25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path style="opacity:.75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                            Menyimpan...
                        </span>
                    </template>
                    <template x-if="!loading">
                        <span x-text="mode === 'delete' ? 'Ya, Hapus' : (mode === 'edit' ? 'Update User' : 'Simpan User')"></span>
                    </template>
                </button>
            </div>

        </div>{{-- .modal-box --}}
    </div>{{-- .modal-backdrop --}}
</div>{{-- modal x-data --}}

@endsection