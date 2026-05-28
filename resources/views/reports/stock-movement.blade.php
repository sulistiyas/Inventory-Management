<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Laporan Mutasi Stok</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 9pt;
      color: #0F172A;
      background: #fff;
      padding: 20px 24px;
    }

    /* ── Header ── */
    .doc-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 18px;
      padding-bottom: 14px;
      border-bottom: 2px solid #3B82F6;
    }

    .brand-block { display: flex; align-items: center; gap: 10px; }

    .brand-icon {
      width: 36px; height: 36px;
      background: #F59E0B;
      border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
    }

    .brand-name {
      font-size: 16pt;
      font-weight: 700;
      color: #0F172A;
      letter-spacing: -0.02em;
      line-height: 1;
    }
    .brand-sub {
      font-size: 7pt;
      color: #94A3B8;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-top: 2px;
    }

    .doc-meta { text-align: right; }
    .doc-title {
      font-size: 13pt;
      font-weight: 700;
      color: #0F172A;
      letter-spacing: -0.01em;
    }
    .doc-subtitle {
      font-size: 8pt;
      color: #64748B;
      margin-top: 2px;
    }
    .doc-date {
      font-size: 7.5pt;
      color: #94A3B8;
      margin-top: 4px;
    }

    /* ── Filter info ── */
    .filter-bar {
      background: #F8FAFC;
      border: 1px solid #E2E8F0;
      border-radius: 6px;
      padding: 8px 14px;
      margin-bottom: 14px;
      display: flex;
      gap: 20px;
      align-items: center;
    }
    .filter-item {
      font-size: 7.5pt;
      color: #64748B;
    }
    .filter-item span {
      font-weight: 600;
      color: #0F172A;
    }

    /* ── Summary stats ── */
    .stats-row {
      display: flex;
      gap: 12px;
      margin-bottom: 16px;
    }

    .stat-box {
      flex: 1;
      border: 1px solid #E2E8F0;
      border-radius: 8px;
      padding: 10px 14px;
      background: #FFFFFF;
    }
    .stat-box.blue   { border-left: 3px solid #3B82F6; }
    .stat-box.green  { border-left: 3px solid #10B981; }
    .stat-box.red    { border-left: 3px solid #EF4444; }
    .stat-box.slate  { border-left: 3px solid #64748B; }

    .stat-label {
      font-size: 7pt;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #94A3B8;
      margin-bottom: 4px;
    }
    .stat-value {
      font-size: 14pt;
      font-weight: 700;
      color: #0F172A;
      line-height: 1;
      font-family: 'DejaVu Sans Mono', monospace;
    }
    .stat-value.green { color: #10B981; }
    .stat-value.red   { color: #EF4444; }

    /* ── Table ── */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8pt;
    }

    thead tr {
      background: #0F172A;
      color: #fff;
    }

    thead th {
      padding: 8px 10px;
      text-align: left;
      font-size: 7.5pt;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      white-space: nowrap;
    }

    thead th.text-right  { text-align: right; }
    thead th.text-center { text-align: center; }

    tbody tr:nth-child(even) { background: #F8FAFC; }
    tbody tr:nth-child(odd)  { background: #FFFFFF; }

    tbody td {
      padding: 7px 10px;
      border-bottom: 1px solid #F1F5F9;
      vertical-align: middle;
      color: #0F172A;
    }
    tbody td.text-right  { text-align: right; font-family: 'DejaVu Sans Mono', monospace; }
    tbody td.text-center { text-align: center; }
    tbody td.muted { color: #94A3B8; font-size: 7.5pt; }

    /* type badge */
    .badge {
      display: inline-block;
      padding: 2px 7px;
      border-radius: 12px;
      font-size: 7pt;
      font-weight: 700;
    }
    .badge-in  { background: #D1FAE5; color: #065F46; }
    .badge-out { background: #FEE2E2; color: #991B1B; }

    /* quantity cell */
    .qty-in  { color: #10B981; font-weight: 700; }
    .qty-out { color: #EF4444; font-weight: 700; }

    /* tfoot */
    tfoot tr { background: #F1F5F9; }
    tfoot td {
      padding: 8px 10px;
      font-size: 8pt;
      font-weight: 700;
      color: #0F172A;
      border-top: 2px solid #E2E8F0;
    }
    tfoot td.text-right {
      text-align: right;
      font-family: 'DejaVu Sans Mono', monospace;
    }

    /* ── Footer ── */
    .doc-footer {
      margin-top: 16px;
      padding-top: 10px;
      border-top: 1px solid #E2E8F0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .doc-footer-left  { font-size: 7pt; color: #94A3B8; }
    .doc-footer-right { font-size: 7pt; color: #94A3B8; text-align: right; }
  </style>
</head>
<body>

  {{-- ── Header ── --}}
  <div class="doc-header">
    <div class="brand-block">
      <div class="brand-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M3 10.5L12 4L21 10.5V20H3V10.5Z" stroke="white" stroke-width="1.8" fill="none"/>
          <path d="M9 20V14H15V20" stroke="white" stroke-width="1.8"/>
        </svg>
      </div>
      <div>
        <div class="brand-name">WarehouSe</div>
        <div class="brand-sub">Sistem Manajemen Gudang</div>
      </div>
    </div>
    <div class="doc-meta">
      <div class="doc-title">Laporan Mutasi Stok</div>
      <div class="doc-subtitle">Stock Movement Report</div>
      <div class="doc-date">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>
  </div>

  {{-- ── Filter info ── --}}
  <div class="filter-bar">
    <div class="filter-item">
      Periode:
      <span>
        @if($request->date_from || $request->date_to)
          {{ $request->date_from ? \Carbon\Carbon::parse($request->date_from)->format('d/m/Y') : '...' }}
          &mdash;
          {{ $request->date_to ? \Carbon\Carbon::parse($request->date_to)->format('d/m/Y') : '...' }}
        @else
          Semua Waktu
        @endif
      </span>
    </div>
    <div class="filter-item">
      Tipe:
      <span>
        @switch($request->type)
          @case('in')  Masuk @break
          @case('out') Keluar @break
          @default     Semua @break
        @endswitch
      </span>
    </div>
    <div class="filter-item">
      Total Transaksi: <span>{{ $movements->count() }}</span>
    </div>
  </div>

  {{-- ── Stats ── --}}
  <div class="stats-row">
    <div class="stat-box blue">
      <div class="stat-label">Total Transaksi</div>
      <div class="stat-value">{{ $movements->count() }}</div>
    </div>
    <div class="stat-box green">
      <div class="stat-label">Total Masuk</div>
      <div class="stat-value green">+{{ number_format($totalIn) }}</div>
    </div>
    <div class="stat-box red">
      <div class="stat-label">Total Keluar</div>
      <div class="stat-value red">-{{ number_format($totalOut) }}</div>
    </div>
    <div class="stat-box slate">
      <div class="stat-label">Selisih Bersih</div>
      <div class="stat-value" style="{{ $totalIn - $totalOut >= 0 ? 'color:#10B981' : 'color:#EF4444' }}">
        {{ $totalIn - $totalOut >= 0 ? '+' : '' }}{{ number_format($totalIn - $totalOut) }}
      </div>
    </div>
  </div>

  {{-- ── Table ── --}}
  <table>
    <thead>
      <tr>
        <th style="width:28px">#</th>
        <th>Tanggal &amp; Waktu</th>
        <th>Produk</th>
        <th>SKU</th>
        <th class="text-center">Tipe</th>
        <th class="text-right">Jumlah</th>
        <th class="text-right">Stok Sebelum</th>
        <th class="text-right">Stok Sesudah</th>
        <th>Keterangan</th>
        <th>User</th>
      </tr>
    </thead>
    <tbody>
      @forelse($movements as $i => $mov)
        <tr>
          <td class="muted">{{ $i + 1 }}</td>
          <td style="white-space:nowrap;font-family:'DejaVu Sans Mono',monospace;font-size:7.5pt">
            {{ $mov->created_at->format('d/m/Y') }}<br>
            <span style="color:#94A3B8">{{ $mov->created_at->format('H:i') }}</span>
          </td>
          <td style="font-weight:600">{{ $mov->product?->name ?? '-' }}</td>
          <td class="muted" style="font-family:'DejaVu Sans Mono',monospace">
            {{ $mov->product?->sku ?? '-' }}
          </td>
          <td class="text-center">
            @if($mov->type === 'in')
              <span class="badge badge-in">Masuk</span>
            @else
              <span class="badge badge-out">Keluar</span>
            @endif
          </td>
          <td class="text-right {{ $mov->type === 'in' ? 'qty-in' : 'qty-out' }}">
            {{ $mov->type === 'in' ? '+' : '-' }}{{ number_format($mov->quantity) }}
          </td>
          <td class="text-right muted">{{ number_format($mov->stock_before) }}</td>
          <td class="text-right" style="font-weight:600">{{ number_format($mov->stock_after) }}</td>
          <td style="max-width:140px;color:#475569">
            {{ $mov->notes ?: '-' }}
          </td>
          <td style="color:#475569">{{ $mov->user?->name ?? '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" style="text-align:center;padding:20px;color:#94A3B8;">
            Tidak ada data mutasi sesuai filter.
          </td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5" style="font-size:8pt;color:#64748B">
          Total {{ $movements->count() }} transaksi
        </td>
        <td class="text-right">
          <span style="color:#10B981">+{{ number_format($totalIn) }}</span>
          /
          <span style="color:#EF4444">-{{ number_format($totalOut) }}</span>
        </td>
        <td colspan="4"></td>
      </tr>
    </tfoot>
  </table>

  {{-- ── Footer ── --}}
  <div class="doc-footer">
    <div class="doc-footer-left">
      WarehouSe &mdash; Sistem Manajemen Gudang &bull; Dokumen ini digenerate secara otomatis oleh sistem
    </div>
    <div class="doc-footer-right">
      Laporan Mutasi Stok &bull; {{ now()->format('d/m/Y H:i') }}
    </div>
  </div>

</body>
</html>