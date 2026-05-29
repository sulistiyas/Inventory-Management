@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')

{{-- ── Header + Filter ─────────────────────────────────────────────────── --}}
<div class="page-header" style="flex-wrap:wrap;gap:.75rem;">
    <div>
        <h1 class="page-title">Laporan Penjualan</h1>
        <p class="page-subtitle">
            {{ \Carbon\Carbon::parse($date_from)->translatedFormat('d F Y') }}
            — {{ \Carbon\Carbon::parse($date_to)->translatedFormat('d F Y') }}
        </p>
    </div>
    <a href="{{ route('owner.dashboard') }}" class="btn btn-secondary btn-sm">← Dashboard</a>
</div>

{{-- Filter form --}}
<div class="card" style="margin-bottom:1.25rem;">
    <form method="GET" action="{{ route('owner.sales-report') }}"
          style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;padding:1rem 1.25rem;">
        <div>
            <label style="font-size:.75rem;color:var(--text-muted);display:block;margin-bottom:3px;">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ $date_from }}"
                   style="height:36px;padding:0 10px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
        </div>
        <div>
            <label style="font-size:.75rem;color:var(--text-muted);display:block;margin-bottom:3px;">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ $date_to }}" max="{{ today()->toDateString() }}"
                   style="height:36px;padding:0 10px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;">
        </div>
        <button type="submit" class="btn btn-primary" style="height:36px;">Tampilkan</button>

        {{-- Shortcut periode --}}
        <div style="display:flex;gap:.5rem;">
            @php
                $shortcuts = [
                    'Hari ini'   => [today()->toDateString(), today()->toDateString()],
                    'Minggu ini' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
                    'Bulan ini'  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                ];
            @endphp
            @foreach($shortcuts as $label => [$from, $to])
                <a href="{{ route('owner.sales-report', ['date_from' => $from, 'date_to' => $to]) }}"
                   style="height:36px;padding:0 12px;border:1px solid var(--border);border-radius:6px;font-size:.8125rem;display:inline-flex;align-items:center;text-decoration:none;color:var(--text);background:var(--bg);
                   {{ $date_from === $from && $date_to === $to ? 'background:var(--primary);color:#fff;border-color:var(--primary);' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </form>
</div>


{{-- ── Summary cards ───────────────────────────────────────────────────── --}}
<div class="stats-grid" style="margin-bottom:1.25rem;">

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Total Omzet</span>
            <span class="stat-card-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </span>
        </div>
        <div class="stat-card-value">Rp {{ number_format($total_revenue, 0, ',', '.') }}</div>
        <div class="stat-card-footer">
            <span class="text-muted text-sm">Diskon: Rp {{ number_format($total_discount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Jumlah Transaksi</span>
            <span class="stat-card-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
            </span>
        </div>
        <div class="stat-card-value">{{ number_format($total_count) }}</div>
        <div class="stat-card-footer">
            @if($total_count > 0)
                <span class="text-muted text-sm">
                    Rata-rata: Rp {{ number_format($total_revenue / $total_count, 0, ',', '.') }}/transaksi
                </span>
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-label">Total Pajak</span>
            <span class="stat-card-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </span>
        </div>
        <div class="stat-card-value">Rp {{ number_format($total_tax, 0, ',', '.') }}</div>
        <div class="stat-card-footer">
            <span class="text-muted text-sm">Pajak dikumpulkan</span>
        </div>
    </div>

</div>


{{-- ── Row: Top produk + Metode bayar ─────────────────────────────────── --}}
<div class="content-grid" style="margin-bottom:1.25rem;">

    {{-- Top produk --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">🏆 Produk Terlaris</div>
        </div>
        @if($top_products->isEmpty())
            <div style="padding:32px 20px;text-align:center;color:var(--text-muted);font-size:.875rem;">Tidak ada data</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th><th>Produk</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_products as $i => $p)
                        <tr>
                            <td style="font-weight:700;">{{ $i + 1 }}</td>
                            <td>
                                <div style="font-weight:600;font-size:.875rem;">{{ $p->name }}</div>
                                <div style="font-size:.75rem;color:var(--text-muted);font-family:var(--font-mono);">{{ $p->sku }}</div>
                            </td>
                            <td style="text-align:right;font-family:var(--font-mono);font-weight:700;">{{ number_format($p->total_qty) }}</td>
                            <td style="text-align:right;font-family:var(--font-mono);">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Metode pembayaran --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">💳 Per Metode Bayar</div>
        </div>
        @if($by_payment_method->isEmpty())
            <div style="padding:32px 20px;text-align:center;color:var(--text-muted);font-size:.875rem;">Tidak ada data</div>
        @else
            <div style="padding:1rem 1.25rem;">
                @foreach($by_payment_method as $method)
                    @php
                        $pct = $total_revenue > 0 ? round(($method->total / $total_revenue) * 100) : 0;
                    @endphp
                    <div style="margin-bottom:1rem;">
                        <div style="display:flex;justify-content:space-between;font-size:.875rem;margin-bottom:4px;">
                            <span style="font-weight:600;">{{ $method->name }}</span>
                            <span style="font-family:var(--font-mono);">Rp {{ number_format($method->total, 0, ',', '.') }}</span>
                        </div>
                        <div style="background:var(--border);border-radius:4px;height:6px;">
                            <div style="background:var(--primary,#2563eb);height:6px;border-radius:4px;width:{{ $pct }}%;"></div>
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px;">{{ $pct }}% dari total omzet</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>


{{-- ── Tabel transaksi ─────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Transaksi</div>
            <div class="card-subtitle">{{ $total_count }} transaksi ditemukan</div>
        </div>
        <button onclick="window.print()" class="btn btn-secondary btn-sm">🖨 Print</button>
    </div>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice</th>
                    <th>Pelanggan</th>
                    <th>Kasir</th>
                    <th style="text-align:right;">Subtotal</th>
                    <th style="text-align:right;">Diskon</th>
                    <th style="text-align:right;">Total</th>
                    <th>Metode Bayar</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $i => $sale)
                    <tr>
                        <td class="text-muted text-sm">{{ $i + 1 }}</td>
                        <td style="font-family:var(--font-mono);font-size:.8125rem;font-weight:600;">
                            {{ $sale->invoice_no }}
                        </td>
                        <td style="font-size:.8125rem;">{{ $sale->customer?->name ?? 'Umum' }}</td>
                        <td style="font-size:.8125rem;">{{ $sale->cashier->name }}</td>
                        <td style="text-align:right;font-family:var(--font-mono);font-size:.8125rem;">
                            Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                        </td>
                        <td style="text-align:right;font-family:var(--font-mono);font-size:.8125rem;color:var(--success);">
                            @if($sale->discount > 0)
                                - Rp {{ number_format($sale->discount, 0, ',', '.') }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="text-align:right;font-family:var(--font-mono);font-weight:700;">
                            Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                        </td>
                        <td style="font-size:.8125rem;">
                            {{ $sale->payments->map(fn($p) => $p->paymentMethod?->name)->filter()->join(', ') ?: '—' }}
                        </td>
                        <td style="font-size:.75rem;color:var(--text-muted);white-space:nowrap;">
                            {{ optional($sale->sold_at)->format('d M Y, H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">
                            Tidak ada transaksi pada periode ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($total_count > 0)
                <tfoot>
                    <tr style="font-weight:700;background:var(--bg-subtle);">
                        <td colspan="6" style="text-align:right;padding:10px 12px;">TOTAL</td>
                        <td style="text-align:right;font-family:var(--font-mono);padding:10px 12px;">
                            Rp {{ number_format($total_revenue, 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
@media print {
    .sidebar, .navbar, .page-header .btn, form, button { display: none !important; }
    .card { box-shadow: none; border: 1px solid #ddd; }
}
</style>
@endpush
