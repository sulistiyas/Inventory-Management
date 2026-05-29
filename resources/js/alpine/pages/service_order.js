export default function serviceOrder() {
    return {
        // ── Product search untuk form items ──────────────────────────────────
        allProducts:      [],
        filteredProducts: [],
        productSearch:    '',
        items:            [],   // spare part yang ditambahkan ke WO

        // ── Customer search ───────────────────────────────────────────────────
        allCustomers:      [],
        filteredCustomers: [],
        customerSearch:    '',
        selectedCustomer:  null,

        // ── Detail modal ──────────────────────────────────────────────────────
        detailOpen:    false,
        detailLoading: false,
        detailData:    null,

        // ── Status update modal ───────────────────────────────────────────────
        statusOpen:      false,
        statusLoading:   false,
        statusTarget:    null,   // { id, order_no, status }
        newStatus:       '',

        // ── Create/edit modal ─────────────────────────────────────────────────
        formOpen:    false,
        formLoading: false,
        formErrors:  {},
        globalError: null,
        form: {
            customer_id:   '',
            vehicle_plate: '',
            vehicle_type:  '',
            complaint:     '',
            diagnosis:     '',
            notes:         '',
            service_fee:   0,
            discount:      0,
            handled_by:    '',
        },

        // ── Init ──────────────────────────────────────────────────────────────
        async init() {
            await Promise.all([
                this.fetchProducts(),
                this.fetchCustomers(),
            ]);
        },

        // ── Fetch helpers ─────────────────────────────────────────────────────
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

        // ── Product & customer filter ─────────────────────────────────────────
        filterProducts() {
            const q = this.productSearch.toLowerCase();
            this.filteredProducts = this.allProducts.filter(p =>
                p.name.toLowerCase().includes(q) ||
                p.sku.toLowerCase().includes(q)
            );
        },

        filterCustomers() {
            const q = this.customerSearch.toLowerCase();
            this.filteredCustomers = this.allCustomers.filter(c =>
                c.name.toLowerCase().includes(q) ||
                (c.phone        ?? '').toLowerCase().includes(q) ||
                (c.vehicle_plate ?? '').toLowerCase().includes(q)
            );
        },

        selectCustomer(customer) {
            this.selectedCustomer      = customer;
            this.form.customer_id      = customer.id;
            this.form.vehicle_plate    = customer.vehicle_plate ?? '';
            this.form.vehicle_type     = customer.vehicle_type  ?? '';
            this.customerSearch        = customer.name;
            this.filteredCustomers     = [];
        },

        // ── Item management ───────────────────────────────────────────────────
        addItem(product) {
            const existing = this.items.find(i => i.product_id === product.id);
            if (existing) {
                existing.qty++;
            } else {
                this.items.push({
                    product_id:   product.id,
                    product_name: product.name,
                    product_sku:  product.sku,
                    qty:          1,
                    price:        product.price,
                });
            }
            this.productSearch    = '';
            this.filteredProducts = this.allProducts;
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        get itemsSubtotal() {
            return this.items.reduce((sum, i) => sum + (i.qty * i.price), 0);
        },

        get grandTotal() {
            return parseFloat(this.form.service_fee || 0)
                 + this.itemsSubtotal
                 - parseFloat(this.form.discount || 0);
        },

        formatRp(val) {
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        },

        // ── Open / close form modal ───────────────────────────────────────────
        openCreate() {
            this.form         = { customer_id:'', vehicle_plate:'', vehicle_type:'',
                                  complaint:'', diagnosis:'', notes:'',
                                  service_fee:0, discount:0, handled_by:'' };
            this.items           = [];
            this.selectedCustomer = null;
            this.customerSearch   = '';
            this.filteredCustomers = this.allCustomers;
            this.formErrors       = {};
            this.globalError      = null;
            this.formOpen         = true;
        },

        closeForm() {
            if (this.formLoading) return;
            this.formOpen = false;
        },

        // ── Submit work order ─────────────────────────────────────────────────
        async submitForm() {
            if (this.formLoading) return;
            this.formLoading = true;
            this.formErrors  = {};
            this.globalError = null;

            try {
                const payload = {
                    ...this.form,
                    items: this.items.map(i => ({
                        product_id: i.product_id,
                        qty:        i.qty,
                        price:      i.price,
                    })),
                };

                const res  = await fetch('/service-orders/store', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                const json = await res.json();

                if (!res.ok) {
                    if (res.status === 422) this.formErrors = json.errors ?? {};
                    throw new Error(json.message || 'Terjadi kesalahan.');
                }

                sessionStorage.setItem('alert', JSON.stringify({
                    type:    'success',
                    message: json.message || 'Work order berhasil dibuat.',
                }));

                window.location.reload();

            } catch (e) {
                if (!Object.keys(this.formErrors).length) {
                    this.globalError = e.message;
                }
            } finally {
                this.formLoading = false;
            }
        },

        // ── Detail modal ──────────────────────────────────────────────────────
        async openDetail(id) {
            this.detailOpen    = true;
            this.detailLoading = true;
            this.detailData    = null;

            try {
                const res  = await fetch(`/service-orders/${id}`);
                const json = await res.json();
                this.detailData = json.data ?? null;
            } catch (e) {
                console.error(e);
            } finally {
                this.detailLoading = false;
            }
        },

        closeDetail() { this.detailOpen = false; },

        // ── Status update modal ───────────────────────────────────────────────
        openStatusModal(order) {
            this.statusTarget = order;
            this.newStatus    = '';
            this.statusOpen   = true;
        },

        closeStatus() { this.statusOpen = false; },

        availableStatuses(currentStatus) {
            const map = {
                pending:     [{ value:'in_progress', label:'Mulai Kerjakan' }, { value:'cancelled', label:'Batalkan' }],
                in_progress: [{ value:'done', label:'Selesai' }, { value:'cancelled', label:'Batalkan' }],
            };
            return map[currentStatus] ?? [];
        },

        async submitStatus() {
            if (this.statusLoading || !this.newStatus) return;
            this.statusLoading = true;

            try {
                const res  = await fetch(`/service-orders/update-status/${this.statusTarget.id}`, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ status: this.newStatus }),
                });

                const json = await res.json();

                if (!res.ok) throw new Error(json.message || 'Gagal update status.');

                sessionStorage.setItem('alert', JSON.stringify({
                    type:    'success',
                    message: json.message || 'Status berhasil diubah.',
                }));

                window.location.reload();

            } catch (e) {
                window.showAlert?.('error', e.message);
            } finally {
                this.statusLoading = false;
            }
        },

        // ── Status badge helpers ──────────────────────────────────────────────
        statusLabel(status) {
            return { pending:'Pending', in_progress:'Dikerjakan', done:'Selesai', cancelled:'Dibatalkan' }[status] ?? status;
        },

        statusClass(status) {
            return {
                pending:     'badge-warning',
                in_progress: 'badge-info',
                done:        'badge-success',
                cancelled:   'badge-danger',
            }[status] ?? '';
        },
    };
}
