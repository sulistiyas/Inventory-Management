export default function posKasir() {
    return {
        // ── Produk ────────────────────────────────────────────────────────────
        allProducts:      [],
        filteredProducts: [],
        productSearch:    '',

        // ── Pelanggan ─────────────────────────────────────────────────────────
        allCustomers:      [],
        filteredCustomers: [],
        customerSearch:    '',
        selectedCustomer:  null,

        // ── Keranjang ─────────────────────────────────────────────────────────
        cart:     [],
        discount: 0,
        tax:      0,
        notes:    '',

        // ── Pembayaran ────────────────────────────────────────────────────────
        paymentOpen:  false,
        payments:     [],   // [{ payment_method_id, amount }]
        paymentMethods: [], // dari props Blade

        // ── Submit ────────────────────────────────────────────────────────────
        isSubmitting: false,
        globalError:  null,

        // ── Receipt modal ─────────────────────────────────────────────────────
        receiptOpen: false,
        receiptData: null,

        // ── Detail modal (riwayat) ────────────────────────────────────────────
        detailOpen:    false,
        detailLoading: false,
        detailData:    null,

        // ── Init ──────────────────────────────────────────────────────────────
        async init() {
            await Promise.all([
                this.fetchProducts(),
                this.fetchCustomers(),
            ]);

            // Ambil paymentMethods yang di-pass dari Blade via x-data prop
            // (diinject di Blade view: posKasir({ paymentMethods: [...] }) )
        },

        // ── Fetch ─────────────────────────────────────────────────────────────
        async fetchProducts() {
            try {
                const res  = await fetch('/api/sales/products');
                const json = await res.json();
                this.allProducts      = json.data ?? [];
                this.filteredProducts = this.allProducts;
            } catch (e) {
                console.error('Gagal load produk', e);
            }
        },

        async fetchCustomers() {
            try {
                const res  = await fetch('/customers/api/data?per_page=200');
                const json = await res.json();
                this.allCustomers      = json.data ?? [];
                this.filteredCustomers = this.allCustomers;
            } catch (e) {
                console.error('Gagal load pelanggan', e);
            }
        },

        async searchProducts() {
            const q = this.productSearch.toLowerCase();
            if (!q) {
                this.filteredProducts = this.allProducts;
                return;
            }
            this.filteredProducts = this.allProducts.filter(p =>
                p.name.toLowerCase().includes(q) ||
                p.sku.toLowerCase().includes(q)
            );
        },

        filterCustomers() {
            const q = this.customerSearch.toLowerCase();
            this.filteredCustomers = this.allCustomers.filter(c =>
                c.name.toLowerCase().includes(q) ||
                (c.phone         ?? '').toLowerCase().includes(q) ||
                (c.vehicle_plate ?? '').toLowerCase().includes(q)
            );
        },

        selectCustomer(customer) {
            this.selectedCustomer  = customer;
            this.customerSearch    = customer.name;
            this.filteredCustomers = [];
        },

        clearCustomer() {
            this.selectedCustomer  = null;
            this.customerSearch    = '';
            this.filteredCustomers = this.allCustomers;
        },

        // ── Keranjang ─────────────────────────────────────────────────────────
        addToCart(product) {
            if (product.stock <= 0) {
                window.showAlert?.('error', `Stok "${product.name}" habis.`);
                return;
            }

            const existing = this.cart.find(i => i.product_id === product.id);
            if (existing) {
                if (existing.qty >= product.stock) {
                    window.showAlert?.('error', `Stok "${product.name}" tidak cukup.`);
                    return;
                }
                existing.qty++;
            } else {
                this.cart.push({
                    product_id:   product.id,
                    product_name: product.name,
                    product_sku:  product.sku,
                    price:        product.price,
                    stock:        product.stock,
                    qty:          1,
                });
            }

            this.productSearch    = '';
            this.filteredProducts = this.allProducts;
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        increaseQty(index) {
            const item = this.cart[index];
            if (item.qty >= item.stock) {
                window.showAlert?.('error', 'Melebihi stok tersedia.');
                return;
            }
            item.qty++;
        },

        decreaseQty(index) {
            const item = this.cart[index];
            if (item.qty <= 1) {
                this.removeFromCart(index);
                return;
            }
            item.qty--;
        },

        clearCart() {
            this.cart            = [];
            this.discount        = 0;
            this.tax             = 0;
            this.notes           = '';
            this.selectedCustomer = null;
            this.customerSearch   = '';
        },

        // ── Kalkulasi ─────────────────────────────────────────────────────────
        get subtotal() {
            return this.cart.reduce((sum, i) => sum + (i.qty * i.price), 0);
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - parseFloat(this.discount || 0) + parseFloat(this.tax || 0));
        },

        get totalPaid() {
            return this.payments.reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
        },

        get change() {
            return Math.max(0, this.totalPaid - this.grandTotal);
        },

        get isPaymentSufficient() {
            return this.totalPaid >= this.grandTotal && this.grandTotal > 0;
        },

        formatRp(val) {
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        },

        // ── Pembayaran ────────────────────────────────────────────────────────
        openPayment() {
            if (!this.cart.length) {
                window.showAlert?.('error', 'Keranjang masih kosong.');
                return;
            }

            // Reset payments, default satu baris cash
            this.payments = [{ payment_method_id: this.paymentMethods[0]?.id ?? '', amount: this.grandTotal }];
            this.paymentOpen = true;
        },

        closePayment() {
            if (this.isSubmitting) return;
            this.paymentOpen = false;
        },

        addPaymentRow() {
            this.payments.push({ payment_method_id: '', amount: 0 });
        },

        removePaymentRow(index) {
            if (this.payments.length <= 1) return;
            this.payments.splice(index, 1);
        },

        // ── Submit transaksi ──────────────────────────────────────────────────
        async submitSale() {
            if (this.isSubmitting || !this.isPaymentSufficient) return;

            this.isSubmitting = true;
            this.globalError  = null;

            try {
                const payload = {
                    customer_id: this.selectedCustomer?.id ?? null,
                    items:       this.cart.map(i => ({ product_id: i.product_id, qty: i.qty })),
                    payments:    this.payments.map(p => ({
                        payment_method_id: parseInt(p.payment_method_id),
                        amount:            parseFloat(p.amount),
                    })),
                    discount:    parseFloat(this.discount || 0),
                    tax:         parseFloat(this.tax || 0),
                    notes:       this.notes,
                };

                const res  = await fetch('/sales/store', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                const json = await res.json();

                if (!res.ok) throw new Error(json.message || 'Transaksi gagal.');

                // Tutup payment modal, tampilkan receipt
                this.paymentOpen = false;
                await this.openReceipt(json.data.id);
                this.clearCart();

            } catch (e) {
                this.globalError = e.message;
            } finally {
                this.isSubmitting = false;
            }
        },

        // ── Receipt ───────────────────────────────────────────────────────────
        async openReceipt(saleId) {
            this.receiptOpen = true;
            try {
                const res  = await fetch(`/sales/${saleId}`);
                const json = await res.json();
                this.receiptData = json.data ?? null;
            } catch (e) {
                console.error(e);
            }
        },

        closeReceipt() {
            this.receiptOpen = false;
            this.receiptData = null;
        },

        printReceipt() {
            window.print();
        },

        // ── Detail riwayat transaksi ──────────────────────────────────────────
        async openDetail(id) {
            this.detailOpen    = true;
            this.detailLoading = true;
            this.detailData    = null;
            try {
                const res  = await fetch(`/sales/${id}`);
                const json = await res.json();
                this.detailData = json.data ?? null;
            } catch (e) {
                console.error(e);
            } finally {
                this.detailLoading = false;
            }
        },

        closeDetail() { this.detailOpen = false; },
    };
}
