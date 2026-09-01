@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
<style>
    .quick-actions-bar {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
        justify-content: flex-end;
    }

    .stat-card-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
    }

    .nav-shortcut-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 0.85rem;
        margin-bottom: 1.5rem;
    }

    .nav-shortcut-card {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 0.9rem 1.1rem;
        text-decoration: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .nav-shortcut-card:hover {
        background: rgba(30, 41, 59, 0.85);
        border-color: rgba(56, 189, 248, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.4);
    }

    .shortcut-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: rgba(56, 189, 248, 0.12);
        border: 1px solid rgba(56, 189, 248, 0.25);
        color: #38bdf8;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .shortcut-content h3 {
        margin: 0 0 0.2rem 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #f8fafc;
    }

    .shortcut-content p {
        margin: 0;
        font-size: 0.78rem;
        color: #94a3b8;
        line-height: 1.35;
    }

    .dashboard-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
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
    {{-- STATISTIK RINGKASAN --}}
    <div class="asrama-stats-grid">
        {{-- CARD 1: PENGHUNI --}}
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #fbbf24;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <p class="task-meta">Penghuni Aktif</p>
            <h3 style="color: #fbbf24; margin: 0.2rem 0 0 0; font-size: 1.45rem; font-weight: 800;">
                {{ $totalPenghuni }} <span style="font-size: 0.82rem; font-weight: 500; color: #94a3b8;">Orang</span>
            </h3>
            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">Riwayat Keluar: <span style="color: #cbd5e1; font-weight: 600;">{{ $penghuniKeluar }}</span> orang</p>
        </div>

        {{-- CARD 2: KAMAR --}}
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 7v13"></path>
                    <path d="M21 7v13"></path>
                    <path d="M3 13h18"></path>
                    <path d="M3 7h18"></path>
                    <circle cx="7" cy="10" r="1"></circle>
                </svg>
            </div>
            <p class="task-meta">Status Kamar</p>
            <h3 style="color: #38bdf8; margin: 0.2rem 0 0 0; font-size: 1.45rem; font-weight: 800;">
                {{ $kamarTersedia }} <span style="font-size: 0.82rem; font-weight: 500; color: #94a3b8;">Tersedia</span>
            </h3>
            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">Total: <span style="color: #cbd5e1; font-weight: 600;">{{ $totalKamar }}</span> Kamar ({{ $kamarPenuh }} Penuh)</p>
        </div>

        {{-- CARD 3: SALDO KAS --}}
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
            </div>
            <p class="task-meta">Saldo Kas Asrama</p>
            <h3 style="color: #34d399; margin: 0.2rem 0 0 0; font-size: 1.45rem; font-weight: 800;">
                Rp {{ number_format($saldoKas, 0, ',', '.') }}
            </h3>
            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">Masuk: <span style="color: #6ee7b7; font-weight: 600;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</span></p>
        </div>

        {{-- CARD 4: PENGELUARAN --}}
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fb7185;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                    <polyline points="17 18 23 18 23 12"></polyline>
                </svg>
            </div>
            <p class="task-meta">Total Pengeluaran Kas</p>
            <h3 style="color: #fb7185; margin: 0.2rem 0 0 0; font-size: 1.45rem; font-weight: 800;">
                Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
            </h3>
            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">Tercatat dalam jurnal kas</p>
        </div>
    </div>

    {{-- SHORTCUT GRID --}}
    <div class="nav-shortcut-grid">
        <a href="{{ route('asrama.data') }}" class="nav-shortcut-card">
            <div class="shortcut-icon" style="background: rgba(56, 189, 248, 0.12); border-color: rgba(56, 189, 248, 0.3); color: #38bdf8;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="shortcut-content">
                <h3>Data Penghuni & Kamar</h3>
                <p>Kelola daftar kamar, status lantai, dan penghuni.</p>
            </div>
        </a>

        <a href="{{ route('asrama.keuangan') }}" class="nav-shortcut-card">
            <div class="shortcut-icon" style="background: rgba(16, 185, 129, 0.12); border-color: rgba(16, 185, 129, 0.3); color: #34d399;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
            </div>
            <div class="shortcut-content">
                <h3>Riwayat Transaksi Kas</h3>
                <p>Pencatatan kas masuk/keluar & laporan.</p>
            </div>
        </a>

        <a href="{{ route('asrama.keuangan.matriks') }}" class="nav-shortcut-card">
            <div class="shortcut-icon" style="background: rgba(168, 85, 247, 0.12); border-color: rgba(168, 85, 247, 0.3); color: #c084fc;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="shortcut-content">
                <h3>Matriks Iuran Bulanan</h3>
                <p>Tabel kontrol iuran & distribusi WiFi.</p>
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
                                <span class="badge badge-tipe-pemasukan">Masuk</span>
                                @else
                                <span class="badge badge-tipe-pengeluaran">Keluar</span>
                                @endif
                            </td>
                            <td><span class="badge-chip">{{ $tx->kategori }}</span></td>
                            <td style="font-weight: 700; color: {{ $tx->tipe === 'pemasukan' ? '#34d399' : '#fb7185' }};">
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
                            <td style="font-weight: 700; color: #f8fafc;">Kamar {{ $km->nomor_kamar }}</td>
                            <td class="task-meta">Lantai {{ $km->lantai }}</td>
                            <td>
                                @if($km->status === 'Tersedia')
                                <span class="badge badge-kamar-tersedia">Tersedia</span>
                                @elseif($km->status === 'Penuh')
                                <span class="badge badge-kamar-penuh">Penuh</span>
                                @elseif($km->status === 'Gudang')
                                <span class="badge badge-kamar-gudang">Gudang</span>
                                @else
                                <span class="badge badge-kamar-perbaikan">Perbaikan</span>
                                @endif
                            </td>
                            <td>
                                @php $occ = $km->penghunis->where('status_penghuni', 'Aktif')->count(); @endphp
                                @if($occ > 0)
                                <span class="badge badge-kamar-penuh" style="font-size: 0.72rem; padding: 1px 6px;">1 / 1 Terisi</span>
                                @else
                                <span class="badge badge-kamar-tersedia" style="font-size: 0.72rem; padding: 1px 6px;">0 / 1 Kosong</span>
                                @endif
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