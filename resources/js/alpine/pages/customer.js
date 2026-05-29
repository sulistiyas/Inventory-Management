export default function customerManager() {
    return {
        async init() {
            // tidak ada meta yang perlu di-fetch
        },

        handleEdit(item) {
            window.dispatchEvent(new CustomEvent('fill-form', { detail: item }));
        },

        handleDelete(item) {
            window.dispatchEvent(new CustomEvent('open-delete', { detail: item }));
        },

        refreshTable() {
            window.dispatchEvent(new Event('refresh-datatable'));
        },
    };
}
