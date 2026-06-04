export default function posKasir(config = {}) {
    return {
        // ── Tab ───────────────────────────────────────────────────────────────
        activeTab: 'products',   // 'products' | 'history'

        // ── Produk ────────────────────────────────────────────────────────────
        allProducts:      [],
        filteredProducts: [],
        productSearch:    '',

        // ── Pelanggan ─────────────────────────────────────────────────────────
        allCustomers:      [],
        filteredCustomers: [],
        customerSearch:    '',
        showCustomerDropdown: false,
        selectedCustomer:  null,

        // ── Keranjang ─────────────────────────────────────────────────────────
        cart:     [],
        discount: 0,
        tax:      0,
        notes:    '',

        // ── Pembayaran ────────────────────────────────────────────────────────
        paymentOpen:    false,
        payments:       [],
        paymentMethods: config.paymentMethods ?? [],

        // ── Submit ────────────────────────────────────────────────────────────
        isSubmitting: false,
        globalError:  null,

        // ── Receipt ───────────────────────────────────────────────────────────
        receiptOpen: false,
        receiptData: null,

        // ── History datatable state ───────────────────────────────────────────
        historyData:      [],
        historyLoading:   false,
        historyPage:      1,
        historyLastPage:  1,
        historySearch:    '',
        historyDateFrom:  '',

        // ── Detail modal ──────────────────────────────────────────────────────
        detailOpen:    false,
        detailLoading: false,
        detailData:    null,

        // ── Init ──────────────────────────────────────────────────────────────
        async init() {
            await Promise.all([
                this.fetchProducts(),
                this.fetchCustomers(),
            ]);
        },

        // ── Tab switching ─────────────────────────────────────────────────────
        setTab(tab) {
            this.activeTab = tab;
            if (tab === 'history' && this.historyData.length === 0) {
                this.fetchHistory();
            }
        },

        // ── Fetch produk ──────────────────────────────────────────────────────
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

        searchProducts() {
            const q = this.productSearch.toLowerCase().trim();
            this.filteredProducts = q
                ? this.allProducts.filter(p =>
                    p.name.toLowerCase().includes(q) ||
                    p.sku.toLowerCase().includes(q)
                )
                : this.allProducts;
        },

        // ── Fetch pelanggan ───────────────────────────────────────────────────
        async fetchCustomers() {
            try {
                const res  = await fetch('/customers/api/data?per_page=200');
                const json = await res.json();
                this.allCustomers = json.data ?? [];
            } catch (e) {
                console.error('Gagal load pelanggan', e);
            }
        },

        filterCustomers() {
            const q = this.customerSearch.toLowerCase().trim();
            if (!q) {
                this.filteredCustomers = [];
                this.showCustomerDropdown = false;
                return;
            }
            this.filteredCustomers = this.allCustomers.filter(c =>
                c.name.toLowerCase().includes(q) ||
                (c.phone         ?? '').toLowerCase().includes(q) ||
                (c.vehicle_plate ?? '').toLowerCase().includes(q)
            ).slice(0, 6);
            this.showCustomerDropdown = this.filteredCustomers.length > 0 && !this.selectedCustomer;
        },

        selectCustomer(customer) {
            this.selectedCustomer     = customer;
            this.customerSearch       = customer.name;
            this.showCustomerDropdown = false;
        },

        clearCustomer() {
            this.selectedCustomer     = null;
            this.customerSearch       = '';
            this.filteredCustomers    = [];
            this.showCustomerDropdown = false;
        },

        // ── Keranjang ─────────────────────────────────────────────────────────
        addToCart(product) {
            if (product.stock <= 0) {
                window.showAlert('error', `Stok "${product.name}" habis.`);
                return;
            }
            const existing = this.cart.find(i => i.product_id === product.id);
            if (existing) {
                if (existing.qty >= product.stock) {
                    window.showAlert('error', `Stok "${product.name}" tidak cukup.`);
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
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        increaseQty(index) {
            const item = this.cart[index];
            if (item.qty >= item.stock) {
                window.showAlert('error', 'Melebihi stok tersedia.');
                return;
            }
            item.qty++;
        },

        decreaseQty(index) {
            if (this.cart[index].qty <= 1) {
                this.removeFromCart(index);
                return;
            }
            this.cart[index].qty--;
        },

        clearCart() {
            this.cart             = [];
            this.discount         = 0;
            this.tax              = 0;
            this.notes            = '';
            this.selectedCustomer = null;
            this.customerSearch   = '';
        },

        // ── Kalkulasi ─────────────────────────────────────────────────────────
        get subtotal() {
            return this.cart.reduce((s, i) => s + i.qty * i.price, 0);
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - parseFloat(this.discount || 0) + parseFloat(this.tax || 0));
        },

        get totalPaid() {
            return this.payments.reduce((s, p) => s + parseFloat(p.amount || 0), 0);
        },

        get change() {
            const cashTotal = this.payments
                .filter(p => this.isCash(p.payment_method_id))
                .reduce((s, p) => s + parseFloat(p.amount || 0), 0);

            const nonCashTotal = this.payments
                .filter(p => !this.isCash(p.payment_method_id))
                .reduce((s, p) => s + parseFloat(p.amount || 0), 0);

            const remainAfterNonCash = Math.max(0, this.grandTotal - nonCashTotal);
            return Math.max(0, cashTotal - remainAfterNonCash);
        },

        get isPaymentSufficient() {
            return this.totalPaid >= this.grandTotal && this.grandTotal > 0 && this.cart.length > 0;
        },

        formatRp(val) {
            return 'Rp\u00a0' + Number(val || 0).toLocaleString('id-ID');
        },

        // ── Helpers metode pembayaran ─────────────────────────────────────────────

        isCash(paymentMethodId) {
            const method = this.paymentMethods.find(m => m.id == paymentMethodId);
            return method?.type === 'cash';
        },

        onPaymentMethodChange(idx) {
            const p      = this.payments[idx];
            const isCash = this.isCash(p.payment_method_id);

            if (!isCash) {
                // Non-cash: hitung sisa tagihan yang belum dibayar baris lain
                const otherPaid = this.payments
                    .filter((_, i) => i !== idx)
                    .reduce((s, x) => s + parseFloat(x.amount || 0), 0);
                p.amount = Math.max(0, this.grandTotal - otherPaid);
            } else {
                // Cash: kosongkan supaya kasir ketik nominal uang yang diterima
                p.amount = '';
            }
        },

        // ── Pembayaran modal ──────────────────────────────────────────────────
        openPayment() {
            if (!this.cart.length) {
                window.showAlert('error', 'Keranjang masih kosong.');
                return;
            }
            this.globalError = null;

            const defaultMethod = this.paymentMethods[0] ?? null;

            // Untuk cash: biarkan kosong supaya kasir ketik nominal yang diterima
            // Untuk non-cash: langsung isi exact grandTotal (tidak ada kembalian)
            const isCashDefault = defaultMethod?.type === 'cash';

            this.payments = [{
                payment_method_id: defaultMethod?.id ?? '',
                amount: isCashDefault ? '' : this.grandTotal,  // ← cash dikosongkan
            }];

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
                const res  = await fetch('/sales/store', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        customer_id: this.selectedCustomer?.id ?? null,
                        items:       this.cart.map(i => ({ product_id: i.product_id, qty: i.qty })),
                        payments:    this.payments.map(p => ({
                            payment_method_id: parseInt(p.payment_method_id),
                            amount:            parseFloat(p.amount),
                        })),
                        discount: parseFloat(this.discount || 0),
                        tax:      parseFloat(this.tax || 0),
                        notes:    this.notes,
                    }),
                });

                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Transaksi gagal.');

                this.paymentOpen = false;
                await this.openReceipt(json.data.id);

                // Refresh stok produk
                await this.fetchProducts();
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
            this.receiptData = null;
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
            // Refresh history kalau tab history aktif
            if (this.activeTab === 'history') this.fetchHistory();
        },

        printReceipt() {
            if (!this.receiptData) return;

            const d    = this.receiptData;
            const self = this;

            // Build item rows
            const itemRows = (d.items ?? []).map(item => `
                <tr>
                    <td style="padding:3px 0;">${item.product_name}</td>
                    <td style="text-align:center;padding:3px 8px;">×${item.qty}</td>
                    <td style="text-align:right;padding:3px 0;">${self.formatRp(item.price)}</td>
                    <td style="text-align:right;padding:3px 0;font-weight:700;">${self.formatRp(item.subtotal)}</td>
                </tr>
            `).join('');

            // Build payment rows
            const payRows = (d.payments ?? []).map(p => `
                <div style="display:flex;justify-content:space-between;margin-top:3px;">
                    <span>${p.method}</span>
                    <span>${self.formatRp(p.amount)}</span>
                </div>
                ${p.change_amount > 0 ? `
                <div style="display:flex;justify-content:space-between;color:#16a34a;font-weight:700;">
                    <span>Kembalian</span>
                    <span>${self.formatRp(p.change_amount)}</span>
                </div>` : ''}
            `).join('');

            const discountRow = parseFloat(d.discount) > 0 ? `
                <div style="display:flex;justify-content:space-between;color:#16a34a;">
                    <span>Diskon</span>
                    <span>− ${self.formatRp(d.discount)}</span>
                </div>` : '';

            const taxRow = parseFloat(d.tax) > 0 ? `
                <div style="display:flex;justify-content:space-between;">
                    <span>Pajak</span>
                    <span>+ ${self.formatRp(d.tax)}</span>
                </div>` : '';

            const customerRow = d.customer ? `
                <div style="margin-bottom:4px;">
                    <span style="color:#6b7280;">Pelanggan: </span>
                    <strong>${d.customer.name}</strong>
                </div>` : '';

            const html = `<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Struk ${d.invoice_no}</title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: 'Courier New', Courier, monospace;
                    font-size: 12px;
                    color: #000;
                    background: #fff;
                    width: 300px;
                    margin: 0 auto;
                    padding: 16px 8px;
                }
                .center { text-align: center; }
                .divider { border: none; border-top: 1px dashed #000; margin: 8px 0; }
                table { width: 100%; border-collapse: collapse; }
                th { text-align: left; font-size: 11px; color: #555; padding-bottom: 4px; border-bottom: 1px dashed #ccc; }
                .total-row { display: flex; justify-content: space-between; font-weight: 700; font-size: 14px; margin-top: 6px; padding-top: 6px; border-top: 1px dashed #000; }
                @media print {
                    body { width: 80mm; }
                    @page { margin: 4mm; }
                }
            </style>
        </head>
        <body>
            <div class="center" style="margin-bottom:12px;">
                <div style="font-size:16px;font-weight:900;letter-spacing:2px;">STRUK PEMBAYARAN</div>
                <div style="margin-top:4px;color:#555;">${d.invoice_no}</div>
                <div style="color:#555;font-size:11px;">${d.sold_at ?? ''}</div>
            </div>

            ${customerRow}

            <hr class="divider">

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Harga</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>${itemRows}</tbody>
            </table>

            <hr class="divider">

            <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                <span>Subtotal</span><span>${self.formatRp(d.subtotal)}</span>
            </div>
            ${discountRow}
            ${taxRow}
            <div class="total-row">
                <span>TOTAL</span><span>${self.formatRp(d.grand_total)}</span>
            </div>

            <hr class="divider">

            ${payRows}

            <hr class="divider">

            <div class="center" style="margin-top:12px;color:#555;font-size:11px;">
                Terima kasih telah berkunjung! 🙏<br>
                Simpan struk ini sebagai bukti pembayaran
            </div>
        </body>
        </html>`;

            const win = window.open('', '_blank', 'width=350,height=600,scrollbars=no');
            if (!win) {
                // Kalau popup diblokir browser, fallback ke print langsung
                window.showAlert?.('error', 'Pop-up diblokir browser. Izinkan pop-up untuk halaman ini.');
                return;
            }

            win.document.write(html);
            win.document.close();

            // Tunggu load baru print
            win.onload = () => {
                win.focus();
                win.print();
                win.onafterprint = () => win.close();
            };
        },

        // ── History ───────────────────────────────────────────────────────────
        async fetchHistory() {
            this.historyLoading = true;
            try {
                const params = new URLSearchParams({
                    page:      this.historyPage,
                    perPage:   10,
                    search:    this.historySearch,
                    status:    'paid',
                    date_from: this.historyDateFrom,
                });
                const res  = await fetch(`/sales/api/data?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                this.historyData     = json.data     ?? [];
                this.historyLastPage = json.meta?.last_page ?? 1;
            } catch (e) {
                console.error('Gagal load history', e);
            } finally {
                this.historyLoading = false;
            }
        },

        historyChangePage(page) {
            if (page < 1 || page > this.historyLastPage) return;
            this.historyPage = page;
            this.fetchHistory();
        },

        // ── Detail transaksi ──────────────────────────────────────────────────
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
        
        async printFromHistory(saleId) {
            try {
                const res  = await fetch(`/sales/${saleId}`);
                const json = await res.json();
                if (!json.success) throw new Error('Gagal load data struk.');

                // Pinjam receiptData sementara, print, lalu kembalikan
                const prev       = this.receiptData;
                this.receiptData = json.data;
                this.printReceipt();
                this.receiptData = prev;

            } catch (e) {
                window.showAlert('error', e.message || 'Gagal mencetak struk.');
            }
        },
    };
}
