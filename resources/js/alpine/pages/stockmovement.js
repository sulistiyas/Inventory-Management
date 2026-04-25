export default function stockManager() {
    return {
        // ── Transaction modal state ──────────────────────────────
        isOpen:       false,
        isSubmitting: false,
        activeTab:    'in',   // 'in' | 'out'
        errors:       {},
        globalError:  null,
        form: { product_id: null, quantity: 1, notes: '' },

        // ── Product picker state ─────────────────────────────────
        allProducts:      [],
        filteredProducts: [],
        selectedProduct:  null,
        productSearch:    '',

        // ── Detail modal state ───────────────────────────────────
        detailOpen:    false,
        detailLoading: false,
        detailData:    null,

        // ── Datatable state ──────────────────────────────────────
        tableData:   [],
        isLoading:   true,
        total:       0,
        currentPage: 1,
        lastPage:    1,
        perPage:     15,
        pageNumbers: [],
        filters: {
            search:    '',
            type:      '',
            date_from: '',
            date_to:   '',
        },

        // ── Init ─────────────────────────────────────────────────
        async init() {
            await this.fetchProducts();
            await this.loadData();
        },

        // ── Fetch product list for the selector ──────────────────
        async fetchProducts() {
            try {
                const res  = await fetch('/api/stock/products');
                const json = await res.json();
                this.allProducts      = json.data ?? [];
                this.filteredProducts = this.allProducts;
            } catch (e) {
                console.error('Failed to load products', e);
            }
        },

        filterProducts() {
            const q = this.productSearch.toLowerCase();
            this.filteredProducts = this.allProducts.filter(p =>
                p.name.toLowerCase().includes(q) ||
                p.sku.toLowerCase().includes(q)
            );
        },

        selectProduct(product) {
            this.selectedProduct  = product;
            this.form.product_id  = product.id;
            this.form.quantity    = 1;
            delete this.errors.product_id;
        },

        // ── Open modal helpers ───────────────────────────────────
        openStockIn() {
            this.activeTab = 'in';
            this._resetForm();
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        openStockOut() {
            this.activeTab = 'out';
            this._resetForm();
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        switchTab(tab) {
            this.activeTab   = tab;
            this.errors      = {};
            this.globalError = null;
        },

        close() {
            if (this.isSubmitting) return;
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        _resetForm() {
            this.form           = { product_id: null, quantity: 1, notes: '' };
            this.selectedProduct = null;
            this.productSearch   = '';
            this.filteredProducts = this.allProducts;
            this.errors          = {};
            this.globalError     = null;
        },

        // ── Quantity controls ────────────────────────────────────
        increment() { this.form.quantity = Math.min(999999, (this.form.quantity || 0) + 1); },
        decrement() { this.form.quantity = Math.max(1, (this.form.quantity || 1) - 1); },

        // ── Stock result preview ─────────────────────────────────
        get stockResult() {
            if (!this.selectedProduct) return 0;
            const qty = parseInt(this.form.quantity) || 0;
            return this.activeTab === 'in'
                ? this.selectedProduct.stock + qty
                : this.selectedProduct.stock - qty;
        },

        // ── Submit transaction ───────────────────────────────────
        async handleSubmit() {
            if (this.isSubmitting) return;

            this.isSubmitting = true;
            this.errors       = {};
            this.globalError  = null;

            const url = this.activeTab === 'in'
                ? '/api/stock/in'
                : '/api/stock/out';

            try {
                const res  = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });
                const json = await res.json();

                if (!res.ok) {
                    if (json.errors) { this.errors = json.errors; return; }
                    this.globalError = json.message ?? 'Terjadi kesalahan.';
                    return;
                }

                // Success
                this.close();

                // Update the selected product's stock in the list
                if (this.selectedProduct) {
                    const p = this.allProducts.find(x => x.id === this.form.product_id);
                    if (p) p.stock = json.data.stock_after;
                }

                // Reload table
                await this.loadData();

            } catch (e) {
                this.globalError = 'Koneksi gagal. Silakan coba lagi.';
            } finally {
                this.isSubmitting = false;
            }
        },

        // ── Detail modal ─────────────────────────────────────────
        async openDetail(id) {
            this.detailOpen    = true;
            this.detailLoading = true;
            this.detailData    = null;
            document.body.style.overflow = 'hidden';

            try {
                const res  = await fetch(`/api/stock/${id}`);
                const json = await res.json();
                this.detailData = json.data ?? null;
            } catch (e) {
                console.error('Failed to load movement detail', e);
            } finally {
                this.detailLoading = false;
            }
        },

        detailRows() {
            if (!this.detailData) return [];
            const d = this.detailData;
            return [
                ['ID Transaksi',  '#' + d.id,                  true],
                ['Tipe',          d.type_label,                 false],
                ['Produk',        d.product?.name,              false],
                ['SKU',           d.product?.sku,               true],
                ['Kategori',      d.product?.category,          false],
                ['Jumlah',        d.quantity + ' unit',         true],
                ['Stok Sebelum',  d.stock_before + ' unit',     true],
                ['Stok Sesudah',  d.stock_after + ' unit',      true],
                ['Catatan',       d.notes,                      false],
                ['Operator',      d.user?.name,                 false],
                ['Waktu',         d.created_at_formatted,       false],
            ];
        },

        // ── Datatable ────────────────────────────────────────────
        async loadData() {
            this.isLoading = true;

            const params = new URLSearchParams({
                page:     this.currentPage,
                per_page: this.perPage,
                ...Object.fromEntries(
                    Object.entries(this.filters).filter(([, v]) => v !== '')
                ),
            });

            try {
                const res  = await fetch(`/api/stock?${params}`);
                const json = await res.json();

                this.tableData   = json.data         ?? [];
                this.total       = json.total        ?? 0;
                this.lastPage    = json.last_page     ?? 1;
                this.currentPage = json.current_page  ?? 1;
                this.pageNumbers = this._buildPageNumbers();
            } catch (e) {
                console.error('Failed to load stock data', e);
            } finally {
                this.isLoading = false;
            }
        },

        changePage(page) {
            if (page < 1 || page > this.lastPage) return;
            this.currentPage = page;
            this.loadData();
        },

        clearFilters() {
            this.filters     = { search: '', type: '', date_from: '', date_to: '' };
            this.currentPage = 1;
            this.loadData();
        },

        _buildPageNumbers() {
            const range = [];
            const delta = 2;
            const left  = Math.max(1, this.currentPage - delta);
            const right = Math.min(this.lastPage, this.currentPage + delta);
            for (let i = left; i <= right; i++) range.push(i);
            return range;
        },
    };
}