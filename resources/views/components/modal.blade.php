{{-- ============================================================
     modal.blade.php
     - Semua Alpine.js attributes 100% tidak diubah
     - Class diupdate ke nama baru yang tidak konflik dengan app.css:
         .btn-cancel  → .modal-btn-cancel
         .btn-submit  → .modal-btn-submit
         h3 (generic) → pakai class .modal-title
     ============================================================ --}}

<div
    x-show="open"
    x-transition
    class="modal-backdrop"
    style="display: none;"
>
    <div class="modal-box" @click.outside="close()">

        {{-- HEADER --}}
        <div class="modal-header">
            <h3 class="modal-title" x-text="title"></h3>
            <button class="modal-close-btn" @click="close()">✕</button>
        </div>

        {{-- BODY --}}
        <div class="modal-body">

            {{-- FORM (CREATE / EDIT) --}}
            <template x-if="mode !== 'delete'">
                <div>
                    {{ $slot }}
                </div>
            </template>

            {{-- DELETE CONFIRM --}}
            <template x-if="mode === 'delete'">
                <div class="modal-delete-confirm">
                    <div class="modal-delete-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                            <path d="M10 11v6M14 11v6"></path>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                        </svg>
                    </div>
                    <span class="modal-delete-text">Yakin ingin menghapus data ini?</span>
                    <span class="modal-delete-sub">Tindakan ini tidak dapat dibatalkan.</span>
                </div>
            </template>

        </div>

        {{-- FOOTER --}}
        <div class="modal-footer">
            <button @click="close()" class="modal-btn-cancel">Cancel</button>

            <button
                @click="submit()"
                :disabled="loading"
                class="modal-btn-submit"
                :class="{ 'is-delete': mode === 'delete' }"
                x-text="mode === 'delete' ? 'Delete' : 'Save'"
            ></button>
        </div>

    </div>
</div>