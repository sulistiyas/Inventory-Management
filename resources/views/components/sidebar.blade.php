{{-- =====================================================
     layouts/partials/sidebar.blade.php
     ===================================================== --}}
<aside
  class="sidebar"
  :class="sidebarClass"
>
  {{-- Brand --}}
  <a href="{{ route('dashboard') }}" class="sidebar-brand">
    <div class="sidebar-brand-icon">
      {{-- Warehouse icon --}}
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
    </div>
    <div class="sidebar-brand-text">
      <span class="sidebar-brand-name">WarehouSe</span>
      <span class="sidebar-brand-sub">Inventory System</span>
    </div>
  </a>

  {{-- Navigation --}}
  <nav class="sidebar-nav">

    {{-- Main --}}
    <div class="nav-section-label">Main</div>

    <a
      href="{{ route('dashboard') }}"
      class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
      data-tooltip="Dashboard"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
          <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
      </span>
      <span class="nav-item-label">Dashboard</span>
    </a>

    {{-- Inventory --}}
    <div class="nav-section-label">Inventory</div>

    <a
    {{-- href="{{ route('products.index') }}" --}}
    href="#"
    class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}"
      data-tooltip="Produk"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        </svg>
      </span>
      <span class="nav-item-label">Produk</span>
    </a>

    <a
      href="{{ route('categories.index') }}"
      {{-- href="#" --}}
      class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}"
      data-tooltip="Kategori"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
          <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
          <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
        </svg>
      </span>
      <span class="nav-item-label">Kategori</span>
    </a>

    <a
      {{-- href="{{ route('suppliers.index') }}" --}}
      href="#"
      class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
      data-tooltip="Supplier"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 4v5h-7V8z"/>
          <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
        </svg>
      </span>
      <span class="nav-item-label">Supplier</span>
    </a>

    {{-- Stock --}}
    <div class="nav-section-label">Stok</div>

    <a
      {{-- href="{{ route('stock.index') }}" --}}
      href="#"
      class="nav-item {{ request()->routeIs('stock.index') ? 'active' : '' }}"
      data-tooltip="Manajemen Stok"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/>
          <line x1="6" y1="20" x2="6" y2="16"/>
        </svg>
      </span>
      <span class="nav-item-label">Manajemen Stok</span>
    </a>

    <a
      {{-- href="{{ route('stock.in') }}" --}}
      href="#"
      class="nav-item {{ request()->routeIs('stock.in') ? 'active' : '' }}"
      data-tooltip="Transaksi Masuk"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/>
          <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
      </span>
      <span class="nav-item-label">Transaksi Masuk</span>
      {{-- Badge: low stock count alert --}}
      @php $lowStockCount = $lowStockCount ?? 0; @endphp
      @if ($lowStockCount > 0)
        <span class="nav-badge">{{ $lowStockCount }}</span>
      @endif
    </a>

    <a
      {{-- href="{{ route('stock.out') }}" --}}
      href="#"
      class="nav-item {{ request()->routeIs('stock.out') ? 'active' : '' }}"
      data-tooltip="Transaksi Keluar"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
      </span>
      <span class="nav-item-label">Transaksi Keluar</span>
    </a>

    {{-- Admin --}}
    @if (auth()->user()->isAdmin())
      <div class="nav-section-label">Admin</div>

      <a
        {{-- href="{{ route('users.index') }}" --}}
        href="#"
        class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
        data-tooltip="User Management"
      >
        <span class="nav-item-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </span>
        <span class="nav-item-label">User Management</span>
      </a>
    @endif

  </nav>

  {{-- Sidebar footer: collapse toggle --}}
  <div class="sidebar-footer">
    <button
      class="nav-item"
      style="width:100%; cursor:pointer; border:none; background:transparent; font-family:inherit;"
      @click="toggleSidebar()"
      data-tooltip="Toggle Sidebar"
    >
      <span class="nav-item-icon">
        <svg
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          :style="collapsed ? 'transform:rotate(180deg)' : ''"
          style="transition: transform 220ms ease;"
        >
          <path d="M11 19l-7-7 7-7"/><path d="M19 19l-7-7 7-7"/>
        </svg>
      </span>
      <span class="nav-item-label">Collapse</span>
    </button>
  </div>

</aside>