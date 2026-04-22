export default function datatable(config = {}) {
    return {
        // ── Configuration ──────────────────────────────────────────────────────
        apiEndpoint: config.apiEndpoint || '/api/categories',
        columns: config.columns || [],
        perPage: config.perPage || 10,

        // ── State ──────────────────────────────────────────────────────────────
        data: [],
        isLoading: false,
        error: null,
        search: '',
        currentPage: 1,
        lastPage: 1,
        total: 0,
        perPageOptions: [5, 10, 25, 50],

        // ── Initialization ─────────────────────────────────────────────────────
        init() {
            this.fetchData();
        },

        // ── Data Fetching ──────────────────────────────────────────────────────
        async fetchData(page = 1) {
            this.isLoading = true;
            this.error = null;

            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: this.perPage,
                    search: this.search,
                });

                const response = await fetch(`${this.apiEndpoint}?${params}`);

                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status}`);
                }

                const result = await response.json();

                this.data = result.data || [];
                this.currentPage = result.meta.current_page;
                this.lastPage = result.meta.last_page;
                this.total = result.meta.total;
            } catch (err) {
                this.error = err.message || 'Failed to load data';
                this.data = [];
            } finally {
                this.isLoading = false;
            }
        },

        // ── Search with Debounce ───────────────────────────────────────────────
        onSearchInput() {
            // Clear existing debounce timer
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            // Debounce search (300ms)
            this.searchTimeout = setTimeout(() => {
                this.currentPage = 1;
                this.fetchData(1);
            }, 300);
        },

        // ── Pagination ─────────────────────────────────────────────────────────
        goToPage(page) {
            if (page > 0 && page <= this.lastPage) {
                this.currentPage = page;
                this.fetchData(page);
            }
        },

        nextPage() {
            if (this.currentPage < this.lastPage) {
                this.goToPage(this.currentPage + 1);
            }
        },

        previousPage() {
            if (this.currentPage > 1) {
                this.goToPage(this.currentPage - 1);
            }
        },

        // ── Utilities ──────────────────────────────────────────────────────────
        hasData() {
            return this.data.length > 0;
        },

        isFirstPage() {
            return this.currentPage === 1;
        },

        isLastPage() {
            return this.currentPage === this.lastPage;
        },

        getPageNumbers() {
            const pages = [];
            const maxPages = 5;
            let startPage = Math.max(1, this.currentPage - Math.floor(maxPages / 2));
            let endPage = Math.min(this.lastPage, startPage + maxPages - 1);

            if (endPage - startPage + 1 < maxPages) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                pages.push(i);
            }

            return pages;
        },

        changePerPage(newPerPage) {
            this.perPage = parseInt(newPerPage);
            this.currentPage = 1;
            this.fetchData(1);
        },
    };
}
