@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
<style>
    .dashboard-hero {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.95) 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 1.75rem 2rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }

    .dashboard-hero h2 {
        margin: 0 0 0.4rem 0;
        font-size: 1.6rem;
        font-weight: 800;
        color: #f8fafc;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .dashboard-hero p {
        margin: 0;
        color: #94a3b8;
        font-size: 0.95rem;
    }

    .quick-actions-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .stat-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 0.75rem;
    }

    .nav-shortcut-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .nav-shortcut-card {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 1.4rem 1.5rem;
        text-decoration: none;
        transition: all 0.25s ease;
        display: flex;
        align-items: flex-start;
        gap: 1.2rem;
    }

    .nav-shortcut-card:hover {
        background: rgba(30, 41, 59, 0.85);
        border-color: rgba(56, 189, 248, 0.4);
        transform: translateY(-3px);
        box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.4);
    }

    .shortcut-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: rgba(56, 189, 248, 0.12);
        border: 1px solid rgba(56, 189, 248, 0.25);
        color: #38bdf8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .shortcut-content h3 {
        margin: 0 0 0.35rem 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #f8fafc;
    }

    .shortcut-content p {
        margin: 0;
        font-size: 0.85rem;
        color: #94a3b8;
        line-height: 1.4;
    }

    .dashboard-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 992px) {
        .dashboard-two-col {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="asrama-wrapper">
    {{-- HERO BANNER --}}
    <div class="dashboard-hero">
        <div>
            <h2>
                <span>🏢</span> Dashboard AsramaApp
            </h2>
            <p>Sistem Informasi & Manajemen Penghuni, Kamar, serta Keuangan Kas Asrama</p>
        </div>
        <div class="quick-actions-bar">
            <a href="{{ route('asrama.data') }}" class="btn btn-primary btn-sm">+ Kelola Data Penghuni</a>
            <a href="{{ route('asrama.keuangan') }}" class="btn btn-secondary btn-sm">+ Catat Kas</a>
        </div>
    </div>

    {{-- STATISTIK RINGKASAN --}}
    <div class="asrama-stats-grid" style="margin-bottom: 2rem;">
        {{-- CARD 1: PENGHUNI --}}
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(234, 179, 8, 0.15); color: #fde047;">
                👥
            </div>
            <p class="task-meta">Penghuni Aktif</p>
            <h3 style="color: #fde047; margin: 0.25rem 0 0 0; font-size: 1.8rem; font-weight: 800;">
                {{ $totalPenghuni }} <span style="font-size: 0.9rem; font-weight: 500; color: #94a3b8;">Orang</span>
            </h3>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem;">Riwayat Keluar: {{ $penghuniKeluar }} orang</p>
        </div>

        {{-- CARD 2: KAMAR --}}
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                🛏️
            </div>
            <p class="task-meta">Status Kamar</p>
            <h3 style="color: #38bdf8; margin: 0.25rem 0 0 0; font-size: 1.8rem; font-weight: 800;">
                {{ $kamarTersedia }} <span style="font-size: 0.9rem; font-weight: 500; color: #94a3b8;">Tersedia</span>
            </h3>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem;">Total: {{ $totalKamar }} Kamar ({{ $kamarPenuh }} Penuh)</p>
        </div>

        {{-- CARD 3: SALDO KAS --}}
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                💰
            </div>
            <p class="task-meta">Saldo Kas Asrama</p>
            <h3 style="color: #34d399; margin: 0.25rem 0 0 0; font-size: 1.6rem; font-weight: 800;">
                Rp {{ number_format($saldoKas, 0, ',', '.') }}
            </h3>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem;">Masuk: Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
        </div>

        {{-- CARD 4: PENGELUARAN --}}
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(239, 68, 68, 0.15); color: #f87171;">
                📉
            </div>
            <p class="task-meta">Total Pengeluaran Kas</p>
            <h3 style="color: #f87171; margin: 0.25rem 0 0 0; font-size: 1.6rem; font-weight: 800;">
                Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
            </h3>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem;">Tercatat dalam jurnal kas</p>
        </div>
    </div>

    {{-- SHORTCUT GRID --}}
    <h3 class="widget-title" style="margin-bottom: 1rem; font-size: 1.15rem;">Akses Cepat Modul Asrama</h3>
    <div class="nav-shortcut-grid">
        <a href="{{ route('asrama.data') }}" class="nav-shortcut-card">
            <div class="shortcut-icon">📋</div>
            <div class="shortcut-content">
                <h3>Data Penghuni & Kamar</h3>
                <p>Kelola daftar kamar, status lantai, serta biodata dan pendaftaran penghuni asrama.</p>
            </div>
        </a>

        <a href="{{ route('asrama.keuangan') }}" class="nav-shortcut-card">
            <div class="shortcut-icon">💳</div>
            <div class="shortcut-content">
                <h3>Riwayat Transaksi Kas</h3>
                <p>Pencatatan uang masuk, keluar, pembayaran iuran, serta export laporan PDF & Excel.</p>
            </div>
        </a>

        <a href="{{ route('asrama.keuangan.matriks') }}" class="nav-shortcut-card">
            <div class="shortcut-icon" style="background: rgba(168, 85, 247, 0.12); border-color: rgba(168, 85, 247, 0.25); color: #c084fc;">📊</div>
            <div class="shortcut-content">
                <h3>Matriks Iuran Bulanan</h3>
                <p>Tabel kontrol iuran bulanan per penghuni untuk mengecek status kelunasan secara visual.</p>
            </div>
        </a>
    </div>

    {{-- DUAL COLUMN SECTION --}}
    <div class="dashboard-two-col">
        {{-- TRANSAKSI TERBARU --}}
        <div class="widget-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 class="widget-title" style="margin: 0; font-size: 1.05rem;">Transaksi Kas Terbaru</h3>
                <a href="{{ route('asrama.keuangan') }}" style="font-size: 0.85rem; color: #38bdf8; text-decoration: none; font-weight: 600;">Lihat Semua &rarr;</a>
            </div>

            @if($recentTransactions->isEmpty())
            <p class="empty-state">Belum ada transaksi kas yang tercatat.</p>
            @else
            <div class="table-wrapper">
                <table class="table" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Kategori</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $tx)
                        <tr>
                            <td class="task-meta">{{ \Carbon\Carbon::parse($tx->tanggal)->format('d M Y') }}</td>
                            <td>
                                @if($tx->tipe === 'pemasukan')
                                <span class="badge badge-success">Pemasukan</span>
                                @else
                                <span class="badge badge-danger">Pengeluaran</span>
                                @endif
                            </td>
                            <td>{{ $tx->kategori }}</td>
                            <td style="font-weight: 700; color: {{ $tx->tipe === 'pemasukan' ? '#34d399' : '#f87171' }};">
                                {{ $tx->tipe === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($tx->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- STATUS KAMAR OVERVIEW --}}
        <div class="widget-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 class="widget-title" style="margin: 0; font-size: 1.05rem;">Daftar Kamar & Penghuni</h3>
                <a href="{{ route('asrama.data') }}" style="font-size: 0.85rem; color: #38bdf8; text-decoration: none; font-weight: 600;">Kelola Kamar &rarr;</a>
            </div>

            @if($kamars->isEmpty())
            <p class="empty-state">Belum ada data kamar terdaftar.</p>
            @else
            <div class="table-wrapper">
                <table class="table" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Kamar</th>
                            <th>Lantai</th>
                            <th>Status</th>
                            <th>Penghuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kamars->take(6) as $km)
                        <tr>
                            <td style="font-weight: 700;">{{ $km->nomor_kamar }}</td>
                            <td class="task-meta">Lt. {{ $km->lantai }}</td>
                            <td>
                                @if($km->status === 'Tersedia')
                                <span class="badge badge-success">Tersedia</span>
                                @elseif($km->status === 'Penuh')
                                <span class="badge badge-warning">Penuh</span>
                                @elseif($km->status === 'Gudang')
                                <span class="badge badge-secondary">Gudang</span>
                                @else
                                <span class="badge badge-danger">Perbaikan</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $km->penghunis->count() }}</strong> Orang
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
