/**
 * ============================================================
 * modal.js — Reusable Alpine.js Modal System
 * Warehouse Inventory Management System
 *
 * HOW TO USE:
 *   import modal from './modal.js'  (if using bundler)
 *   OR load via <script src="...modal.js"></script>
 *   then register: document.addEventListener('alpine:init', () => {
 *     Alpine.data('modal', modal)
 *   })
 *
 * FEATURES:
 *   - Generic modal state management
 *   - Built-in form state (data, errors, loading)
 *   - Create / Edit / Delete modes
 *   - Global event bus (open/close from anywhere)
 *   - Focus trap + keyboard (Escape to close)
 *   - Body scroll lock
 *   - Confirm dialog helper
 *   - Per-field error clearing
 * ============================================================
 */

export default function modal() {
    return {

        // ── State ────────────────────────────────────────────────
        isOpen:       false,
        mode:         'create',   // 'create' | 'edit' | 'delete' | 'view'
        title:        '',
        size:         'md',       // 'sm' | 'md' | 'lg' | 'xl' | 'full'
        isSubmitting: false,
        isDirty:      false,

        // Form data — consumer fills this via openCreate()/openEdit()
        form:   {},
        errors: {},

        // The record being edited (raw object from server)
        record: null,

        // Optional callbacks — set by the parent component
        _onSuccess: null,
        _onCancel:  null,

        // ── Init ─────────────────────────────────────────────────
        init() {
            // Global event bus — open from anywhere in the page
            this.$el.addEventListener('modal:open', (e) => {
                const { mode, title, size, form, record } = e.detail ?? {};
                this._openWith({ mode, title, size, form, record });
            });

            this.$el.addEventListener('modal:close', () => this.close());

            // Escape key
            this._escHandler = (e) => {
                if (e.key === 'Escape' && this.isOpen && !this.isSubmitting) {
                    this.close();
                }
            };
            document.addEventListener('keydown', this._escHandler);
        },

        destroy() {
            document.removeEventListener('keydown', this._escHandler);
            this._unlockScroll();
        },

        // ── Open helpers ─────────────────────────────────────────

        /**
         * Open in CREATE mode.
         * @param {object} options - { title, size, defaults }
         */
        openCreate(options = {}) {
            this._openWith({
                mode:   'create',
                title:  options.title  ?? 'Add New',
                size:   options.size   ?? 'md',
                form:   options.defaults ?? {},
                record: null,
            });
        },

        /**
         * Open in EDIT mode — pre-populate form from record.
         * @param {object} record  - The data object to edit
         * @param {object} options - { title, size, fields }
         *   fields: array of keys to copy from record into form
         *           (omit to copy all enumerable keys)
         */
        openEdit(record, options = {}) {
            const fields = options.fields ?? Object.keys(record);
            const form   = {};
            fields.forEach(key => { form[key] = record[key] ?? ''; });

            this._openWith({
                mode:   'edit',
                title:  options.title ?? 'Edit',
                size:   options.size  ?? 'md',
                form,
                record,
            });
        },

        /**
         * Open in VIEW mode (read-only).
         */
        openView(record, options = {}) {
            this._openWith({
                mode:   'view',
                title:  options.title ?? 'Detail',
                size:   options.size  ?? 'md',
                form:   {},
                record,
            });
        },

        /**
         * Open in DELETE/CONFIRM mode.
         */
        openDelete(record, options = {}) {
            this._openWith({
                mode:   'delete',
                title:  options.title ?? 'Confirm Delete',
                size:   options.size  ?? 'sm',
                form:   {},
                record,
            });
        },

        // ── Internal open ────────────────────────────────────────
        _openWith({ mode, title, size, form, record }) {
            this.mode    = mode   ?? 'create';
            this.title   = title  ?? '';
            this.size    = size   ?? 'md';
            this.form    = this._deepClone(form   ?? {});
            this.record  = this._deepClone(record ?? null);
            this.errors  = {};
            this.isDirty = false;
            this.isOpen  = true;

            this._lockScroll();
            this.$nextTick(() => this._focusFirst());
        },

        // ── Close ────────────────────────────────────────────────
        close() {
            if (this.isSubmitting) return;

            this.isOpen  = false;
            this.errors  = {};
            this.isDirty = false;
            this._unlockScroll();

            if (typeof this._onCancel === 'function') {
                this._onCancel();
                this._onCancel = null;
            }
        },

        // ── Form helpers ─────────────────────────────────────────

        /**
         * Set a field value and mark form dirty.
         */
        setField(key, value) {
            this.form[key] = value;
            this.isDirty   = true;
            // Clear the error for this field on change
            if (this.errors[key]) {
                delete this.errors[key];
            }
        },

        /**
         * Clear all errors, or a specific field's errors.
         */
        clearErrors(field = null) {
            if (field) {
                delete this.errors[field];
            } else {
                this.errors = {};
            }
        },

        /**
         * Set server-returned validation errors.
         * Accepts Laravel's { field: ['msg'] } shape.
         */
        setErrors(errors) {
            this.errors = errors ?? {};
        },

        /**
         * Get the first error message for a field, or null.
         */
        getError(field) {
            const err = this.errors[field];
            if (!err) return null;
            return Array.isArray(err) ? err[0] : err;
        },

        /**
         * True if a given field has an error.
         */
        hasError(field) {
            return !!this.errors[field];
        },

        // ── Submit wrapper ───────────────────────────────────────

        /**
         * Generic submit handler.
         * Wraps any async function with loading/error state.
         *
         * @param {Function} fn    - async () => response data
         * @param {Function} onOk  - callback(data) on success
         */
        async submit(fn, onOk = null) {
            if (this.isSubmitting) return;

            this.isSubmitting = true;
            this.errors       = {};

            try {
                const data = await fn();

                this.isSubmitting = false;
                this.close();

                if (typeof onOk === 'function')          onOk(data);
                if (typeof this._onSuccess === 'function') {
                    this._onSuccess(data);
                    this._onSuccess = null;
                }

            } catch (err) {
                this.isSubmitting = false;

                // Laravel validation errors (422)
                if (err?.errors) {
                    this.errors = err.errors;
                    return;
                }

                // Generic error message
                this.errors = {
                    _global: [err?.message ?? 'Something went wrong. Please try again.']
                };
            }
        },

        // ── HTTP helpers (fetch wrappers) ─────────────────────────

        /**
         * Build fetch options with CSRF + JSON headers.
         */
        _fetchOptions(method, body = null) {
            const opts = {
                method,
                headers: {
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
            };
            if (body !== null) opts.body = JSON.stringify(body);
            return opts;
        },

        /**
         * Fetch + parse JSON, throw structured error on failure.
         */
        async _fetch(url, options) {
            const response = await fetch(url, options);
            let json;

            try {
                json = await response.json();
            } catch {
                throw { message: `Server error (${response.status})` };
            }

            if (!response.ok) {
                throw json; // Let submit() catch errors / validation
            }

            return json;
        },

        /**
         * POST form data to a URL.
         */
        async postTo(url, data = null) {
            return this._fetch(url, this._fetchOptions('POST', data ?? this.form));
        },

        /**
         * PUT form data to a URL (edit).
         */
        async putTo(url, data = null) {
            return this._fetch(url, this._fetchOptions('PUT', data ?? this.form));
        },

        /**
         * DELETE request.
         */
        async deleteTo(url) {
            return this._fetch(url, this._fetchOptions('DELETE'));
        },

        /**
         * GET and return JSON.
         */
        async getFrom(url) {
            return this._fetch(url, this._fetchOptions('GET'));
        },

        // ── Confirm dialog ───────────────────────────────────────

        /**
         * Show a native confirm and return bool.
         * Can be swapped for a custom confirm modal later.
         */
        confirm(message = 'Are you sure?') {
            return window.confirm(message);
        },

        // ── Computed ─────────────────────────────────────────────

        get isCreate() { return this.mode === 'create'; },
        get isEdit()   { return this.mode === 'edit'; },
        get isDelete() { return this.mode === 'delete'; },
        get isView()   { return this.mode === 'view'; },
        get isReadOnly() { return this.mode === 'view' || this.mode === 'delete'; },

        get hasGlobalError() { return !!this.errors._global; },
        get globalError()    { return this.errors._global?.[0] ?? null; },

        /** CSS width class for the modal panel */
        get sizeClass() {
            return {
                sm:   'modal-size-sm',
                md:   'modal-size-md',
                lg:   'modal-size-lg',
                xl:   'modal-size-xl',
                full: 'modal-size-full',
            }[this.size] ?? 'modal-size-md';
        },

        /** Submit button label */
        get submitLabel() {
            if (this.isSubmitting) return 'Menyimpan...';
            return {
                create: 'Simpan',
                edit:   'Update',
                delete: 'Hapus',
                view:   'Tutup',
            }[this.mode] ?? 'OK';
        },

        // ── Scroll lock ──────────────────────────────────────────
        _lockScroll() {
            document.body.style.overflow = 'hidden';
        },

        _unlockScroll() {
            document.body.style.overflow = '';
        },

        // ── Focus trap ───────────────────────────────────────────
        _focusFirst() {
            const panel   = this.$el.querySelector('.modal-panel');
            const focusable = panel?.querySelector(
                'input:not([disabled]):not([type=hidden]), ' +
                'select:not([disabled]), ' +
                'textarea:not([disabled]), ' +
                'button:not([disabled])'
            );
            focusable?.focus();
        },

        // ── Utilities ────────────────────────────────────────────
        _deepClone(obj) {
            if (obj === null || obj === undefined) return obj;
            try {
                return JSON.parse(JSON.stringify(obj));
            } catch {
                return { ...obj };
            }
        },
    };
}

// ── Auto-register if Alpine is already on the page ──────────
if (typeof window !== 'undefined' && window.Alpine) {
    window.Alpine.data('modal', modal);
}

// ── Also expose on window for non-module usage ───────────────
if (typeof window !== 'undefined') {
    window.warehouseModal = modal;
}