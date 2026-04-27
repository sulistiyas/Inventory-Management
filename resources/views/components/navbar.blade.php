{{-- =====================================================
     layouts/partials/navbar.blade.php
     ===================================================== --}}
<header
  class="navbar"
  :class="navbarClass"
>
  {{-- Toggle button --}}
  <button
    class="navbar-toggle-btn"
    @click="toggleSidebar()"
    aria-label="Toggle sidebar"
  >
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <line x1="3" y1="6"  x2="21" y2="6"/>
      <line x1="3" y1="12" x2="21" y2="12"/>
      <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>

  {{-- Breadcrumb --}}
  <nav class="navbar-breadcrumb">
    <span>WarehouSe</span>
    <span class="separator">/</span>
    <span class="current">@yield('breadcrumb', 'Dashboard')</span>
  </nav>

  {{-- Right section --}}
  <div class="navbar-right">

    {{-- Notification bell --}}
    <div style="position:relative;" x-data="{ open: false }">
      <button
        class="navbar-icon-btn"
        @click="open = !open"
        aria-label="Notifications"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @php $lowStockCount = $lowStockCount ?? 0; @endphp
        @if($lowStockCount > 0)
          <span class="navbar-badge"></span>
        @endif
      </button>

      {{-- Notification dropdown --}}
      <div
        class="dropdown-menu"
        style="min-width:280px;"
        x-show="open"
        x-cloak
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
      >
        <div class="dropdown-header" style="display:flex; align-items:center; justify-content:space-between;">
          <span class="dropdown-header-name">Notifikasi</span>
          @if($lowStockCount > 0)
            <span class="badge badge-warning">{{ $lowStockCount }} low stock</span>
          @endif
        </div>

        @if($lowStockCount > 0)
          <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" class="dropdown-item">
          {{-- <a href="#" class="dropdown-item"> --}}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--warning)">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            {{ $lowStockCount }} produk stok rendah
          </a>
        @else
          <div class="dropdown-item" style="color:var(--text-muted); cursor:default;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            Tidak ada notifikasi baru
          </div>
        @endif
      </div>
    </div>

    {{-- Profile dropdown --}}
    <div style="position:relative;" x-data="{ open: false }">
      <button
        class="profile-btn"
        @click="open = !open"
        :aria-expanded="open"
      >
        {{-- Avatar with initials --}}
        <div class="profile-avatar">
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="profile-info">
          <div class="profile-name">{{ auth()->user()->name }}</div>
          <div class="profile-role">{{ auth()->user()->roleLabelAttribute ?? ucfirst(auth()->user()->role) }}</div>
        </div>
        <span class="profile-chevron" :class="{ 'open': open }">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </span>
      </button>

      {{-- Profile dropdown menu --}}
      <div
        class="dropdown-menu"
        x-show="open"
        x-cloak
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
      >
        <div class="dropdown-header">
          <div class="dropdown-header-name">{{ auth()->user()->name }}</div>
          <div class="dropdown-header-email">{{ auth()->user()->email }}</div>
        </div>

        {{-- <a href="{{ route('profile.show') }}" class="dropdown-item"> --}}
        <a href="#" class="dropdown-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          Profil Saya
        </a>

        @if(auth()->user()->isAdmin())
          {{-- <a href="{{ route('users.index') }}" class="dropdown-item"> --}}
          <a href="#" class="dropdown-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.07 4.93l-1.41 1.41M5.34 18.66l-1.41 1.41M3 12H1M23 12h-2M5.34 5.34L3.93 3.93M20.07 20.07l-1.41-1.41M12 21v2M12 1v2"/>
            </svg>
            Pengaturan
          </a>
        @endif

        <div class="dropdown-divider"></div>

        <form method="POST" action="{{ route('logout') }}">
        {{-- <form method="POST" action="#"> --}}
          @csrf
          <button type="submit" class="dropdown-item danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Keluar
          </button>
        </form>

      </div>
    </div>{{-- /profile --}}

  </div>{{-- .navbar-right --}}
</header>