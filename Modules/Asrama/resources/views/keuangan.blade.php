@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
@endpush

@section('topbar')
<a href="{{ route('asrama.data') }}" class="btn btn-secondary">Data Asrama</a>
<a href="{{ route('asrama.keuangan') }}" class="btn btn-secondary active">Keuangan</a>
@endsection

@section('content')

<div class="page-header">
    <h2 class="title">Keuangan Asrama</h2>
</div>

<div class="asrama-wrapper">
    {{-- STATS KEUANGAN GRID --}}
    <div class="asrama-stats-grid">
        <div class="asrama-stat-card">
            <p class="task-meta">Total Pemasukan</p>
            <h3 style="color: #6ee7b7; margin: 0.25rem 0; font-size: 1.6rem;">Rp {{ number_format($summary['total_pemasukan'], 0, ',', '.') }}</h3>
            <p class="task-meta" style="font-size: 0.75rem;">Iuran & pemasukan lain</p>
        </div>
        <div class="asrama-stat-card">
            <p class="task-meta">Total Pengeluaran</p>
            <h3 style="color: #f87171; margin: 0.25rem 0; font-size: 1.6rem;">Rp {{ number_format($summary['total_pengeluaran'], 0, ',', '.') }}</h3>
            <p class="task-meta" style="font-size: 0.75rem;">Biaya operasional & perbaikan</p>
        </div>
        <div class="asrama-stat-card">
            <p class="task-meta">Saldo Kas Saat Ini</p>
            <h3 style="color: {{ $summary['saldo_kas'] >= 0 ? '#fde047' : '#f87171' }}; margin: 0.25rem 0; font-size: 1.6rem;">
                Rp {{ number_format($summary['saldo_kas'], 0, ',', '.') }}
            </h3>
            <p class="task-meta" style="font-size: 0.75rem;">Sisa kas bersih asrama</p>
        </div>
    </div>

    {{-- KEUANGAN SECTION --}}
    <div class="widget-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">💰 Riwayat Transaksi Keuangan</h3>
                <p class="task-meta" style="margin: 0.2rem 0 0 0;">Catatan arus kas pemasukan iuran & pengeluaran operasional</p>
            </div>
            <button type="button" onclick="openKeuanganModal()" class="btn btn-primary btn-sm">+ Catat Transaksi</button>
        </div>

        @if($keuangans->isEmpty())
            <p class="empty-state">Belum ada catatan keuangan. Klik <strong>+ Catat Transaksi</strong> untuk menambahkan transaksi baru.</p>
        @else
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Kategori</th>
                            <th>Nominal</th>
                            <th>Penghuni (Jika Iuran)</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keuangans as $k)
                        <tr>
                            <td class="task-title" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($k->tanggal)->format('d M Y') }}</td>
                            <td>
                                @if($k->tipe === 'pemasukan')
                                    <span class="badge badge-success">Pemasukan</span>
                                @else
                                    <span class="badge badge-danger">Pengeluaran</span>
                                @endif
                            </td>
                            <td><span class="badge badge-info">{{ $k->kategori }}</span></td>
                            <td style="font-weight: 700; color: {{ $k->tipe === 'pemasukan' ? '#6ee7b7' : '#f87171' }};">
                                {{ $k->tipe === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($k->nominal, 0, ',', '.') }}
                            </td>
                            <td class="task-meta">
                                {{ $k->penghuni ? $k->penghuni->nama : '-' }}
                            </td>
                            <td class="task-meta">{{ $k->keterangan ?: '-' }}</td>
                            <td>
                                <form action="{{ route('asrama.keuangan.destroy', $k->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus catatan transaksi {{ $k->kategori }} ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH TRANSAKSI KEUANGAN --}}
<div id="modal-keuangan" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Catat Transaksi Keuangan</h3>
        <button onclick="closeKeuanganModal()" class="modal-close">&times;</button>
    </div>
    <form action="{{ route('asrama.keuangan.store') }}" method="POST" autocomplete="off">
        @csrf
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Tipe Transaksi <span class="required">*</span></label>
                <select name="tipe" class="form-control" required>
                    <option value="pemasukan">🟢 Pemasukan (+)</option>
                    <option value="pengeluaran">🔴 Pengeluaran (-)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kategori <span class="required">*</span></label>
                <select name="kategori" class="form-control" required>
                    <option value="Iuran Bulanan">Iuran Bulanan</option>
                    <option value="Listrik & Air">Listrik & Air</option>
                    <option value="Kebersihan & Keamanan">Kebersihan & Keamanan</option>
                    <option value="Perbaikan & Maintenance">Perbaikan & Maintenance</option>
                    <option value="Pembelian Peralatan">Pembelian Peralatan</option>
                    <option value="Lain-lain">Lain-lain</option>
                </select>
            </div>
        </div>

        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Nominal (Rp) <span class="required">*</span></label>
                <input type="number" name="nominal" class="form-control" placeholder="500000" min="1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Transaksi <span class="required">*</span></label>
                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Penghuni Pembayar (Opsional)</label>
            <select name="penghuni_id" class="form-control">
                <option value="">-- Tidak Terikat Penghuni Spesifik --</option>
                @foreach($penghunis as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }} {{ $p->kamar ? '(' . $p->kamar->nomor_kamar . ')' : '' }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Keterangan / Catatan</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="cth: Pembayaran Iuran Bulan Agustus 2026"></textarea>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeKeuanganModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
        </div>
    </form>
</div>
<div id="modal-keuangan-overlay" class="modal-overlay" onclick="closeKeuanganModal()"></div>

@endsection

@push('scripts')
<script>
    function openKeuanganModal() {
        const m = document.getElementById('modal-keuangan');
        const o = document.getElementById('modal-keuangan-overlay');
        if (m) { m.classList.add('show'); m.style.display = 'block'; }
        if (o) { o.classList.add('show'); o.style.display = 'block'; }
    }

    function closeKeuanganModal() {
        const m = document.getElementById('modal-keuangan');
        const o = document.getElementById('modal-keuangan-overlay');
        if (m) { m.classList.remove('show'); m.style.display = 'none'; }
        if (o) { o.classList.remove('show'); o.style.display = 'none'; }
    }
</script>
@endpush