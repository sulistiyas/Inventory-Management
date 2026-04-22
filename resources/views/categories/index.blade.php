@extends('layouts.app')

@section('title', 'Categories')
@section('breadcrumb', 'Categories')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/components/datatable.css') }}" />
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Categories</h1>
            <p class="page-subtitle">Manage and organise your product categories</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-secondary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export
            </button>
            <button class="btn btn-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Category
            </button>
        </div>
    </div>

    <div
        x-data="datatable({
            apiEndpoint: '{{ route('api.categories.list') }}',
            columns: [
                { key: 'id',          label: 'ID' },
                { key: 'name',        label: 'Category Name' },
                { key: 'description', label: 'Description' },
                { key: 'created_at',  label: 'Created' },
            ],
            perPage: 10,
        })"
        class="datatable-wrapper"
    >

        {{-- ── Toolbar: Search + meta ───────────────────────────── --}}
        <div class="datatable-toolbar">
            <div class="datatable-search-box">
                <svg class="datatable-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    x-model="search"
                    @input="onSearchInput()"
                    placeholder="Search categories..."
                    class="datatable-search-input"
                />
                <template x-if="search">
                    <button class="datatable-search-clear" @click="search = ''; onSearchInput()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </template>
            </div>

            <div class="datatable-toolbar-meta">
                <template x-if="!isLoading && total > 0">
                    <span class="datatable-total-badge" x-text="total + ' categories'"></span>
                </template>
            </div>
        </div>

        {{-- ── Error State ──────────────────────────────────────── --}}
        <template x-if="error">
            <div class="datatable-error">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
                </svg>
                <div>
                    <strong>Something went wrong</strong>
                    <span x-text="error"></span>
                </div>
            </div>
        </template>

        {{-- ── Loading State ────────────────────────────────────── --}}
        <template x-if="isLoading && !hasData()">
            <div class="datatable-loading">
                <div class="datatable-skeleton">
                    <template x-for="i in 5" :key="i">
                        <div class="datatable-skeleton-row">
                            <div class="skeleton skeleton-id"></div>
                            <div class="skeleton skeleton-name"></div>
                            <div class="skeleton skeleton-desc"></div>
                            <div class="skeleton skeleton-date"></div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- ── Table ────────────────────────────────────────────── --}}
        <template x-if="hasData() || !isLoading">
            <div class="datatable-table-container">
                <table class="datatable-table">
                    <thead class="datatable-thead">
                        <tr>
                            <template x-for="column in columns" :key="column.key">
                                <th class="datatable-th" x-text="column.label"></th>
                            </template>
                            <th class="datatable-th datatable-th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="datatable-tbody">
                        <template x-for="(item, index) in data" :key="item.id">
                            <tr class="datatable-row">
                                <template x-for="column in columns" :key="column.key">
                                    <td class="datatable-td" :class="'datatable-td--' + column.key">

                                        {{-- ID column --}}
                                        <template x-if="column.key === 'id'">
                                            <span class="datatable-id-badge" x-text="item[column.key]"></span>
                                        </template>

                                        {{-- Name column --}}
                                        <template x-if="column.key === 'name'">
                                            <div class="datatable-name-cell">
                                                <span class="datatable-name-avatar" x-text="item[column.key] ? item[column.key].charAt(0).toUpperCase() : '?'"></span>
                                                <span class="datatable-name-text" x-text="item[column.key] || '-'"></span>
                                            </div>
                                        </template>

                                        {{-- Description column --}}
                                        <template x-if="column.key === 'description'">
                                            <span class="datatable-desc-text" x-text="item[column.key] || '-'"></span>
                                        </template>

                                        {{-- Created at column --}}
                                        <template x-if="column.key === 'created_at'">
                                            <span
                                                class="datatable-date"
                                                x-text="new Date(item[column.key]).toLocaleDateString('en-US', {
                                                    year: 'numeric',
                                                    month: 'short',
                                                    day: 'numeric'
                                                })"
                                            ></span>
                                        </template>

                                    </td>
                                </template>

                                {{-- Actions column --}}
                                <td class="datatable-td datatable-td-actions">
                                    <div class="datatable-actions">
                                        <button class="datatable-action-btn datatable-action-edit" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                            </svg>
                                        </button>
                                        <button class="datatable-action-btn datatable-action-delete" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        {{-- ── Empty State ──────────────────────────────────────── --}}
        <template x-if="!hasData() && !isLoading && !error">
            <div class="datatable-empty">
                <div class="datatable-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                </div>
                <h3 class="datatable-empty-title">
                    <template x-if="search">No results found</template>
                    <template x-if="!search">No categories yet</template>
                </h3>
                <p class="datatable-empty-text">
                    <template x-if="search">
                        <span>No categories match "<strong x-text="search"></strong>". Try a different keyword.</span>
                    </template>
                    <template x-if="!search">
                        <span>Get started by creating your first product category.</span>
                    </template>
                </p>
                <template x-if="!search">
                    <button class="btn btn-primary btn-sm" style="margin-top:.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add Category
                    </button>
                </template>
            </div>
        </template>

        {{-- ── Pagination ───────────────────────────────────────── --}}
        <template x-if="lastPage > 1">
            <div class="datatable-pagination">
                <div class="datatable-page-info">
                    Showing
                    <strong x-text="(currentPage - 1) * perPage + 1"></strong>
                    –
                    <strong x-text="Math.min(currentPage * perPage, total)"></strong>
                    of
                    <strong x-text="total"></strong>
                    results
                </div>

                <div class="datatable-pagination-right">
                    <div class="datatable-controls">
                        <label for="per-page-select">Rows:</label>
                        <select
                            id="per-page-select"
                            class="datatable-per-page"
                            x-model="perPage"
                            @change="changePerPage($event.target.value)"
                        >
                            <template x-for="option in perPageOptions" :key="option">
                                <option :value="option" x-text="option"></option>
                            </template>
                        </select>
                    </div>

                    <div class="datatable-page-buttons">
                        <button
                            class="datatable-page-btn datatable-page-btn--nav"
                            @click="previousPage()"
                            :disabled="isFirstPage()"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </button>

                        <template x-for="pageNum in getPageNumbers()" :key="pageNum">
                            <button
                                class="datatable-page-btn"
                                :class="{ 'active': pageNum === currentPage }"
                                @click="goToPage(pageNum)"
                                x-text="pageNum"
                            ></button>
                        </template>

                        <button
                            class="datatable-page-btn datatable-page-btn--nav"
                            @click="nextPage()"
                            :disabled="isLastPage()"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>{{-- .datatable-wrapper --}}
@endsection