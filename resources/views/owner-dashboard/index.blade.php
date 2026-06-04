@extends('layouts.app')

@section('title', 'Dashboard Owner')

@section('content')

<div x-data="ownerDashboard()" x-init="init()">

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<div class="page-header" style="flex-wrap:wrap;gap:.75rem;">
    <div>
        <h1 class="page-title">Dashboard Owner</h1>
        <p class="page-subtitle">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
        {{-- Date picker laporan harian --}}
        <input type="date"
               x-model="selectedDate"
               @change="refreshDailyStats()"
               max="{{ today()->toDateString() }}"
               style="height:36px;padding:0 10px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
        <a href="{{ route('owner.sales-report') }}" class="btn btn-secondary btn-sm">
            📊 Laporan Penjualan
        </a>
        <a href="{{ route('sales.index') }}" class="btn btn-primary">
            💳 Buka Kasir
        </a>
    </div>
</div>

{{-- ── Stat cards harian ────────────────────────────────────────────────── --}}
<div class="stats-grid">

    {{-- Omzet hari ini --}}
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Omzet Hari Ini</span>
            <span class="stat-card-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </span>
        </div>
        <div class="stat-card-value" x-text="loading ? '...' : 'Rp ' + Number(stats.daily_revenue).toLocaleString('id-ID')">
            Rp {{ number_format($daily_revenue, 0, ',', '.') }}
        </div>
        <div class="stat-card-footer">
            <span class="text-muted text-sm" x-text="selectedDate"></span>
        </div>
    </div>

    {{-- Transaksi --}}
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Transaksi</span>
            <span class="stat-card-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </span>
        </div>
        <div class="stat-card-value" x-text="loading ? '...' : stats.daily_transaction_count">
            {{ $daily_transaction_count }}
        </div>
        <div class="stat-card-footer">
            <span class="text-muted text-sm">Penjualan terkonfirmasi</span>
        </div>
    </div>

    {{-- Work order aktif --}}
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Work Order Aktif</span>
            <span class="stat-card-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </span>
        </div>
        <div class="stat-card-value" x-text="loading ? '...' : stats.daily_service_count">
            {{ $daily_service_count }}
        </div>
        <div class="stat-card-footer">
            <a href="{{ route('service-orders.index') }}" style="color:var(--warning);font-weight:600;text-decoration:none;font-size:12px;">
                Lihat work order →
            </a>
        </div>
    </div>

    {{-- Stok rendah --}}
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Stok Perlu Restock</span>
            <span class="stat-card-icon red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </span>
        </div>
        <div class="stat-card-value" style="{{ $low_stock_count > 0 ? 'color:var(--danger)' : '' }}">
            {{ $low_stock_count }}
        </div>
        <div class="stat-card-footer">
            @if($low_stock_count > 0)
                <span class="stat-trend down">⚠ Perlu perhatian</span>
            @else
                <span class="stat-trend up">✓ Stok aman</span>
            @endif
        </div>
    </div>

</div>{{-- stats-grid --}}


{{-- ── Row 2: Grafik omzet ─────────────────────────────────────────────── --}}
<div class="card" x-data="revenueChart()">
    <div class="card-header">
        <div>
            <div class="card-title">Omzet & Transaksi Harian</div>
            <div class="card-subtitle">Performa penjualan dalam N hari terakhir</div>
        </div>
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
    <div class="chart-container" style="padding:16px 20px 20px;height:300px;position:relative;">
        <canvas x-ref="canvas"></canvas>
        <div x-show="loading" x-transition.opacity
             style="position:absolute;inset:0;background:rgba(255,255,255,.7);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(2px);z-index:10;">
            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border:3px solid #e5e7eb;border-top-color:#3b82f6;border-radius:50%;animation:spin .8s linear infinite;"></div>
                <span style="font-size:12px;color:#6b7280;">Memuat grafik...</span>
            </div>
        </div>
    </div>
