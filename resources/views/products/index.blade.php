@extends('layouts.app')

@section('title', 'Products')

@section('content')

{{-- ① productManager: fetch meta + handle edit/delete/refresh --}}
<div
    x-data="productManager()"
    x-init="init()"
    @edit-product.window="handleEdit($event.detail)"
    @delete-product.window="handleDelete($event.detail)"
>

    {{-- ② modal scope — tambahkan state meta di sini --}}
    <div
        x-data="{
            ...modal({
                endpoint: '/products',
                createTitle: 'Tambah Product',
                editTitle: 'Edit Product',
                deleteTitle: 'Hapus Product',
                defaultForm: () => ({
                    id: null,
                    name: '',
                    sku: '',
                    category_id: '',
                    supplier_id: '',
                    price: '',
                    stock: '',
                    min_stock: '',
                    description: '',
                    is_active: true
                })
            }),
            meta: { categories: [], suppliers: [] }
        }"
        @meta-loaded.window="meta = $event.detail"
        @open-edit.window="openEdit($event.detail)"
        @open-delete.window="openDelete($event.detail)"
    >

        {{-- ③ datatable scope --}}
        <div
            x-data="datatable({
                apiEndpoint: '{{ route('products.list') }}',
                columns: [
                    { key: 'name',          label: 'Name'     },
                    { key: 'sku',           label: 'SKU'      },
                    { key: 'category.name', label: 'Category' },
                    { key: 'supplier.name', label: 'Supplier' },
                    { key: 'price',         label: 'Price'    },
                    { key: 'stock',         label: 'Stock'    }
                ]
            })"
            class="datatable datatable--product"
            @refresh-datatable.window="fetchData()"
        >

            {{-- HEADER --}}
            <div class="datatable-header">
                <h2>Product List</h2>

                <div class="datatable-search-wrap">
                    <input type="text" x-model="search" placeholder="Search products...">
                </div>

                <button class="datatable-add-btn" @click="openCreate()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add Product
                </button>
            </div>

            {{-- TABLE CARD --}}
            <div class="datatable-card">
                <div class="datatable-table-wrap">
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

                            <template x-if="loading">
                                <tr>
                                    <td colspan="8" class="datatable-loading">Loading...</td>
                                </tr>
                            </template>

                            <template x-if="!loading && data.length === 0">
                                <tr>
                                    <td colspan="8" class="datatable-empty">
                                        <span class="datatable-empty-icon"></span>
                                        <span class="datatable-empty-text">Data tidak ditemukan</span>
                                        <span class="datatable-empty-sub">Coba ubah kata kunci pencarian.</span>
                                    </td>
                                </tr>
                            </template>

                            <template x-for="(item, index) in data" :key="item.id">
                                <tr>
                                    <td x-text="numberStart + index + 1"></td>

                                    <template x-for="col in columns" :key="col.key">
                                        <td x-text="getValue(item, col.key) ?? '-'"></td>
                                    </template>

                                    <td>
                                        <button
                                            class="dt-btn dt-btn-edit"
                                            @click="$dispatch('edit-product', item)"
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
                                            @click="$dispatch('delete-product', item)"
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

        {{-- MODAL — meta sudah ada di scope ini --}}
        <x-modal>
            <div class="modal-form-group">

                <div class="modal-field-row">
                    <div>
                        <label>Name <span class="modal-required">*</span></label>
                        <input type="text" x-model="form.name" placeholder="Nama produk...">
                        <template x-if="errors.name">
                            <span class="modal-field-error" x-text="errors.name[0]"></span>
                        </template>
                    </div>
                    <div>
                        <label>SKU <span class="modal-required">*</span></label>
                        <input type="text" x-model="form.sku" placeholder="SKU-001...">
                        <template x-if="errors.sku">
                            <span class="modal-field-error" x-text="errors.sku[0]"></span>
                        </template>
                    </div>
                </div>

                <div class="modal-field-row">
                    <div>
                        {{-- ✅ meta.categories sekarang bisa diakses --}}
                        <label>Category <span class="modal-required">*</span></label>
                        <select x-model="form.category_id">
                            <option value="">-- Select Category --</option>
                            <template x-for="cat in meta.categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                        <template x-if="errors.category_id">
                            <span class="modal-field-error" x-text="errors.category_id[0]"></span>
                        </template>
                    </div>
                    <div>
                        {{-- ✅ meta.suppliers sekarang bisa diakses --}}
                        <label>Supplier</label>
                        <select x-model="form.supplier_id">
                            <option value="">-- Select Supplier --</option>
                            <template x-for="sup in meta.suppliers" :key="sup.id">
                                <option :value="sup.id" x-text="sup.name"></option>
                            </template>
                        </select>
                        <template x-if="errors.supplier_id">
                            <span class="modal-field-error" x-text="errors.supplier_id[0]"></span>
                        </template>
                    </div>
                </div>

                <div class="modal-field-row">
                    <div>
                        <label>Price <span class="modal-required">*</span></label>
                        <input type="number" x-model="form.price" placeholder="0">
                        <template x-if="errors.price">
                            <span class="modal-field-error" x-text="errors.price[0]"></span>
                        </template>
                    </div>
                    <div>
                        <label>Stock</label>
                        <input type="number" x-model="form.stock" placeholder="0">
                        <template x-if="errors.stock">
                            <span class="modal-field-error" x-text="errors.stock[0]"></span>
                        </template>
                    </div>
                </div>

                <div class="modal-field">
                    <label>Min Stock</label>
                    <input type="number" x-model="form.min_stock" placeholder="0">
                    <template x-if="errors.min_stock">
                        <span class="modal-field-error" x-text="errors.min_stock[0]"></span>
                    </template>
                </div>

                <div class="modal-field">
                    <label>Description</label>
                    <textarea x-model="form.description" rows="2" placeholder="Deskripsi produk..."></textarea>
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

    </div>{{-- /modal scope --}}

</div>{{-- /productManager --}}

@endsection