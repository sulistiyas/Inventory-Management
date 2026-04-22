<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Dashboard') — WarehouSe</title>
  @vite(['resources/js/app.js'])

  {{-- Favicon --}}
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%23F59E0B'/><path d='M8 22V12l8-4 8 4v10l-8 4-8-4z' fill='none' stroke='white' stroke-width='1.5'/></svg>" />

  {{-- CSS --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />

  {{-- Alpine.js --}}
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  {{-- Chart.js --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  @stack('styles')
</head>

{{-- Alpine root: sidebar collapse state persisted in localStorage --}}
<body
  x-data="appShell()"
  x-init="init()"
  :class="{ 'sidebar-is-collapsed': collapsed }"
>

  {{-- Mobile sidebar overlay --}}
  <div
    class="sidebar-overlay"
    :class="{ 'visible': mobileOpen }"
    @click="mobileOpen = false"
    x-cloak
  ></div>

  <div class="layout-shell">

    {{-- ── Sidebar ───────────────────────────────────────────── --}}
    @include('components.sidebar')

    {{-- ── Main area ────────────────────────────────────────────── --}}
    <div
      class="main-content"
      :class="{ 'sidebar-collapsed': collapsed }"
    >
      {{-- ── Navbar ─────────────────────────────────────────────── --}}
      @include('components.navbar')

      {{-- ── Page content ──────────────────────────────────────── --}}
      <div class="content-area">

        {{-- Flash messages --}}
        @if (session('success'))
          @include('components.alert', ['type' => 'success', 'message' => session('success')])
        @endif
        @if (session('error'))
          @include('components.alert', ['type' => 'error', 'message' => session('error')])
        @endif

        @yield('content')
      </div>
    </div>

  </div>{{-- .layout-shell --}}

  {{-- ── Global Alpine store + shell logic ─────────────────── --}}
  <script>
    function appShell() {
      return {
        collapsed:  localStorage.getItem('sidebar_collapsed') === 'true',
        mobileOpen: false,

        init() {
          // Re-check on resize
          window.addEventListener('resize', () => {
            if (window.innerWidth > 768) this.mobileOpen = false;
          });
        },

        toggleSidebar() {
          if (window.innerWidth <= 768) {
            this.mobileOpen = !this.mobileOpen;
          } else {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar_collapsed', this.collapsed);
          }
        },

        get sidebarClass() {
          return {
            'collapsed':   this.collapsed,
            'mobile-open': this.mobileOpen,
          };
        },

        get navbarClass() {
          return { 'sidebar-collapsed': this.collapsed };
        },
      };
    }
  </script>

  @stack('scripts')
</body>
</html>