</div>


{{-- ── Row 3: Work Order aktif + Stok rendah ───────────────────────────── --}}
<div class="content-grid">

    {{-- Work Order aktif --}}
    <div class="card">
        <div class="card-header" style="padding-bottom:0;">
            <div>
                <div class="card-title">🔧 Work Order Aktif</div>
                <div class="card-subtitle">Pending & sedang dikerjakan</div>
            </div>
            @if(($service_order_summary['pending'] + $service_order_summary['in_progress']) > 0)
                <span class="badge badge-warning">
                    {{ $service_order_summary['pending'] + $service_order_summary['in_progress'] }}
                </span>
            @endif
        </div>

        @if($active_service_orders->isEmpty())
            <div style="padding:32px 20px;text-align:center;color:var(--text-muted);">
                <div style="font-size:13px;font-weight:600;">Tidak ada work order aktif</div>
                <div style="font-size:12px;margin-top:2px;">Semua servis sudah selesai</div>
            </div>
        @else
            <div class="activity-list" style="margin-top:12px;">
                @foreach($active_service_orders as $wo)
                    <div style="padding:10px 20px;border-bottom:1px solid var(--border-light,#f9fafb);display:flex;align-items:center;gap:12px;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;font-size:.875rem;" >{{ $wo->order_no }}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);">
                                {{ $wo->customer?->name }} · {{ $wo->vehicle_plate }}
                            </div>
                            <div style="font-size:.75rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $wo->complaint }}
                            </div>
                        </div>
                        <div style="flex-shrink:0;text-align:right;">
                            <span class="badge {{ $wo->status === 'pending' ? 'badge-warning' : 'badge-info' }}"
                                  style="font-size:11px;">
                                {{ $wo->status === 'pending' ? 'Pending' : 'Dikerjakan' }}
                            </span>
                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:3px;">
                                {{ $wo->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="padding:10px 20px;border-top:1px solid var(--border);">
                <a href="{{ route('service-orders.index') }}"
                   style="font-size:12px;color:var(--info);font-weight:600;text-decoration:none;">
                    Lihat semua work order →
                </a>
            </div>
        @endif
    </div>

    {{-- Stok rendah --}}
    <div class="card">
        <div class="card-header" style="padding-bottom:0;">
            <div>
                <div class="card-title">⚠ Stok Rendah</div>
                <div class="card-subtitle">Produk perlu restock segera</div>
            </div>
            @if($low_stock_count > 0)
                <span class="badge badge-warning">{{ $low_stock_count }}</span>
            @endif
        </div>

        @if($low_stock_products->isEmpty())
            <div style="padding:32px 20px;text-align:center;color:var(--text-muted);">
                <div style="font-size:13px;font-weight:600;">Semua stok aman ✓</div>
            </div>
        @else
            <div class="activity-list" style="margin-top:12px;">
                @foreach($low_stock_products as $product)
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
            @if($low_stock_count > $low_stock_products->count())
                <div style="padding:10px 20px;border-top:1px solid var(--border);">
                    <a href="{{ route('products.index', ['filter' => 'low_stock']) }}"
                       style="font-size:12px;color:var(--info);font-weight:600;text-decoration:none;">
                        Lihat {{ $low_stock_count - $low_stock_products->count() }} produk lainnya →
                    </a>
                </div>
            @endif
        @endif
    </div>

</div>{{-- content-grid --}}


{{-- ── Row 4: Top produk hari ini ──────────────────────────────────────── --}}
@if($daily_top_products->isNotEmpty())
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">🏆 Produk Terlaris Hari Ini</div>
            <div class="card-subtitle">Berdasarkan jumlah terjual</div>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th style="text-align:right;">Qty Terjual</th>
                    <th style="text-align:right;">Omzet</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daily_top_products as $i => $product)
                    <tr>
                        <td style="font-weight:700;color:{{ $i === 0 ? '#f59e0b' : ($i === 1 ? '#9ca3af' : ($i === 2 ? '#b45309' : 'var(--text-muted)')) }};">
                            {{ $i + 1 }}
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:.875rem;">{{ $product->name }}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);font-family:var(--font-mono);">{{ $product->sku }}</div>
                        </td>
                        <td style="text-align:right;font-family:var(--font-mono);font-weight:700;">
                            {{ number_format($product->total_qty) }}
                        </td>
                        <td style="text-align:right;font-family:var(--font-mono);">
                            Rp {{ number_format($product->total_revenue, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div>{{-- ownerDashboard --}}

@endsection

@push('scripts')
<script>
    function ownerDashboard() {
        return {
            selectedDate: '{{ $selected_date }}',
            loading: false,
            stats: {
                daily_revenue:           {{ $daily_revenue }},
                daily_transaction_count: {{ $daily_transaction_count }},
                daily_service_count:     {{ $daily_service_count }},
            },

            init() {},

            async refreshDailyStats() {
                this.loading = true;
                try {
                    const res  = await fetch(`/owner/dashboard/daily-summary?date=${this.selectedDate}`);
                    const json = await res.json();
                    if (json.success) {
                        this.stats.daily_revenue           = json.data.daily_revenue;
                        this.stats.daily_transaction_count = json.data.daily_transaction_count;
                        this.stats.daily_service_count     = json.data.daily_service_count;
                    }
                } catch (e) {
                    console.error('Gagal refresh stats:', e);
                } finally {
                    this.loading = false;
                }
            },
        };
    }

    function revenueChart() {
        // ← chart disimpan di sini, di luar Alpine reactive scope
        let _chart = null;

        return {
            loading:      false,
            selectedDays: {{ $chart_days }},
            chartData:    @json($revenue_chart),

            init() {
                this.$nextTick(() => {
                    this.$nextTick(() => this.render());
                });
            },

            render() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;

                if (_chart) {
                    _chart.destroy();
                    _chart = null;
                }

                _chart = new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: this.chartData.labels,
                        datasets: [
                            {
                                label:           'Omzet (Rp)',
                                data:            this.chartData.revenue,
                                backgroundColor: 'rgba(37,99,235,0.8)',
                                borderRadius:    4,
                                yAxisID:         'y',
                            },
                            {
                                label:                'Transaksi',
                                data:                 this.chartData.transactions,
                                backgroundColor:      'rgba(16,185,129,0.8)',
                                borderRadius:         4,
                                type:                 'line',
                                yAxisID:              'y1',
                                tension:              0.3,
                                fill:                 false,
                                borderColor:          'rgba(16,185,129,1)',
                                pointBackgroundColor: 'rgba(16,185,129,1)',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 300 },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { boxWidth: 12, padding: 16, font: { size: 12 } },
                            },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        if (ctx.datasetIndex === 0) {
                                            return 'Omzet: Rp ' + Number(ctx.raw).toLocaleString('id-ID');
                                        }
                                        return 'Transaksi: ' + ctx.raw;
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                position: 'left',
                                ticks: {
                                    callback: val => 'Rp ' + Number(val).toLocaleString('id-ID'),
                                    font: { size: 11 },
                                },
                            },
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: { precision: 0, font: { size: 11 } },
                            },
                        },
                    },
                });
            },

            async changeDays(days) {
                if (this.selectedDays === days || !_chart) return;
                this.loading      = true;
                this.selectedDays = days;

                try {
                    const res  = await fetch(`/owner/dashboard/chart?days=${days}`);
                    const json = await res.json();
                    this.chartData = json.revenue;

                    _chart.data.labels           = [...this.chartData.labels];
                    _chart.data.datasets[0].data = [...this.chartData.revenue];
                    _chart.data.datasets[1].data = [...this.chartData.transactions];
                    _chart.update();
                } catch (e) {
                    console.error('Gagal load chart:', e);
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endpush
