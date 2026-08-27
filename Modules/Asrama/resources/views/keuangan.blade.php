@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
@endpush

@section('topbar')
<a href="{{ route('asrama.data') }}" class="topbar-menu-btn btn btn-secondary {{ request()->routeIs('asrama.data') ? 'active' : '' }}" data-menu="data_asrama">Data Asrama</a>
<a href="{{ route('asrama.keuangan') }}" class="topbar-menu-btn btn btn-secondary {{ request()->routeIs('asrama.keuangan*') ? 'active' : '' }}" data-menu="keuangan">Keuangan</a>
@endsection

@section('content')

@php
$wifiSettings = \Modules\Asrama\Http\Controllers\AsramaController::getWifiSettings();
@endphp



<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div class="sub-nav-tabs" id="asrama-sub-nav" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('asrama.data') }}" class="sub-nav-btn btn btn-sm {{ request()->routeIs('asrama.data') ? 'btn-primary' : 'btn-secondary' }}" data-nav="data">
            Data Penghuni & Kamar
        </a>
        <a href="{{ route('asrama.keuangan') }}" class="sub-nav-btn btn btn-sm {{ request()->routeIs('asrama.keuangan') ? 'btn-primary' : 'btn-secondary' }}" data-nav="keuangan">
            Riwayat Transaksi Kas
        </a>
        <a href="{{ route('asrama.keuangan.matriks') }}" class="sub-nav-btn btn btn-sm {{ request()->routeIs('asrama.keuangan.matriks') ? 'btn-primary' : 'btn-secondary' }}" data-nav="matriks">
            Matriks Iuran Bulanan
        </a>
    </div>

    {{-- WIFI SETTINGS BUTTON --}}
    <button type="button" onclick="openWifiModal()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;" title="Atur SSID & Password WiFi Asrama">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #94a3b8;">
            <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
            <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
            <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
            <line x1="12" y1="20" x2="12.01" y2="20"></line>
        </svg>
        <span>Pengaturan WiFi</span>
    </button>
</div>

<div class="asrama-wrapper">
    {{-- STATS KEUANGAN GRID --}}
    <div class="asrama-stats-grid">
        <div class="asrama-stat-card">
            <p class="task-meta">Total Pemasukan</p>
            <h3 style="color: #6ee7b7; margin: 0.25rem 0 0 0; font-size: 1.6rem;">Rp {{ number_format($summary['total_pemasukan'], 0, ',', '.') }}</h3>
        </div>
        <div class="asrama-stat-card">
            <p class="task-meta">Total Pengeluaran</p>
            <h3 style="color: #f87171; margin: 0.25rem 0 0 0; font-size: 1.6rem;">Rp {{ number_format($summary['total_pengeluaran'], 0, ',', '.') }}</h3>
        </div>
        <div class="asrama-stat-card">
            <p class="task-meta">Saldo Kas Saat Ini</p>
            <h3 style="color: {{ $summary['saldo_kas'] >= 0 ? '#fde047' : '#f87171' }}; margin: 0.25rem 0 0 0; font-size: 1.6rem;">
                Rp {{ number_format($summary['saldo_kas'], 0, ',', '.') }}
            </h3>
        </div>
    </div>

    {{-- KEUANGAN SECTION --}}
    <div class="widget-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">Riwayat Transaksi Keuangan</h3>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <a href="{{ route('asrama.keuangan.export.excel') }}" class="btn btn-secondary btn-sm" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" title="Download Riwayat Transaksi Format Excel (.csv)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <polyline points="9 15 12 18 15 15"></polyline>
                    </svg>
                    <span>Export Excel</span>
                </a>
                <a href="{{ route('asrama.keuangan.export.pdf') }}" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" title="Cetak / Simpan PDF Riwayat Transaksi">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Export PDF</span>
                </a>
                <button type="button" onclick="openKeuanganModal()" class="btn btn-primary btn-sm">+ Catat Transaksi</button>
            </div>
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
                            <button type="button" class="btn btn-danger btn-sm" onclick="openDeleteModal('{{ route('asrama.keuangan.destroy', $k->id) }}', '{{ addslashes($k->kategori) }} - Rp {{ number_format($k->nominal, 0, ',', '.') }}')">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- MODAL KONFIRMASI HAPUS TRANSAKSI --}}
