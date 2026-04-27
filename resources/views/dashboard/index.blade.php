@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

{{-- ── Page header ─────────────────────────────────────────── --}}
<div class="page-header">
  <div>
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">
      Selamat datang, <strong>{{ auth()->user()->name }}</strong> —
      {{-- Selamat datang, <strong>Admin</strong> — --}}
      {{ now()->translatedFormat('l, d F Y') }}
    </p>
  </div>
  <div style="display:flex; gap:8px; flex-shrink:0;">
    {{-- <a href="#" class="btn btn-primary"> --}}
    <a href="{{ route('stock.in') }}" class="btn btn-primary">
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
              class="pill-tab"
              :class="{ 'active': selectedDays === {{ $d }} }"
              @click="changeDays({{ $d }})">
              {{ $label }}
            </button>
          @endforeach
        </div>
      </div>
    </div>

    <div class="chart-container" style="padding:16px 20px 20px; height:320px; position:relative;">
      <canvas x-ref="canvas"></canvas>
      <!-- 🔥 LOADING OVERLAY -->
      <div 
        x-show="loading"
        x-transition.opacity
        style="
          position:absolute;
          inset:0;
          background:rgba(255,255,255,0.7);
          display:flex;
          align-items:center;
          justify-content:center;
          backdrop-filter: blur(2px);
          z-index:10;
        "
      >
        <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
          
          <!-- spinner -->
          <div style="
            width:28px;
            height:28px;
            border:3px solid #e5e7eb;
            border-top-color:#3b82f6;
            border-radius:50%;
            animation: spin 0.8s linear infinite;
          "></div>

          <span style="font-size:12px; color:#6b7280;">Loading chart...</span>
        </div>
      </div>
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
          <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" style="font-size:12px; color:var(--info); font-weight:600; text-decoration:none;">
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
    isRendering: false,
    loading: false,
    chartData: @json($chartData),
    selectedDays: {{ $chartDays }},

    init() {
      if (window.__chartInitialized) {
        console.warn('Chart already initialized globally');
        return;
      }

      window.__chartInitialized = true;

      setTimeout(() => this.render(), 100);
    },
    async changeDays(days) {
      this.loading = true;
      // if (this.selectedDays === days) return;

      this.selectedDays = days;

      try {
        const res = await fetch(`/dashboard/chart?days=${days}`);
        const data = await res.json();

        this.chartData = JSON.parse(JSON.stringify(data));

        this.updateChart(); // 🔥 tanpa destroy total
        this.loading = false;
      } catch (e) {
        console.error('Failed fetch chart:', e);
        this.loading = false;
      }
    },

    render() {
      // Prevent multiple simultaneous renders
      if (this.isRendering) {
        console.log('render already in progress, skipping');
        return;
      }

      this.isRendering = true;
      console.log('render called');

      try {
        const canvas = this.$refs.canvas;
        console.log('canvas element:', canvas);

        if (!canvas) {
          console.error('Canvas element not found');
          return;
        }

        // Check if canvas is still in DOM
        if (!document.contains(canvas)) {
          console.error('Canvas is not in DOM');
          return;
        }

        const ctx = canvas.getContext('2d');
        console.log('canvas context:', ctx);

        if (!ctx) {
          console.error('Canvas context not available');
          return;
        }

        // Destroy existing chart before creating new one
        if (this.chart instanceof Chart) {
          this.chart.destroy();
        }

        console.log('Creating chart with data:', this.chartData);

        this.chart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: this.chartData.labels,
            datasets: [
              {
                label: 'Stok Masuk',
                data: this.chartData.in,
                backgroundColor: 'rgba(16,185,129,0.85)',
                borderColor: 'rgba(16,185,129,1)',
                borderWidth: 0,
                borderRadius: 4,
              },
              {
                label: 'Stok Keluar',
                data: this.chartData.out,
                backgroundColor: 'rgba(239,68,68,0.80)',
                borderColor: 'rgba(239,68,68,1)',
                borderWidth: 0,
                borderRadius: 4,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
              duration: 300,
            },
            plugins: {
              legend: {
                display: true,
                position: 'top',
                labels: {
                  boxWidth: 12,
                  padding: 16,
                  font: { size: 13 },
                  usePointStyle: false,
                },
              },
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1,
                  precision: 0,
                },
              },
            },
          },
        });

        console.log('Chart created successfully');
      } catch (error) {
        console.error('Error rendering chart:', error);
      } finally {
        this.isRendering = false;
      }
    },

    updateChart() {
      if (!this.chart) return;

      try {
        this.chart.data.labels = [...this.chartData.labels];
        this.chart.data.datasets[0].data = [...this.chartData.in];
        this.chart.data.datasets[1].data = [...this.chartData.out];

        this.chart.update();
      } catch (e) {
        console.warn('Update gagal, recreate chart');
        this.render(); // fallback
      }
    },
  };
}
</script>
@endpush