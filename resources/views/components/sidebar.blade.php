{{-- =====================================================
     components/sidebar.blade.php
     ===================================================== --}}
<aside
  class="sidebar"
  :class="sidebarClass"
>
  {{-- Brand --}}
  <a href="{{ route('dashboard') }}" class="sidebar-brand">
    <div class="sidebar-brand-icon">
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
    <a
       href="{{ route('owner.dashboard') }}"
       class="nav-item {{ request()->routeIs('owner.*') ? 'active' : '' }}"
       data-tooltip="Owner Dashboard">
       <span class="nav-item-icon">
         <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
           <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>
         </svg>
       </span>
       <span class="nav-item-label">Owner Dashboard</span>
    </a>

    {{-- ── POS ─────────────────────────────────────────────────────── --}}
    <div class="nav-section-label">POS</div>

    <a
      href="{{ route('sales.index') }}"
      class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}"
      data-tooltip="Kasir"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="3" width="20" height="14" rx="2"/>
          <path d="M8 21h8M12 17v4"/>
        </svg>
      </span>
      <span class="nav-item-label">Kasir</span>
    </a>

    <a
      href="{{ route('service-orders.index') }}"
      class="nav-item {{ request()->routeIs('service-orders.*') ? 'active' : '' }}"
      data-tooltip="Work Order Servis"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
      </span>
      <span class="nav-item-label">Work Order Servis</span>
      {{-- Badge: WO pending --}}
      @php $pendingWoCount = $pendingWoCount ?? 0; @endphp
      @if ($pendingWoCount > 0)
        <span class="nav-badge">{{ $pendingWoCount }}</span>
      @endif
    </a>

    <a
      href="{{ route('customers.index') }}"
      class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}"
      data-tooltip="Pelanggan"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </span>
      <span class="nav-item-label">Pelanggan</span>
    </a>

    {{-- ── Inventory ────────────────────────────────────────────────── --}}
    <div class="nav-section-label">Inventory</div>

    <a
      href="{{ route('products.index') }}"
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
      href="{{ route('suppliers.index') }}"
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

    {{-- ── Stok ─────────────────────────────────────────────────────── --}}
    <div class="nav-section-label">Stok</div>

    <a
      href="{{ route('stock.index') }}"
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
      href="{{ route('stock.in') }}"
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
      @php $lowStockCount = $lowStockCount ?? 0; @endphp
      @if ($lowStockCount > 0)
        <span class="nav-badge">{{ $lowStockCount }}</span>
      @endif
    </a>

    <a
      href="{{ route('stock.out') }}"
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

    {{-- ── Laporan ──────────────────────────────────────────────────── --}}
    <div class="nav-section-label">Laporan</div>

    <a
      href="{{ route('reports.index') }}"
      class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}"
      data-tooltip="Laporan"
    >
      <span class="nav-item-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="9" y1="13" x2="15" y2="13"/>
          <line x1="9" y1="17" x2="13" y2="17"/>
        </svg>
      </span>
      <span class="nav-item-label">Laporan</span>
    </a>

    {{-- ── Admin ────────────────────────────────────────────────────── --}}
    @if (auth()->user()->isAdmin())
      <div class="nav-section-label">Admin</div>

      <a
        href="{{ route('users.index') }}"
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

  {{-- Sidebar footer: collapse toggle (desktop only) --}}
  <div class="sidebar-footer" x-show="!isMobile">
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