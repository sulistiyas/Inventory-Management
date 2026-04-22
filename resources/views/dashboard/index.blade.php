@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

{{-- ── Page header ─────────────────────────────────────────── --}}
<div class="page-header">
  <div>
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">
      {{-- Selamat datang, <strong>{{ auth()->user()->name }}</strong> — --}}
      Selamat datang, <strong>Admin</strong> —
      {{ now()->translatedFormat('l, d F Y') }}
    </p>
  </div>
  <div style="display:flex; gap:8px; flex-shrink:0;">
    <a href="#" class="btn btn-primary">
    {{-- <a href="{{ route('stock.in') }}" class="btn btn-primary"> --}}
    <a href="#" class="btn btn-primary">
    {{-- <a href="{{ route('stock.in') }}" class="btn btn-primary"> --}}
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Transaksi Masuk
    </a>
    <a href="#" class="btn btn-secondary">
    {{-- <a href="{{ route('stock.out') }}" class="btn btn-secondary"> --}}
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Transaksi Keluar
    </a>
  </div>
</div>

{{-- ── Stat cards ──────────────────────────────────────────── --}}
<div class="stats-grid">

  {{-- Total Produk --}}
  <div class="stat-card">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Produk</span>
      <span class="stat-card-icon blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        </svg>
      </span>
    </div>
    <div class="stat-card-value">{{ number_format($totalProducts) }}</div>
    <div class="stat-card-footer">
      <a href="#" style="color:var(--info); font-weight:600; text-decoration:none; font-size:12px;">
      {{-- <a href="{{ route('products.index') }}" style="color:var(--info); font-weight:600; text-decoration:none; font-size:12px;"> --}}
        Lihat semua produk →
      </a>
    </div>
  </div>

  {{-- Total Stok --}}
  <div class="stat-card">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Stok</span>
      <span class="stat-card-icon green">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/>
          <line x1="6" y1="20" x2="6" y2="16"/>
        </svg>
      </span>
    </div>
    <div class="stat-card-value">{{ number_format($totalStock) }}</div>
    <div class="stat-card-footer">
      @if($lowStockCount > 0)
        <span class="stat-trend down">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
          {{ $lowStockCount }} low stock
        </span>
      @else
        <span class="stat-trend up">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
          Stok aman
        </span>
      @endif
    </div>
  </div>

  {{-- Total Supplier --}}
  <div class="stat-card">
    <div class="stat-card-header">
      <span class="stat-card-label">Total Supplier</span>
      <span class="stat-card-icon amber">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 4v5h-7V8z"/>
          <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
        </svg>
      </span>
    </div>
    <div class="stat-card-value">{{ number_format($totalSuppliers) }}</div>
    <div class="stat-card-footer">
      {{-- <a href="{{ route('suppliers.index') }}" style="color:var(--warning); font-weight:600; text-decoration:none; font-size:12px;"> --}}
      <a href="#" style="color:var(--warning); font-weight:600; text-decoration:none; font-size:12px;">
        Lihat semua supplier →
      </a>
    </div>
  </div>

  {{-- Transaksi Hari Ini --}}
  <div class="stat-card">
    <div class="stat-card-header">
      <span class="stat-card-label">Transaksi Hari Ini</span>
      <span class="stat-card-icon red">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
      </span>
    </div>
    <div class="stat-card-value">{{ number_format($todayMovementsCount) }}</div>
    <div class="stat-card-footer">
      <span class="text-muted text-sm">{{ now()->format('d M Y') }}</span>
    </div>
  </div>

</div>{{-- .stats-grid --}}