<div id="modal-delete-keuangan" class="modal modal-sm" aria-hidden="true" style="display: none; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 16px; padding: 1.75rem; background: #1e293b; color: #f8fafc; z-index: 10001;">
    <div style="text-align: center; margin-bottom: 1.25rem;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #ef4444; font-size: 1.75rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
            ⚠️
        </div>
        <h3 style="color: #f8fafc; font-size: 1.15rem; font-weight: 700; margin: 0 0 0.5rem 0;">Konfirmasi Hapus Transaksi</h3>
        <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.5; margin: 0;" id="delete-modal-text">
            Apakah Anda yakin ingin menghapus catatan transaksi ini?
        </p>
    </div>
    <form id="delete-keuangan-form" action="" method="POST">
        @csrf
        @method('DELETE')
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()" style="background: #334155; color: #f8fafc; border: 1px solid rgba(255, 255, 255, 0.1); padding: 0.65rem 1rem; border-radius: 10px; font-weight: 600; cursor: pointer;">
                Batal
            </button>
            <button type="submit" class="btn btn-danger" style="background: #ef4444; color: #ffffff; border: none; padding: 0.65rem 1rem; border-radius: 10px; font-weight: 600; cursor: pointer;">
                Ya, Hapus
            </button>
        </div>
    </form>
</div>
<div id="modal-delete-keuangan-overlay" class="modal-overlay" onclick="closeDeleteModal()"></div>

