@extends('layouts.app')

@section('title', 'Categories')

{{-- @push('styles')
<link rel="stylesheet" href="{{ asset('css/components/datatable.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/modal.css') }}">
@endpush --}}

@section('content')
 
{{-- ════════════════════════════════════════════════════════
     MODAL WRAPPER — membungkus datatable agar openCreate()
     dapat diakses dari dalam scope datatable
     ════════════════════════════════════════════════════════ --}}
<div
    x-data="modal({
        endpoint: '/categories',
        createTitle: 'Tambah Category',
        editTitle: 'Edit Category',
        deleteTitle: 'Hapus Category',
        defaultForm: () => ({
            name: '',
            description: ''
        })
    })"
    @open-edit.window="openEdit($event.detail)"
    @open-delete.window="openDelete($event.detail)"
>
 
    {{-- ════════════════════════════════════════════════════
         DATATABLE
         ════════════════════════════════════════════════════ --}}
    <div
        x-data="datatable({
            apiEndpoint: '{{ route('categories.list') }}',
            columns: [
                { key: 'name', label: 'Category Name' },
                { key: 'description', label: 'Description' }
            ]
        })"
        class="datatable"
    >
 
        {{-- HEADER: judul + search + tombol Add --}}
        <div class="datatable-header">
            <h2>Category List</h2>
 
            <div class="datatable-search-wrap">
                <input type="text" x-model="search" placeholder="Search categories...">
            </div>
 
            <button class="datatable-add-btn" @click="openCreate()">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Category
            </button>
        </div>
 
        {{-- TABLE CARD --}}
        <div class="datatable-card">
            <table class="datatable-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <template x-for="col in columns" :key="col.key">
                            <th x-text="col.label"></th>
                        </template>
                        <th>Action</th>
                    </tr>
                </thead>
 
                <tbody>
 
                    {{-- Loading --}}
                    <template x-if="loading">
                        <tr>
                            <td colspan="5" class="datatable-loading">Loading...</td>
                        </tr>
                    </template>
 
                    {{-- Empty --}}
                    <template x-if="!loading && data.length === 0">
                        <tr>
                            <td colspan="5" class="datatable-empty">
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
 
                            <template x-for="col in columns" :key="col.key">
                                <td x-text="item[col.key]"></td>
                            </template>
 
                            <td>
                                {{-- .dt-btn & .dt-btn-edit — bukan .btn / .btn-primary dari app.css --}}
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
         MODAL COMPONENT
         ════════════════════════════════════════════════════ --}}
    <x-modal>
        <div class="modal-form-group">
            <label for="cat-name">Name</label>
            <input id="cat-name" type="text" x-model="form.name" placeholder="Nama kategori...">
            <template x-if="errors.name">
                <span class="modal-field-error" x-text="errors.name[0]"></span>
            </template>
 
            <label for="cat-desc">Description</label>
            <textarea id="cat-desc" x-model="form.description" rows="3"
                      placeholder="Deskripsi singkat..."></textarea>
            <template x-if="errors.description">
                <span class="modal-field-error" x-text="errors.description[0]"></span>
            </template>
        </div>
    </x-modal>
 
</div>{{-- /modal wrapper --}}
@endsection