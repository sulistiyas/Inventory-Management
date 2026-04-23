{{-- =====================================================
     layouts/partials/alert.blade.php
     Usage: @include('layouts.partials.alert', ['type' => 'success', 'message' => '...'])
     ===================================================== --}}
<div
  x-data="{ show: true }"
  x-show="show"
  x-init="setTimeout(() => show = false, 4000)"
  x-transition:leave="transition ease-in duration-300"
  x-transition:leave-start="opacity-100 translate-y-0"
  x-transition:leave-end="opacity-0 -translate-y-2"
  style="
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    border-radius: var(--radius-md);
    margin-bottom: 20px;
    font-size: 13.5px;
    font-weight: 500;
    border: 1px solid;
    {{ $type === 'success' ? 'background:var(--success-bg); color:#065F46; border-color:#A7F3D0;' : '' }}
    {{ $type === 'error'   ? 'background:var(--danger-bg);  color:#991B1B; border-color:#FCA5A5;' : '' }}
    {{ $type === 'warning' ? 'background:var(--warning-bg); color:#92400E; border-color:#FCD34D;' : '' }}
    {{ $type === 'info'    ? 'background:var(--info-bg);    color:#1E40AF; border-color:#93C5FD;' : '' }}
  "
  x-cloak
>
  <div style="display:flex; align-items:center; gap:10px;">
    @if($type === 'success')
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    @elseif($type === 'error')
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    @elseif($type === 'warning')
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    @else
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    @endif
    <span x-text="message"></span>
  </div>
  <button @click="show = false" style="background:none; border:none; cursor:pointer; opacity:0.6; padding:0;" aria-label="Close">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
  </button>
</div>