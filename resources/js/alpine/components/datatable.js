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

        async fetchData() {
            this.loading = true;

            try {
                const params = new URLSearchParams({
                    page: this.page,
                    perPage: this.perPage,
                    search: this.search
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

        init() {
            this.$watch('search', () => {
                this.page = 1;
                this.fetchData();
            });

            this.fetchData();
        },

        getValue(obj, path) {
            return path.split('.').reduce((o, key) => o ? o[key] : null, obj);
        }
    }
}