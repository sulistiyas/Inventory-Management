@extends('layouts.app')

@section('title', 'Riwayat Stok — ' . $product->name)
@section('breadcrumb', 'Riwayat Stok')

@section('content')

<div class="page-header">
    <div>
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
            <a href="{{ route('stock.index') }}" style="color:var(--text-muted); text-decoration:none; font-size:13px; display:flex; align-items:center; gap:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                Manajemen Stok
            </a>
            <span style="color:var(--border-dark);">/</span>
            <span style="font-size:13px; color:var(--text-secondary);">Riwayat Produk</span>
        </div>
        <h1 class="page-title">{{ $product->name }}</h1>
        <p class="page-subtitle">
            <span class="text-mono">{{ $product->sku }}</span>
            · {{ $product->category?->name }}
        </p>
    </div>
    <div style="text-align:right;">
        <div style="font-size:12px; color:var(--text-muted); margin-bottom:4px; text-transform:uppercase; letter-spacing:.04em; font-weight:600;">Stok Saat Ini</div>
        <div class="text-mono" style="font-size:32px; font-weight:700; line-height:1;
             color:{{ $product->isLowStock ? 'var(--warning)' : 'var(--success)' }};">
            {{ number_format($product->stock) }}
        </div>
        <div style="font-size:12px; color:var(--text-muted);">unit · min {{ $product->min_stock }}</div>
    </div>
</div>

{{-- Timeline table --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Riwayat Pergerakan Stok</div>
            <div class="card-subtitle">{{ $movements->total() }} total transaksi</div>
        </div>
        <a href="{{ route('products.show', $product) }}" class="btn btn-secondary btn-sm">Lihat Produk →</a>
    </div>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipe</th>
                    <th style="text-align:right;">Qty</th>
                    <th style="text-align:right;">Sebelum</th>
                    <th style="text-align:right;">Sesudah</th>
                    <th>Catatan</th>
                    <th>Operator</th>
                    <th>Waktu</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td class="text-mono text-muted text-sm">{{ $m->id }}</td>
                        <td>
                            <span class="badge {{ $m->typeBadgeClassAttribute }}">
                                <span style="width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0;"></span>
                                {{ $m->typeLabelAttribute }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <span class="text-mono font-semibold"
                                  style="color:{{ $m->type === 'in' ? 'var(--success)' : 'var(--danger)' }}">
                                {{ $m->type === 'in' ? '+' : '-' }}{{ number_format($m->quantity) }}
                            </span>
                        </td>
                        <td style="text-align:right;" class="text-mono text-muted text-sm">{{ number_format($m->stock_before) }}</td>
                        <td style="text-align:right;" class="text-mono font-semibold">{{ number_format($m->stock_after) }}</td>
                        <td style="color:var(--text-secondary); font-size:13px; max-width:200px;" class="truncate">
                            {{ $m->notes ?: '—' }}
                        </td>
                        <td style="font-size:13px;">{{ $m->user->name }}</td>
                        <td>
                            <div style="font-size:12px; color:var(--text-muted);" title="{{ $m->created_at->format('d M Y H:i:s') }}">
                                {{ $m->created_at->format('d M Y') }}
                            </div>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $m->created_at->format('H:i') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('stock.show', $m) }}" class="btn btn-secondary btn-sm" style="padding:3px 10px;">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:48px; color:var(--text-muted);">
                            Belum ada pergerakan stok untuk produk ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($movements->hasPages())
        <div style="padding:16px 20px; border-top:1px solid var(--border);">
            {{ $movements->links() }}
        </div>
    @endif
</div>

@endsection