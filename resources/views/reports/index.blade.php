@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Laporan</h1>
    <p class="page-subtitle">Generate laporan stok &amp; mutasi dalam format PDF</p>
  </div>
</div>

<div class="reports-grid">

  {{-- ── Laporan Stok Barang ──────────────────────────────────────────── --}}
  <div class="report-card" x-data="reportStock()">
    <div class="report-card-header">
      <div class="report-card-icon amber">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
        </svg>
      </div>
      <div>
        <div class="report-card-title">Laporan Stok Barang</div>
        <div class="report-card-desc">Ringkasan stok semua produk beserta nilai inventori</div>
      </div>
    </div>

    <div class="report-form">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Kategori</label>
          <select x-model="form.category_id" class="form-select">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Supplier</label>
          <select x-model="form.supplier_id" class="form-select">
            <option value="">Semua Supplier</option>
            @foreach($suppliers as $sup)
              <option value="{{ $sup->id }}">{{ $sup->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Status Stok</label>
          <select x-model="form.status" class="form-select">
            <option value="">Semua Status</option>
            <option value="low">Stok Menipis</option>
            <option value="out">Habis</option>
          </select>
        </div>
      </div>
    </div>

    <div class="report-card-footer">
      <button @click="generate()" :disabled="loading" class="btn btn-primary">
        <template x-if="!loading">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
          </svg>
        </template>
        <template x-if="loading">
          <svg class="spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12"/>
          </svg>
        </template>
        <span x-text="loading ? 'Generating...' : 'Download PDF'"></span>
      </button>
      <span class="report-hint">Format: A4 Landscape</span>
    </div>
  </div>

  {{-- ── Laporan Mutasi Stok ──────────────────────────────────────────── --}}
  <div class="report-card" x-data="reportMovement()">
    <div class="report-card-header">
      <div class="report-card-icon blue">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
        </svg>
      </div>
      <div>
        <div class="report-card-title">Laporan Mutasi Stok</div>
        <div class="report-card-desc">Riwayat pergerakan stok masuk dan keluar berdasarkan periode</div>
      </div>
    </div>

    <div class="report-form">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Tanggal Mulai</label>
          <input type="date" x-model="form.date_from" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal Akhir</label>
          <input type="date" x-model="form.date_to" class="form-input" />
        </div>
        <div class="form-group">
          <label class="form-label">Tipe Mutasi</label>
          <select x-model="form.type" class="form-select">
            <option value="">Semua Tipe</option>
            <option value="in">Masuk</option>
            <option value="out">Keluar</option>
          </select>
        </div>
      </div>
      <div x-show="dateError" class="form-error">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
          <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
        Tanggal akhir harus setelah tanggal mulai
      </div>
    </div>

    <div class="report-card-footer">
      <button @click="generate()" :disabled="loading || dateError" class="btn btn-primary">
        <template x-if="!loading">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
          </svg>
        </template>
        <template x-if="loading">
          <svg class="spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="12"/>
          </svg>
        </template>
        <span x-text="loading ? 'Generating...' : 'Download PDF'"></span>
      </button>
      <span class="report-hint">Format: A4 Landscape</span>
    </div>
  </div>

</div>
@endsection

@push('styles')
<style>
  .reports-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }

  @media (max-width: 900px) {
    .reports-grid { grid-template-columns: 1fr; }
  }

  .report-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    gap: 0;
    overflow: hidden;
    transition: box-shadow var(--transition);
  }
  .report-card:hover { box-shadow: var(--shadow-md); }

  .report-card-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 20px 20px 16px;
    border-bottom: 1px solid var(--border);
  }

  .report-card-icon {
    width: 44px; height: 44px;
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .report-card-icon svg { width: 22px; height: 22px; }
  .report-card-icon.amber { background: var(--warning-bg); color: var(--warning); }
  .report-card-icon.blue  { background: var(--info-bg);    color: var(--info); }

  .report-card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.01em;
  }
  .report-card-desc {
    font-size: 12.5px;
    color: var(--text-muted);
    margin-top: 3px;
    line-height: 1.5;
  }

  .report-form {
    padding: 18px 20px;
    flex: 1;
  }

  .form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
  }

  .form-group { display: flex; flex-direction: column; gap: 5px; }

  .form-label {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .form-select,
  .form-input {
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-family: var(--font-body);
    color: var(--text-primary);
    background: var(--bg-body);
    transition: border-color var(--transition), box-shadow var(--transition);
    width: 100%;
  }
  .form-select:focus,
  .form-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-glow);
    background: var(--bg-card);
  }

  .form-error {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--danger);
    margin-top: 8px;
  }

  .report-card-footer {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: var(--bg-body);
    border-top: 1px solid var(--border);
  }

  .report-hint {
    font-size: 11.5px;
    color: var(--text-muted);
  }

  .spin {
    animation: spin 0.8s linear infinite;
    width: 15px; height: 15px;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@push('scripts')
<script>
  function reportStock() {
    return {
      loading: false,
      form: { category_id: '', supplier_id: '', status: '' },
      generate() {
        this.loading = true;
        const params = new URLSearchParams();
        if (this.form.category_id) params.set('category_id', this.form.category_id);
        if (this.form.supplier_id) params.set('supplier_id', this.form.supplier_id);
        if (this.form.status)      params.set('status', this.form.status);
        const url = "{{ route('reports.stock-summary') }}" + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
        setTimeout(() => this.loading = false, 3000);
      }
    }
  }

  function reportMovement() {
    return {
      loading: false,
      form: { date_from: '', date_to: '', type: '' },
      get dateError() {
        return this.form.date_from && this.form.date_to && this.form.date_to < this.form.date_from;
      },
      generate() {
        if (this.dateError) return;
        this.loading = true;
        const params = new URLSearchParams();
        if (this.form.date_from) params.set('date_from', this.form.date_from);
        if (this.form.date_to)   params.set('date_to', this.form.date_to);
        if (this.form.type)      params.set('type', this.form.type);
        const url = "{{ route('reports.stock-movement') }}" + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
        setTimeout(() => this.loading = false, 3000);
      }
    }
  }
</script>
@endpush