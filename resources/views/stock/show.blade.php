@extends('layouts.app')

@section('title', 'Detail Transaksi #' . $movement->id)
@section('breadcrumb', 'Detail Transaksi')

@section('content')

<div class="page-header">
    <div>
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
            <a href="{{ route('stock.index') }}" style="color:var(--text-muted); text-decoration:none; font-size:13px; display:flex; align-items:center; gap:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                Manajemen Stok
            </a>
            <span style="color:var(--border-dark);">/</span>
            <span style="font-size:13px; color:var(--text-secondary);">Transaksi #{{ $movement->id }}</span>
        </div>
        <h1 class="page-title">Detail Transaksi #{{ $movement->id }}</h1>
        <p class="page-subtitle">{{ $movement->created_at->format('l, d F Y — H:i:s') }}</p>
    </div>
    <span class="badge {{ $movement->typeBadgeClassAttribute }}" style="font-size:14px; padding:6px 16px;">
        <span style="width:8px; height:8px; border-radius:50%; background:currentColor; flex-shrink:0;"></span>
        {{ $movement->typeLabelAttribute }}
    </span>
</div>

<div style="display:grid; grid-template-columns: 1fr 340px; gap:16px;">

    {{-- Main detail card --}}
    <div class="card">
        <div class="card-header" style="padding-bottom:0;">
            <div class="card-title">Informasi Transaksi</div>
        </div>
        <div class="card-body">
            <table style="width:100%; border-collapse:collapse;">
                @php
                $rows = [
                    ['ID Transaksi',  '#' . $movement->id,                   'mono'],
                    ['Tipe',          null,                                    'badge'],
                    ['Produk',        $movement->product->name,                ''],
                    ['SKU',           $movement->product->sku,                 'mono'],
                    ['Kategori',      $movement->product->category?->name,     ''],
                    ['Supplier',      $movement->product->supplier?->name,     ''],
                    ['Jumlah',        number_format($movement->quantity) . ' unit', 'mono bold'],
                    ['Stok Sebelum',  number_format($movement->stock_before) . ' unit', 'mono'],
                    ['Stok Sesudah',  number_format($movement->stock_after)  . ' unit', 'mono'],
                    ['Catatan',       $movement->notes ?: '—',                ''],
                    ['Operator',      $movement->user->name,                   ''],
                    ['Email',         $movement->user->email ?? '—',           ''],
                    ['Waktu Dibuat',  $movement->created_at->format('d M Y, H:i:s'), ''],
                ];
                @endphp

                @foreach($rows as [$label, $value, $style])
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:12px 16px 12px 0; width:160px; font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.04em; vertical-align:top;">
                            {{ $label }}
                        </td>
                        <td style="padding:12px 0; font-size:14px; color:var(--text-primary);">
                            @if($label === 'Tipe')
                                <span class="badge {{ $movement->typeBadgeClassAttribute }}">{{ $movement->typeLabelAttribute }}</span>
                            @elseif(str_contains($style, 'mono'))
                                <span class="text-mono {{ str_contains($style, 'bold') ? 'font-semibold' : '' }}" style="font-size:14px;">{{ $value }}</span>
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    {{-- Side panel: stock delta visual --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Stock change visual --}}
        <div class="card">
            <div class="card-body" style="text-align:center; padding:28px 20px;">
                <div style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-bottom:16px;">
                    Perubahan Stok
                </div>
                <div style="display:flex; align-items:center; justify-content:center; gap:16px;">
                    <div style="text-align:center;">
                        <div style="font-size:11px; color:var(--text-muted); margin-bottom:4px;">Sebelum</div>
                        <div class="text-mono" style="font-size:28px; font-weight:700; color:var(--text-primary);">
                            {{ number_format($movement->stock_before) }}
                        </div>
                    </div>
                    <div style="font-size:24px; font-weight:700; color:{{ $movement->type === 'in' ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $movement->type === 'in' ? '→' : '→' }}
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:11px; color:var(--text-muted); margin-bottom:4px;">Sesudah</div>
                        <div class="text-mono" style="font-size:28px; font-weight:700; color:{{ $movement->type === 'in' ? 'var(--success)' : 'var(--danger)' }};">
                            {{ number_format($movement->stock_after) }}
                        </div>
                    </div>
                </div>
                <div style="margin-top:16px; padding:10px; border-radius:var(--radius-sm); background:{{ $movement->type === 'in' ? 'var(--success-bg)' : 'var(--danger-bg)' }};">
                    <span class="text-mono" style="font-size:20px; font-weight:700; color:{{ $movement->type === 'in' ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $movement->type === 'in' ? '+' : '-' }}{{ number_format($movement->quantity) }}
                    </span>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">unit {{ $movement->typeLabelAttribute }}</div>
                </div>
            </div>
        </div>

        {{-- Product quick info --}}
        <div class="card">
            <div class="card-header" style="padding-bottom:0;">
                <div class="card-title">Produk</div>
                <a href="{{ route('products.show', $movement->product) }}" style="font-size:12px; color:var(--info); text-decoration:none; font-weight:600;">Lihat →</a>
            </div>
            <div class="card-body" style="padding-top:12px;">
                <div style="font-size:15px; font-weight:700; color:var(--text-primary); margin-bottom:4px;">
                    {{ $movement->product->name }}
                </div>
                <div class="text-mono text-muted text-sm" style="margin-bottom:12px;">{{ $movement->product->sku }}</div>

                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:var(--text-muted);">Stok saat ini</span>
                        <span class="text-mono font-semibold"
                              style="color:{{ $movement->product->isLowStock ? 'var(--warning)' : 'var(--success)' }}">
                            {{ number_format($movement->product->stock) }} unit
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:var(--text-muted);">Minimum stok</span>
                        <span class="text-mono">{{ number_format($movement->product->min_stock) }} unit</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px;">
                        <span style="color:var(--text-muted);">Status</span>
                        <span class="badge {{ $movement->product->stockStatusClassAttribute }}">
                            {{ $movement->product->stockStatusAttribute }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex; flex-direction:column; gap:8px;">
            <a href="{{ route('stock.product.history', $movement->product) }}" class="btn btn-secondary" style="justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Riwayat Produk Ini
            </a>
            <a href="{{ route('stock.index') }}" class="btn btn-secondary" style="justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>
</div>

@endsection