{{-- ── Row 2: Chart + Low Stock ────────────────────────────── --}}
<div class="content-grid">

  {{-- Stock Movement Chart --}}
  <div class="card" x-data="stockChart()" x-init="init()">
    <div class="card-header">
      <div>
        <div class="card-title">Grafik Stok Masuk & Keluar</div>
        <div class="card-subtitle">Pergerakan stok harian</div>
      </div>

      {{-- Period selector --}}
      <div class="card-actions">
        <div class="pill-tabs">
          @foreach([7 => '7H', 14 => '14H', 30 => '30H'] as $d => $label)
            <button
              class="pill-tab {{ $chartDays == $d ? 'active' : '' }}"
              onclick="window.location.href='{{ route('dashboard', ['days' => $d]) }}'"
            >
              {{ $label }}
            </button>
          @endforeach
        </div>
      </div>
    </div>

    <div class="chart-container" style="padding: 16px 20px 20px;">
      <canvas id="stockChart"></canvas>
    </div>
  </div>

  {{-- Low Stock Alert --}}
  <div class="card">
    <div class="card-header" style="padding-bottom:0;">
      <div>
        <div class="card-title">⚠ Stok Rendah</div>
        <div class="card-subtitle">Produk perlu restock</div>
      </div>
      @if($lowStockCount > 0)
        <span class="badge badge-warning">{{ $lowStockCount }}</span>
      @endif
    </div>

    @if($lowStockProducts->isEmpty())
      <div style="padding:32px 20px; text-align:center; color:var(--text-muted);">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 8px; display:block; opacity:0.4"><polyline points="20 6 9 17 4 12"/></svg>
        <div style="font-size:13px; font-weight:600;">Semua stok aman</div>
        <div style="font-size:12px; margin-top:2px;">Tidak ada produk di bawah minimum</div>
      </div>
    @else
      <div class="activity-list" style="margin-top:12px;">
        @foreach($lowStockProducts as $product)
          @php
            $pct = $product->min_stock > 0
              ? min(100, round(($product->stock / $product->min_stock) * 100))
              : 100;
          @endphp
          <div class="low-stock-item">
            <div class="low-stock-info">
              <div class="low-stock-name">{{ $product->name }}</div>
              <div class="low-stock-sku">{{ $product->sku }}</div>
            </div>
            <div class="stock-progress-wrap">
              <div class="stock-bar">
                <div class="stock-bar-fill" style="width:{{ $pct }}%;"></div>
              </div>
              <span class="stock-val">{{ $product->stock }}/{{ $product->min_stock }}</span>
            </div>
          </div>
        @endforeach
      </div>
      @if($lowStockCount > $lowStockProducts->count())
        <div style="padding:10px 20px; border-top:1px solid var(--border);">
          {{-- <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" style="font-size:12px; color:var(--info); font-weight:600; text-decoration:none;"> --}}
          <a href="#ow_stock']) }}" style="font-size:12px; color:var(--info); font-weight:600; text-decoration:none;">
            Lihat {{ $lowStockCount - $lowStockProducts->count() }} produk lainnya →
          </a>
        </div>
      @endif
    @endif
  </div>

</div>{{-- .content-grid --}}


{{-- ── Row 3: Recent Transactions Table ───────────────────── --}}
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Transaksi Terakhir</div>
      <div class="card-subtitle">5 pergerakan stok terbaru</div>
    </div>
    {{-- <a href="{{ route('stock.index') }}" class="btn btn-secondary btn-sm"> --}}
    <a href="#" class="btn btn-secondary btn-sm">
      Lihat Semua
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
  </div>

  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th>Tipe</th>
          <th style="text-align:right;">Qty</th>
          <th style="text-align:right;">Stok Setelah</th>
          <th>Operator</th>
          <th>Waktu</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentMovements as $movement)
          <tr>
            <td class="text-mono text-muted text-sm">{{ $movement->id }}</td>
            <td>
              <a
                {{-- href="{{ route('products.show', $movement->product_id) }}" --}}
                href="#"
                style="color:var(--text-primary); font-weight:600; text-decoration:none;"
              >
                {{ $movement->product->name ?? '—' }}
              </a>
              <div style="font-size:11px; color:var(--text-muted); font-family:var(--font-mono);">
                {{ $movement->product->sku ?? '' }}
              </div>
            </td>
            <td>
              <span class="badge badge-secondary">
                {{ $movement->product->category->name ?? '—' }}
              </span>
            </td>
            <td>
              <span class="badge {{ $movement->typeBadgeClassAttribute }}">
                <span style="width:6px; height:6px; border-radius:50%; background:currentColor; flex-shrink:0;"></span>
                {{ $movement->typeLabelAttribute }}
              </span>
            </td>
            <td style="text-align:right;">
              <span
                class="text-mono font-semibold"
                style="color: {{ $movement->type === 'in' ? 'var(--success)' : 'var(--danger)' }};"
              >
                {{ $movement->type === 'in' ? '+' : '-' }}{{ number_format($movement->quantity) }}
              </span>
            </td>
            <td style="text-align:right;" class="text-mono">
              {{ number_format($movement->stock_after) }}
            </td>
            <td>
              <div style="font-size:13px; font-weight:500;">{{ $movement->user->name }}</div>
            </td>
            <td>
              <div style="font-size:12px; color:var(--text-muted);" title="{{ $movement->created_at->format('d M Y H:i:s') }}">
                {{ $movement->created_at->diffForHumans() }}
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 8px; display:block; opacity:0.4"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/></svg>
              Belum ada transaksi
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection


@push('scripts')
<script>
/**
 * Alpine component: renders the Chart.js stock movement chart.
 * Chart data is server-rendered as JSON to avoid an extra AJAX call.
 */
function stockChart() {
  return {
    chart: null,

    chartData: @json($chartData),

    init() {
      this.$nextTick(() => this.render());
    },

    render() {
      const ctx = document.getElementById('stockChart');
      if (!ctx) return;
      if (this.chart) this.chart.destroy();

      // Pull CSS variables for consistent theming
      const style     = getComputedStyle(document.documentElement);
      const accent    = '#F59E0B';
      const success   = '#10B981';
      const danger    = '#EF4444';
      const gridColor = 'rgba(148,163,184,0.12)';
      const textColor = style.getPropertyValue('--text-muted').trim() || '#94A3B8';

      this.chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.chartData.labels,
          datasets: [
            {
              label: 'Stok Masuk',
              data: this.chartData.in,
              backgroundColor: 'rgba(16,185,129,0.85)',
              borderColor: success,
              borderWidth: 0,
              borderRadius: 5,
              borderSkipped: false,
            },
            {
              label: 'Stok Keluar',
              data: this.chartData.out,
              backgroundColor: 'rgba(239,68,68,0.80)',
              borderColor: danger,
              borderWidth: 0,
              borderRadius: 5,
              borderSkipped: false,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: {
              position: 'top',
              align: 'end',
              labels: {
                boxWidth: 12,
                boxHeight: 12,
                borderRadius: 3,
                useBorderRadius: true,
                font: { family: "'DM Sans', sans-serif", size: 12, weight: '600' },
                color: textColor,
                padding: 16,
              },
            },
            tooltip: {
              backgroundColor: '#0F172A',
              titleColor: '#E2E8F0',
              bodyColor: '#94A3B8',
              borderColor: '#1E293B',
              borderWidth: 1,
              padding: 12,
              cornerRadius: 8,
              titleFont: { family: "'DM Sans', sans-serif", size: 13, weight: '700' },
              bodyFont: { family: "'DM Sans', sans-serif", size: 12 },
              callbacks: {
                label(ctx) {
                  return `  ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('id-ID')} unit`;
                },
              },
            },
          },
          scales: {
            x: {
              grid: { display: false },
              border: { display: false },
              ticks: {
                color: textColor,
                font: { family: "'DM Sans', sans-serif", size: 12 },
              },
            },
            y: {
              beginAtZero: true,
              border: { display: false, dash: [4, 4] },
              grid: { color: gridColor, drawBorder: false },
              ticks: {
                color: textColor,
                font: { family: "'DM Sans', sans-serif", size: 12 },
                maxTicksLimit: 6,
                callback: (v) => v.toLocaleString('id-ID'),
              },
            },
          },
        },
      });
    },
  };
}
</script>
@endpush