export default function modal(config = {}) {
    return {
        open: false,
        mode: 'create', // create | edit | delete
        title: '',
        form: {},
        errors: {},
        loading: false,

        endpoint: config.endpoint || '',
        method: 'POST',
        id: null,

        openCreate() {
            this.mode = 'create';
            this.title = config.createTitle || 'Create Data';
            this.form = config.defaultForm ? config.defaultForm() : {};
            this.errors = {};
            this.method = 'POST';
            this.open = true;
        },

        openEdit(data) {
            this.mode = 'edit';
            this.title = config.editTitle || 'Edit Data';
            this.form = { ...data };
            this.id = data.id;
            this.errors = {};
            this.method = 'PUT';
            this.open = true;
        },

        openDelete(data) {
            this.mode = 'delete';
            this.title = config.deleteTitle || 'Delete Data';
            this.id = data.id;
            this.form = data;
            this.open = true;
        },

        close() {
            this.open = false;
            this.form = {};
            this.errors = {};
            this.id = null;
        },

        async submit() {
            if (this.loading) return;

            this.loading = true;
            this.errors = {};

            try {
                const token = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content');

                let url = this.endpoint;
                let payload = { ...this.form };

                if (this.mode === 'edit') {
                    url = `${this.endpoint}/${this.id}`;
                    payload._method = 'PUT';
                }

                if (this.mode === 'delete') {
                    url = `${this.endpoint}/${this.id}`;
                    payload = { _method: 'DELETE' };
                }

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                // 🔥 INI POSISINYA DI SINI
                if (!res.ok) {
                    if (res.status === 422) {
                        this.errors = json.errors || {};
                    }
                    throw new Error(json.message || 'Error');
                }

                // ✅ SUCCESS
                sessionStorage.setItem('alert', JSON.stringify({
                    type: 'success',
                    message: json.message || 'Success'
                }));

                window.location.reload();

            } catch (e) {
                console.error(e);

                if (!this.errors || Object.keys(this.errors).length === 0) {
                    window.showAlert?.('error', e.message || 'Error');
                }
            }

            this.loading = false;
        }
    }
}