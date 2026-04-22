@extends('layouts.app')

@section('title', 'Categories')
@section('breadcrumb', 'Categories')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/components/datatable.css') }}" />
@endpush

@section('content')
    <div class="page-header">
        <h1>Categories</h1>
        <p class="text-muted">Manage product categories</p>
    </div>

    <div
        x-data="datatable({
            apiEndpoint: '{{ route('api.categories.list') }}',
            columns: [
                { key: 'id', label: 'ID' },
                { key: 'name', label: 'Category Name' },
                { key: 'description', label: 'Description' },
                { key: 'created_at', label: 'Created' },
            ],
            perPage: 10,
        })"
        class="datatable-wrapper"
    >
        {{-- ── Search ───────────────────────────────────────────────── --}}
        <div class="datatable-search-box">
            <input
                type="text"
                x-model="search"
                @input="onSearchInput()"
                placeholder="Search by category name..."
                class="datatable-search-input"
            />
        </div>

        {{-- ── Error State ──────────────────────────────────────────── --}}
        <template x-if="error">
            <div class="datatable-error">
                <strong>Error:</strong> <span x-text="error"></span>
            </div>
        </template>

        {{-- ── Loading State ────────────────────────────────────────── --}}
        <template x-if="isLoading && !hasData()">
            <div class="datatable-loading">
                <div class="spinner"></div>
                <span>Loading categories...</span>
            </div>
        </template>

        {{-- ── Table ────────────────────────────────────────────────── --}}
        <template x-if="hasData() || !isLoading">
            <div class="datatable-table-container">
                <table class="datatable-table">
                    <thead class="datatable-thead">
                        <tr>
                            <template x-for="column in columns" :key="column.key">
                                <th class="datatable-th" x-text="column.label"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="datatable-tbody">
                        <template x-for="item in data" :key="item.id">
                            <tr>
                                <template x-for="column in columns" :key="column.key">
                                    <td class="datatable-td">
                                        <template x-if="column.key === 'created_at'">
                                            <span
                                                x-text="new Date(item[column.key]).toLocaleDateString('en-US', {
                                                    year: 'numeric',
                                                    month: 'short',
                                                    day: 'numeric'
                                                })"
                                            ></span>
                                        </template>
                                        <template x-if="column.key !== 'created_at'">
                                            <span x-text="item[column.key] || '-'"></span>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        {{-- ── Empty State ──────────────────────────────────────────── --}}
        <template x-if="!hasData() && !isLoading && !error">
            <div class="datatable-empty">
                <div class="datatable-empty-icon">📦</div>
                <p class="datatable-empty-text">
                    <template x-if="search">
                        No categories found matching "<span x-text="search"></span>"
                    </template>
                    <template x-if="!search">
                        No categories found
                    </template>
                </p>
            </div>
        </template>

        {{-- ── Pagination ───────────────────────────────────────────── --}}
        <template x-if="lastPage > 1">
            <div class="datatable-pagination">
                <div class="datatable-page-info">
                    Showing <span x-text="(currentPage - 1) * perPage + 1"></span> to
                    <span
                        x-text="Math.min(currentPage * perPage, total)"
                    ></span> of <span x-text="total"></span> results
                </div>

                <div class="datatable-controls">
                    <label for="per-page-select">Per page:</label>
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
                        class="datatable-page-btn"
                        @click="previousPage()"
                        :disabled="isFirstPage()"
                    >
                        ← Prev
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
                        class="datatable-page-btn"
                        @click="nextPage()"
                        :disabled="isLastPage()"
                    >
                        Next →
                    </button>
                </div>
            </div>
        </template>
    </div>
@endsection
