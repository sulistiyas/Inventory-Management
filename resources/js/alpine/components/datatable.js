export default function datatable(config = {}) {
    return {
        data: [],
        loading: false,
        page: 1,
        perPage: config.perPage || 10,
        total: 0,
        lastPage: 1,
        search: '',
        columns: config.columns || [],
        apiEndpoint: config.apiEndpoint || '',
        filters: config.filters || {},

        async fetchData() {
            this.loading = true;

            try {
                const params = new URLSearchParams({
                    page: this.page,
                    perPage: this.perPage,
                    search: this.search
                });

                Object.entries(this.filters).forEach(([key, value]) => {
                    if (value) params.set(key, value);
                });

                const res = await fetch(`${this.apiEndpoint}?${params}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                // 🔥 HANDLE ERROR HTTP
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }

                const json = await res.json();

                this.data = json.data;
                this.total = json.meta.total;
                this.lastPage = json.meta.last_page;

            } catch (e) {
                console.error('Fetch error:', e);
            }

            this.loading = false;
        },

        changePage(page) {
            if (page < 1 || page > this.lastPage) return;
            this.page = page;
            this.fetchData();
        },

        get numberStart() {
            return (this.page - 1) * this.perPage;
        },

        edit(row) {
            window.dispatchEvent(new CustomEvent('fill-form', {
                detail: {
                    ...row,
                    password: ''
                }
            }));
        },

        delete(row) {
            if (row.is_me) return;
            window.dispatchEvent(new CustomEvent('open-delete', { detail: row }));
        },
        async toggle(url, row) {
            if (row.is_me) return;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const json = await res.json();

                if (res.ok) {
                    row.is_active = json.is_active;
                } else {
                    alert(json.message ?? 'Gagal update');
                }

            } catch {
                alert('Koneksi gagal');
            }
        },


        init() {
            this.$watch('search', () => {
                this.page = 1;
                this.fetchData();
            });

            this.$watch('filters', () => {
                this.page = 1;
                this.fetchData();
            }, { deep: true });


            this.fetchData();
        },

        getValue(obj, path) {
            return path.split('.').reduce((o, key) => o ? o[key] : null, obj);
        }
    }
}