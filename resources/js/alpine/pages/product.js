export default function productManager() {
    return {
        meta: {
            categories: [],
            suppliers: []
        },

        async init() {
            await this.fetchMeta()
        },

        async fetchMeta() {
            try {
                const [cat, sup] = await Promise.all([
                    fetch('/categories/api/data').then(r => r.json()),
                    fetch('/suppliers/api/data').then(r => r.json())
                ])

                this.meta.categories = cat.data || []
                this.meta.suppliers  = sup.data || []

                // ✅ expose ke window agar bisa diakses scope Alpine lain
                window.__productMeta = this.meta

                // ✅ dispatch event supaya scope modal bisa reaktif
                window.dispatchEvent(new CustomEvent('meta-loaded', {
                    detail: this.meta
                }))

            } catch (e) {
                console.error('Meta load error:', e)
            }
        },

        async handleEdit(item) {
            try {
                const res    = await fetch(`/products/${item.id}`)
                const result = await res.json()

                window.dispatchEvent(new CustomEvent('fill-form', {
                    detail: result.data
                }))

            } catch (e) {
                console.error('Edit error:', e)
            }
        },

        handleDelete(item) {
            window.dispatchEvent(new CustomEvent('open-delete', {
                detail: item
            }))
        },

        refreshTable() {
            window.dispatchEvent(new Event('refresh-datatable'))
        }
    }
}