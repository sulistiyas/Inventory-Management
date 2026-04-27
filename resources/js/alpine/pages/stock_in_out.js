export default function stockForm(type = 'in', products = []) {
    return {
        type,

        loading: false,
        globalError: null,
        errors: {},
        selectedProduct: null,

        products,
        productSearch: '',
        filteredProducts: [],

        form: {
            product_id: '',
            quantity: 1,
            notes: '',
        },

        init() {
            // auto trigger saat product berubah
            this.filteredProducts = this.products.map(p => ({
                ...p,
                is_low: p.stock <= p.min_stock
            }));

            this.$watch('form.product_id', () => this.setProduct());
        },

        filterProducts() {
            const q = this.productSearch.toLowerCase();

            this.filteredProducts = this.products
                .filter(p =>
                    p.name.toLowerCase().includes(q) ||
                    p.sku.toLowerCase().includes(q)
                )
                .map(p => ({
                    ...p,
                    is_low: p.stock <= p.min_stock
                }));
        },

        selectProduct(product) {
            this.form.product_id = product.id;
            this.selectedProduct = {
                ...product,
                isLow: product.stock <= product.min_stock,
            };
        },

        setProduct() {
            const product = this.products.find(p => p.id == this.form.product_id);

            if (!product) {
                this.selectedProduct = null;
                return;
            }

            this.selectedProduct = {
                ...product,
                isLow: product.stock <= product.min_stock,
            };

            this.form.quantity = 1;
        },

        get stockAfter() {
            if (!this.selectedProduct) return 0;

            if (this.type === 'in') {
                return this.selectedProduct.stock + (parseInt(this.form.quantity) || 0);
            }

            return this.selectedProduct.stock - (parseInt(this.form.quantity) || 0);
        },

        increment() {
            if (this.type === 'out' && this.selectedProduct) {
                if (this.form.quantity >= this.selectedProduct.stock) return;
            }
            this.form.quantity++;
        },

        decrement() {
            this.form.quantity = Math.max(1, this.form.quantity - 1);
        },

        async submit(url, redirectUrl) {
            if (this.loading) return;

            this.loading = true;
            this.errors = {};
            this.globalError = null;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });

                const json = await res.json();

                if (!res.ok) {
                    if (res.status === 422) {
                        this.errors = json.errors;
                        return;
                    }
                    this.globalError = json.message ?? 'Terjadi kesalahan.';
                    return;
                }

                sessionStorage.setItem('alert', JSON.stringify({
                    type: 'success',
                    message: json.message ?? 'Berhasil disimpan.',
                }));

                window.location.href = redirectUrl;

            } catch (e) {
                this.globalError = 'Koneksi gagal. Silakan coba lagi.';
            } finally {
                this.loading = false;
            }
        }
    }
}