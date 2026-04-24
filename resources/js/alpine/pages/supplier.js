export default function supplierManager() {
    return {
        form: {
            id: null,
            name: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            is_active: true,
        },
        loading: false,

        handleCreate() {
            this.resetForm();
            this.open('supplier-form');
        },

        handleEdit(data) {
            this.form = {
                id: data.id,
                name: data.name,
                email: data.email,
                phone: data.phone,
                address: data.address,
                city: data.city,
                is_active: data.is_active,
            };
            this.open('supplier-form');
        },

        handleDelete(data) {
            if (!confirm(`Delete ${data.name}?`)) return;

            fetch(`/api/suppliers/${data.id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(res => {
                window.showAlert('success', res.message || 'Deleted');
                window.location.reload();
            });
        },

        submitForm() {
            this.loading = true;

            const method = this.form.id ? 'PUT' : 'POST';
            const url = this.form.id 
                ? `/api/suppliers/${this.form.id}` 
                : `/api/suppliers`;

            fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(res => {
                this.loading = false;
                this.close();

                window.showAlert('success', res.message || 'Success');
                window.location.reload();
            })
            .catch(() => this.loading = false);
        },

        resetForm() {
            this.form = {
                id: null,
                name: '',
                email: '',
                phone: '',
                address: '',
                city: '',
                is_active: true,
            };
        }
    }
}