{{-- MODAL TAMBAH TRANSAKSI KEUANGAN --}}
<div id="modal-keuangan" class="modal modal-create" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Catat Transaksi Keuangan</h3>
            <button type="button" class="modal-close" onclick="closeKeuanganModal()">&times;</button>
        </div>
        <form action="{{ route('asrama.keuangan.store') }}" method="POST">
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
                    <select name="kategori" id="tx-kategori-select" class="form-control" required onchange="togglePenghuniField()">
                        <option value="Iuran Bulanan">Iuran Bulanan</option>
                        <option value="Pembayaran WiFi">Pembayaran WiFi</option>
                        <option value="Pembayaran Sampah">Pembayaran Sampah</option>
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
                    <label class="form-label">Nominal Transaksi <span class="required">*</span></label>
                    <div style="display: flex; align-items: center; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0 0.75rem;">
                        <span style="font-weight: 700; color: #94a3b8; margin-right: 0.5rem;">Rp</span>
                        <input type="text" id="tx-nominal-formatted" class="form-control" placeholder="0" required oninput="formatCurrencyInput(this)" style="border: none; background: transparent; padding-left: 0;">
                    </div>
                    <input type="hidden" name="nominal" id="tx-nominal-raw" value="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Transaksi <span class="required">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-group" id="form-group-penghuni">
                <label class="form-label">Penghuni Terkait (Khusus Iuran)</label>
                <select name="penghuni_id" id="tx-penghuni-select" class="form-control">
                    <option value="">-- Bukan Transaksi Spesifik Penghuni --</option>
                    @foreach($penghunis as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }} (Kamar {{ $p->kamar ? $p->kamar->nomor_kamar : '-' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan / Catatan</label>
                <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Iuran bulan Juli, Pembayaran WiFi Biznet, dll."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeKeuanganModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>
<div id="modal-keuangan-overlay" class="modal-overlay" onclick="closeKeuanganModal()"></div>

{{-- MODAL PENGATURAN WIFI --}}
<div id="modal-wifi" class="modal modal-create" style="display: none; max-width: 440px;">
    <div class="modal-header">
        <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">📶 Pengaturan WiFi Asrama</h3>
        <button type="button" onclick="closeWifiModal()" class="modal-close">&times;</button>
    </div>
    <div class="modal-body" style="padding-top: 1rem;">
        <form action="{{ route('asrama.wifi.settings') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">SSID (Nama WiFi) <span class="required">*</span></label>
                <input type="text" name="wifi_ssid" class="form-control" value="{{ $wifiSettings['ssid'] }}" required placeholder="Contoh: MyHub_Asrama_WiFi">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">Password WiFi <span class="required">*</span></label>
                <input type="text" name="wifi_password" class="form-control" value="{{ $wifiSettings['password'] }}" required placeholder="Contoh: Asrama2026!Pass">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeWifiModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
<div id="modal-wifi-overlay" class="modal-overlay" onclick="closeWifiModal()" style="display: none;"></div>

@endsection

@push('scripts')
<script>
    function openWifiModal() {
        const m = document.getElementById('modal-wifi');
        const o = document.getElementById('modal-wifi-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closeWifiModal() {
        const m = document.getElementById('modal-wifi');
        const o = document.getElementById('modal-wifi-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    function formatNumberWithDots(val) {
        val = val.toString().replace(/\D/g, '');
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatCurrencyInput(elem) {
        let rawVal = elem.value.replace(/\D/g, '');
        elem.value = formatNumberWithDots(rawVal);
        let hiddenInputId = elem.id.replace('-formatted', '-raw');
        let hiddenElem = document.getElementById(hiddenInputId);
        if (hiddenElem) hiddenElem.value = rawVal || 0;
    }

    function togglePenghuniField() {
        const kategoriSelect = document.getElementById('tx-kategori-select');
        const groupPenghuni = document.getElementById('form-group-penghuni');
        const penghuniSelect = document.getElementById('tx-penghuni-select');

        if (!kategoriSelect || !groupPenghuni) return;

        const val = kategoriSelect.value;
        if (val === 'Iuran Bulanan' || val.toLowerCase().includes('iuran')) {
            groupPenghuni.style.display = 'block';
            if (penghuniSelect) penghuniSelect.disabled = false;
        } else {
            groupPenghuni.style.display = 'none';
            if (penghuniSelect) {
                penghuniSelect.value = '';
                penghuniSelect.disabled = true;
            }
        }
    }

    function openKeuanganModal() {
        document.getElementById('tx-nominal-formatted').value = '';
        document.getElementById('tx-nominal-raw').value = '';
        togglePenghuniField();
        const m = document.getElementById('modal-keuangan');
        const o = document.getElementById('modal-keuangan-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closeKeuanganModal() {
        const m = document.getElementById('modal-keuangan');
        const o = document.getElementById('modal-keuangan-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    function openDeleteModal(deleteUrl, transactionInfo) {
        const form = document.getElementById('delete-keuangan-form');
        const text = document.getElementById('delete-modal-text');
        const m = document.getElementById('modal-delete-keuangan');
        const o = document.getElementById('modal-delete-keuangan-overlay');
        if (form) form.action = deleteUrl;
        if (text) text.innerText = 'Apakah Anda yakin ingin menghapus transaksi "' + transactionInfo + '"? Matriks iuran terkait akan otomatis disesuaikan.';
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closeDeleteModal() {
        const m = document.getElementById('modal-delete-keuangan');
        const o = document.getElementById('modal-delete-keuangan-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        togglePenghuniField();

        // Header Topbar Menu Event Listeners (Data Asrama & Keuangan)
        const topbarMenuBtns = document.querySelectorAll('.topbar-menu-btn');
        topbarMenuBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                topbarMenuBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Sub-nav buttons Event Listeners
        const subNavBtns = document.querySelectorAll('.sub-nav-btn');
        subNavBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                subNavBtns.forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-secondary');
                });
                this.classList.remove('btn-secondary');
                this.classList.add('btn-primary');
            });
        });
    });
</script>
@endpush