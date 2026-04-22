{{-- ================================================================
     components/modal.blade.php
     Reusable modal shell — works with the modal() Alpine component.

     USAGE in any Blade view:
       <x-modal>
         <x-slot:body>
           <!-- your form fields here -->
         </x-slot:body>
         <x-slot:footer>
           <!-- optional custom footer -->
         </x-slot:footer>
       </x-modal>

     The parent x-data must include (or delegate to) modal().
     ================================================================ --}}

@props([
    'subtitle' => null,   {{-- Optional subtitle shown below the title --}}
    'hideFooter' => false,
])

{{-- ── Backdrop ────────────────────────────────────────────── --}}
<div
    x-show="isOpen"
    x-cloak
    class="modal-backdrop"
    @click.self="close()"

    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>

    {{-- ── Panel ─────────────────────────────────────────────── --}}
    <div
        class="modal-panel"
        :class="sizeClass"
        @click.stop
        role="dialog"
        aria-modal="true"
        :aria-label="title"

        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
    >

        {{-- ── Header ──────────────────────────────────────────── --}}
        <div class="modal-header">

            {{-- Mode icon --}}
            <div class="modal-header-icon" :class="mode">
                {{-- Create icon --}}
                <template x-if="isCreate">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </template>
                {{-- Edit icon --}}
                <template x-if="isEdit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                    </svg>
                </template>
                {{-- Delete icon --}}
                <template x-if="isDelete">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                </template>
                {{-- View icon --}}
                <template x-if="isView">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </template>
            </div>

            {{-- Title + subtitle --}}
            <div class="modal-title-group">
                <div class="modal-title" x-text="title"></div>
                @if($subtitle)
                    <div class="modal-subtitle">{{ $subtitle }}</div>
                @else
                    <div class="modal-subtitle" x-show="isEdit && record">
                        ID: <span class="text-mono" x-text="record?.id"></span>
                    </div>
                @endif
            </div>

            {{-- Close button --}}
            <button
                class="modal-close-btn"
                @click="close()"
                :disabled="isSubmitting"
                aria-label="Tutup modal"
                type="button"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>{{-- /.modal-header --}}

        {{-- ── Body ────────────────────────────────────────────── --}}
        <div class="modal-body">

            {{-- Global error banner --}}
            <template x-if="hasGlobalError">
                <div class="modal-global-error">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/>
                    </svg>
                    <span x-text="globalError"></span>
                </div>
            </template>

            {{-- ── DELETE mode: confirmation panel ─────────────── --}}
            <template x-if="isDelete">
                <div class="modal-delete-body">
                    <div class="modal-delete-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                    </div>
                    <div class="modal-delete-title" x-text="title"></div>
                    <p class="modal-delete-text">
                        Apakah Anda yakin ingin menghapus
                        <strong class="modal-delete-record" x-text="record?.name ?? record?.id ?? 'item ini'"></strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </template>

            {{-- ── CREATE / EDIT / VIEW mode: slot content ─────── --}}
            <template x-if="!isDelete">
                <div>{{ $body ?? '' }}</div>
            </template>

        </div>{{-- /.modal-body --}}

        {{-- ── Footer ──────────────────────────────────────────── --}}
        @unless($hideFooter)
        <div class="modal-footer">

            {{-- Left slot (e.g. "last updated" info) --}}
            @isset($footerLeft)
                <div class="modal-footer-left">{{ $footerLeft }}</div>
            @endisset

            {{-- Custom footer slot, or default buttons --}}
            @isset($footer)
                {{ $footer }}
            @else

                {{-- Cancel / Close --}}
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    @click="close()"
                    :disabled="isSubmitting"
                >
                    <template x-if="isView">
                        <span>Tutup</span>
                    </template>
                    <template x-if="!isView">
                        <span>Batal</span>
                    </template>
                </button>

                {{-- Submit — hidden in view mode --}}
                <template x-if="!isView">
                    <button
                        type="submit"
                        class="btn btn-sm"
                        :class="isDelete ? 'btn-danger' : 'btn-primary'"
                        :disabled="isSubmitting"
                    >
                        <template x-if="isSubmitting">
                            <span class="btn-spinner"></span>
                        </template>
                        <template x-if="!isSubmitting && isCreate">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                        </template>
                        <template x-if="!isSubmitting && isEdit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                        </template>
                        <template x-if="!isSubmitting && isDelete">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                            </svg>
                        </template>
                        <span x-text="submitLabel"></span>
                    </button>
                </template>

            @endisset
        </div>
        @endunless

    </div>{{-- /.modal-panel --}}
</div>{{-- /.modal-backdrop --}}