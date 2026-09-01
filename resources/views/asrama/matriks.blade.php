@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
<style>
    .matriks-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.85rem;
        margin-bottom: 1.25rem;
    }

    .matriks-stat-card {
        background: var(--bg-card-2, rgba(30, 41, 59, 0.6));
        border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.08));
        border-radius: 8px;
        padding: 0.85rem 1.1rem;
        backdrop-filter: blur(8px);
    }

    .matriks-table-wrapper {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid var(--border-default, rgba(148, 163, 184, 0.18));
        background: var(--bg-card, rgba(15, 23, 42, 0.96));
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }

    .matriks-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1250px;
        font-size: 0.85rem;
    }

    .matriks-table th,
    .matriks-table td {
        padding: 0.65rem 0.6rem;
        border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.07));
        text-align: center;
        vertical-align: middle;
    }

    .matriks-table th {
        background: var(--bg-card-2, rgba(30, 41, 59, 0.98));
        color: var(--text-primary);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.75rem;
    }

    .matriks-table th.col-current-month {
        background: rgba(245, 158, 11, 0.18);
        border-bottom: 2px solid #f59e0b;
        color: #fbbf24;
    }

    .matriks-table th.col-nama,
    .matriks-table td.col-nama {
        text-align: left;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: var(--bg-card-2, rgba(30, 41, 59, 0.98));
        z-index: 2;
        min-width: 190px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
    }

    .matriks-table td.col-nama {
        background: var(--bg-card, rgba(15, 23, 42, 0.96));
        color: var(--text-primary);
    }

    .cell-paid {
        background: #10b981 !important;
        color: #ffffff !important;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.15s ease, filter 0.15s ease;
    }

    .cell-paid:hover {
        filter: brightness(1.15);
        transform: scale(1.02);
    }

    .cell-empty {
        background: var(--bg-card, rgba(15, 23, 42, 0.96));
        color: var(--text-muted);
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .cell-empty:hover {
        background: var(--bg-card-hover, rgba(30, 41, 59, 0.8));
        color: var(--text-primary);
    }

    .cell-not-joined {
        background: var(--bg-card-2, rgba(30, 41, 59, 0.6)) !important;
        color: var(--text-faint) !important;
        font-style: italic;
        font-size: 0.72rem;
    }

    .row-divider {
        background: var(--bg-card-2, rgba(30, 41, 59, 0.9)) !important;
        color: var(--text-muted);
        font-weight: 700;
        font-style: italic;
        text-align: left !important;
        letter-spacing: 1px;
        padding-left: 1rem !important;
    }

    .preset-btn {
        background: var(--bg-card-hover, rgba(255, 255, 255, 0.06));
        border: 1px solid var(--border-default, rgba(255, 255, 255, 0.15));
        color: var(--text-primary);
        border-radius: 6px;
        padding: 0.35rem 0.65rem;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .preset-btn:hover {
        background: var(--accent-primary, #6366f1);
        border-color: var(--accent-primary, #6366f1);
        color: #fff;
    }

    /* Light Theme Overrides for Matriks */
    [data-theme="light"] .matriks-table-wrapper {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.06);
    }

    [data-theme="light"] .matriks-table th {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    [data-theme="light"] .matriks-table td {
        border-color: #e2e8f0;
    }

    [data-theme="light"] .matriks-table th.col-current-month {
        background: #fef3c7;
        color: #b45309;
        border-bottom: 2px solid #d97706;
    }

    [data-theme="light"] .matriks-table th.col-nama {
        background: #f1f5f9;
        color: #0f172a;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    }

    [data-theme="light"] .matriks-table td.col-nama {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    }

    [data-theme="light"] .cell-empty {
        background: #ffffff;
        color: #94a3b8;
    }

    [data-theme="light"] .cell-empty:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    [data-theme="light"] .cell-not-joined {
        background: #f1f5f9 !important;
        color: #94a3b8 !important;
    }

    [data-theme="light"] .row-divider {
        background: #e2e8f0 !important;
        color: #334155;
    }

    [data-theme="light"] .preset-btn {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #334155;
    }
</style>
@endpush



@section('content')

<div class="asrama-wrapper">
    {{-- STATS GRID MATRIKS --}}
    <div class="matriks-stats-grid">
        <div class="matriks-stat-card">
            <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.35rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
            </div>
            <p class="task-meta" style="margin: 0; font-size: 0.75rem;">Total Terbayar Tahun {{ $tahun }}</p>
            <h3 style="color: #6ee7b7; margin: 0.2rem 0 0 0; font-size: 1.25rem; font-weight: 700;">
                Rp {{ number_format($statsMatriks['total_terbayar'], 0, ',', '.') }}
            </h3>
        </div>
        <div class="matriks-stat-card">
            <div class="stat-card-icon" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #fbbf24; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.35rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <p class="task-meta" style="margin: 0; font-size: 0.75rem;">Lunas Bulan Ini ({{ \Carbon\Carbon::now()->format('F') }})</p>
            <h3 style="color: #fde047; margin: 0.2rem 0 0 0; font-size: 1.25rem; font-weight: 700;">
                {{ $statsMatriks['lunas_bulan_ini'] }} / {{ $statsMatriks['total_aktif'] }} Penghuni
            </h3>
        </div>
        <div class="matriks-stat-card">
            <div class="stat-card-icon" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.35rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
            </div>
            <p class="task-meta" style="margin: 0; font-size: 0.75rem;">Tarif Standar / Bulan</p>
            <h3 style="color: #38bdf8; margin: 0.2rem 0 0 0; font-size: 1.25rem; font-weight: 700;">
                Rp {{ number_format($tarifDefault, 0, ',', '.') }}
            </h3>
        </div>
        <div class="matriks-stat-card">
            <div class="stat-card-icon" style="background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.35); color: #c084fc; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.35rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
            <p class="task-meta" style="margin: 0; font-size: 0.75rem;">Penghuni Aktif</p>
            <h3 style="color: #c084fc; margin: 0.2rem 0 0 0; font-size: 1.25rem; font-weight: 700;">
                {{ $statsMatriks['total_aktif'] }} Orang
            </h3>
        </div>
    </div>

    {{-- TABLE WIDGET CARD --}}
    <div class="widget-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">Matriks Pembayaran Iuran & Fasilitas (Tahun {{ $tahun }})</h3>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                {{-- WIFI DISTRIBUTION BUTTON --}}
                <button type="button" onclick="openWifiDistributionModal()" class="btn btn-secondary btn-sm" style="background: rgba(14, 165, 233, 0.18); border-color: rgba(14, 165, 233, 0.45); color: #38bdf8; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem;" title="Distribusi Akses Password WiFi via WhatsApp">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
                        <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                        <line x1="12" y1="20" x2="12.01" y2="20"></line>
                    </svg>
                    <span>Distribusi Password WiFi</span>
                </button>

                {{-- EXPORT BUTTONS --}}
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <a href="{{ route('asrama.keuangan.matriks.export.excel', ['tahun' => $tahun]) }}" class="btn btn-secondary btn-sm" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" title="Download Matriks Iuran Format Excel (.csv)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <polyline points="9 15 12 18 15 15"></polyline>
                        </svg>
                        <span>Export Excel</span>
                    </a>
                    <a href="{{ route('asrama.keuangan.matriks.export.pdf', ['tahun' => $tahun, 'tarif_default' => $tarifDefault]) }}" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" title="Cetak / Simpan PDF Matriks Iuran">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span>Export PDF</span>
                    </a>
                </div>

                {{-- QUICK SEARCH INPUT --}}
                <div style="position: relative;">
                    <input type="text" id="search-matriks" class="form-control" placeholder="Cari nama..." onkeyup="filterMatriksTable()" style="padding: 0.35rem 0.75rem; font-size: 0.85rem; width: 150px;">
                </div>

                {{-- FORM FILTER & TARIF DEFAULT --}}
                <form id="form-filter-matriks" action="{{ route('asrama.keuangan.matriks') }}" method="GET" style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.35rem;" title="Atur nominal iuran bulanan default">
                        <label class="form-label" style="margin: 0; white-space: nowrap; font-size: 0.8rem; color: #94a3b8;">Tarif Default:</label>
                        <div style="display: flex; align-items: center; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; padding: 0 0.4rem;">
                            <span style="font-size: 0.8rem; color: #6ee7b7; font-weight: 700;">Rp</span>
                            <input type="number" name="tarif_default" value="{{ $tarifDefault }}" class="form-control" style="width: 90px; padding: 0.3rem 0.3rem; font-size: 0.85rem; border: none; background: transparent; font-weight: 700; color: #6ee7b7;" onchange="document.getElementById('form-filter-matriks').submit()" placeholder="100000" step="5000">
                        </div>
                    </div>

                    <select name="tahun" class="form-control" style="width: auto; padding: 0.35rem 0.75rem; font-size: 0.85rem;" onchange="document.getElementById('form-filter-matriks').submit()">
                        @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="matriks-table-wrapper">
            <table class="matriks-table" id="table-matriks">
                <thead>
                    @php $currentMonth = (int) date('n'); @endphp
                    <tr>
                        <th class="col-nama">Nama</th>
                        @foreach($bulanNames as $blnNum => $blnName)
                        <th class="{{ ($tahun == date('Y') && $blnNum == $currentMonth) ? 'col-current-month' : '' }}">
                            {{ $blnName }}
                            @if($tahun == date('Y') && $blnNum == $currentMonth)
                            <div style="font-size: 0.65rem; color: #f59e0b; font-weight: 800; text-transform: uppercase; margin-top: 1px;">• Bulan Ini •</div>
                            @endif
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- FIXED EXPENSES ROWS (WIFI & Iuran Sampah) --}}
                    @foreach(['wifi' => 'WIFI', 'sampah' => 'Iuran Sampah'] as $key => $label)
                    <tr class="matriks-row">
                        <td class="col-nama" style="font-weight: 700; color: #f8fafc;">
                            {{ $label }}
                        </td>
                        @foreach($bulanNames as $bNum => $bName)
                        @php
                        $cell = $iuranMap['fasilitas_' . $key . '_' . $bNum] ?? null;
                        $isLunas = $cell ? $cell->status_lunas : false;
                        $catName = ($key === 'wifi') ? 'Pembayaran WiFi' : 'Pembayaran Sampah';
                        @endphp
                        <td class="{{ $isLunas ? 'cell-paid' : 'cell-empty' }}" style="cursor: default;" title="Status {{ $label }} {{ $bName }} otomatis terisi dari Transaksi Kas (Kategori: {{ $catName }})">
                            @if($isLunas)
                            <span style="font-size: 1.15rem; color: #6ee7b7; font-weight: bold;">☑</span>
                            @else
                            <span style="font-size: 1.15rem; color: #475569;">☐</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach

                    {{-- ACTIVE RESIDENTS ROWS --}}
                    @foreach($penghuniAktif as $p)
                    @php
                    $effectiveJoinDate = $p->tanggal_masuk ?: '2026-01-01';
                    $pJoinCarbon = \Carbon\Carbon::parse($effectiveJoinDate);
                    $pJoinYear = (int)$pJoinCarbon->format('Y');
                    $pJoinMonth = (int)$pJoinCarbon->format('m');
                    $pJoinDay = (int)$pJoinCarbon->format('d');

                    $nowCarbon = \Carbon\Carbon::now();
                    $cYear = (int)$nowCarbon->format('Y');
                    $cMonth = (int)$nowCarbon->format('n');

                    $untilM = 12;
                    if ($tahun == $cYear) {
                    $untilM = $cMonth;
                    } elseif ($tahun > $cYear) {
                    $untilM = 0;
                    }

                    $pTotalObligation = 0;
                    if ($tahun >= $pJoinYear) {
                    $startM = ($tahun == $pJoinYear) ? $pJoinMonth : 1;
                    for ($m = $startM; $m <= $untilM; $m++) {
                        if ($tahun==$pJoinYear && $m==$pJoinMonth) {
                        $tDays=$pJoinCarbon->daysInMonth;
                        if ($pJoinDay == 1) {
                        $pTotalObligation += $tarifDefault;
                        } else {
                        $sisaH = max(1, $tDays - $pJoinDay);
                        $rawP = ($tarifDefault / $tDays) * $sisaH;
                        $pTotalObligation += (int) (round($rawP / 1000) * 1000);
                        }
                        } else {
                        $pTotalObligation += $tarifDefault;
                        }
                        }
                        }

                        $pTotalPaid = \App\Models\AsramaKeuangan::where('penghuni_id', $p->id)
                        ->whereYear('tanggal', $tahun)
                        ->sum('nominal');

                        $pTunggakan = max(0, $pTotalObligation - $pTotalPaid);
                        @endphp
                        <tr class="matriks-row">
                            <td class="col-nama">
                                <div style="display: flex; flex-direction: column;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.4rem;">
                                        <span style="font-weight: 600; color: #f8fafc;">{{ $p->nama }}</span>
                                        @if($pTunggakan > 0)
                                        <span onclick="openWifiDistributionModal()" style="cursor: pointer; font-size: 0.66rem; font-weight: 700; background: rgba(239, 68, 68, 0.18); color: #fca5a5; padding: 1px 5px; border-radius: 4px; border: 1px solid rgba(239, 68, 68, 0.3); white-space: nowrap;" title="WiFi Ditahan (Ada Tunggakan). Klik untuk buka panel distribusi WiFi.">🚫 WiFi</span>
                                        @else
                                        <span onclick="openWifiDistributionModal()" style="cursor: pointer; font-size: 0.66rem; font-weight: 700; background: rgba(16, 185, 129, 0.18); color: #6ee7b7; padding: 1px 5px; border-radius: 4px; border: 1px solid rgba(16, 185, 129, 0.3); white-space: nowrap;" title="WiFi Siap Kirim (Lunas). Klik untuk buka panel distribusi WiFi.">📶 WiFi OK</span>
                                        @endif
                                    </div>
                                    @if($p->kamar)
                                    <span style="font-size: 0.72rem; color: #94a3b8; margin-top: 1px;">Kamar {{ $p->kamar->nomor_kamar }}</span>
                                    @endif
                                    @if($pTunggakan > 0)
                                    <span style="font-size: 0.72rem; color: #f87171; font-weight: 700; margin-top: 2px;" title="Total kekurangan iuran s.d {{ \Carbon\Carbon::now()->format('F') }}">
                                        Kurang: Rp {{ number_format($pTunggakan, 0, ',', '.') }}
                                    </span>
                                    @else
                                    <span style="font-size: 0.72rem; color: #6ee7b7; font-weight: 700; margin-top: 2px;" title="Iuran s.d bulan {{ \Carbon\Carbon::now()->format('F') }} telah lunas">
                                        ✓ Lunas
                                    </span>
                                    @endif
                                </div>
                            </td>
                            @foreach($bulanNames as $bNum => $bName)
                            @php
                            $cell = $iuranMap['penghuni_' . $p->id . '_' . $bNum] ?? null;
                            $nominal = $cell ? $cell->nominal : 0;

                            $isPriorToJoin = false;
                            $isJoinMonth = false;
                            $proratedFee = 0;
                            $sisaHari = 0;

                            $effectiveJoinDate = $p->tanggal_masuk ?: '2026-01-01';
                            $joinCarbon = \Carbon\Carbon::parse($effectiveJoinDate);
                            $joinYear = (int)$joinCarbon->format('Y');
                            $joinMonth = (int)$joinCarbon->format('m');
                            $joinDay = (int)$joinCarbon->format('d');

                            if ($tahun < $joinYear || ($tahun==$joinYear && $bNum < $joinMonth)) {
                                $isPriorToJoin=true;
                                } elseif ($tahun==$joinYear && $bNum==$joinMonth) {
                                $isJoinMonth=true;
                                $totalDaysInMonth=$joinCarbon->daysInMonth;
                                if ($joinDay == 1) {
                                $sisaHari = $totalDaysInMonth;
                                $proratedFee = $tarifDefault;
                                } else {
                                $sisaHari = max(1, $totalDaysInMonth - $joinDay);
                                $rawProrata = ($tarifDefault / $totalDaysInMonth) * $sisaHari;
                                $proratedFee = (int) (round($rawProrata / 1000) * 1000);
                                }
                                }
                                @endphp

                                @if($isPriorToJoin && $nominal == 0)
                                <td class="cell-not-joined" title="Masuk: {{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d/m/Y') }}">
                                    masuk : {{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d/m/Y') }}
                                </td>
                                @elseif($isJoinMonth && $nominal == 0)
                                <td class="cell-empty" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); cursor: pointer;" onclick="openCellModal({{ $p->id }}, null, '{{ addslashes($p->nama) }}', {{ $bNum }}, '{{ $bName }}', {{ $nominal }}, 0, {{ $proratedFee }}, {{ $sisaHari }})" title="Klik untuk bayar iuran prorata {{ $p->nama }} (Masuk {{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d/m/Y') }})">
                                    <div style="font-size: 0.65rem; color: #f59e0b; font-weight: 700; text-transform: uppercase;">Masuk {{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d/m') }}</div>
                                    <div style="font-size: 0.78rem; font-weight: 700; color: #6ee7b7;">Rp {{ number_format($proratedFee, 0, ',', '.') }}</div>
                                    <div style="font-size: 0.65rem; color: #cbd5e1;">({{ $sisaHari }} hr)</div>
                                </td>
                                @else
                                <td class="{{ $nominal > 0 ? 'cell-paid' : 'cell-empty' }}" onclick="openCellModal({{ $p->id }}, null, '{{ addslashes($p->nama) }}', {{ $bNum }}, '{{ $bName }}', {{ $nominal }}, {{ $nominal > 0 ? 1 : 0 }}, {{ $proratedFee }}, {{ $sisaHari }})" title="Klik untuk ubah iuran {{ $p->nama }} ({{ $bName }})">
                                    @if($nominal > 0)
                                    Rp {{ number_format($nominal, 0, ',', '.') }}
                                    @else
                                    <span style="opacity: 0.3;">Rp 0</span>
                                    @endif
                                </td>
                                @endif
                                @endforeach
                        </tr>
                        @endforeach

                        {{-- KELUAR DIVIDER ROW --}}
                        <tr>
                            <td colspan="13" class="row-divider">
                                Keluar (Penghuni Non-Aktif)
                            </td>
                        </tr>

                        {{-- FORMER RESIDENTS ROWS --}}
                        @foreach($penghuniKeluar as $p)
                        <tr class="matriks-row">
                            <td class="col-nama">
                                <div style="display: flex; flex-direction: column; opacity: 0.85;">
                                    <span style="font-weight: 500; color: #cbd5e1;">{{ $p->nama }}</span>
                                    <span style="font-size: 0.7rem; color: #64748b;">(Keluar)</span>
                                </div>
                            </td>
                            @foreach($bulanNames as $bNum => $bName)
                            @php
                            $cell = $iuranMap['penghuni_' . $p->id . '_' . $bNum] ?? null;
                            $nominal = $cell ? $cell->nominal : 0;
                            @endphp
                            <td class="{{ $nominal > 0 ? 'cell-paid' : 'cell-empty' }}" onclick="openCellModal({{ $p->id }}, null, '{{ addslashes($p->nama) }}', {{ $bNum }}, '{{ $bName }}', {{ $nominal }}, {{ $nominal > 0 ? 1 : 0 }})" title="Klik untuk ubah iuran {{ $p->nama }} ({{ $bName }})">
                                @if($nominal > 0)
                                Rp {{ number_format($nominal, 0, ',', '.') }}
                                @else
                                <span style="opacity: 0.25;">Rp 0</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL UPDATE MATRIKS CELL --}}
<div id="modal-matriks-cell" class="modal modal-create" onclick="event.stopPropagation()">
    <div class="modal-header">
        <h3 id="cell-modal-title">Update Status Pembayaran</h3>
        <button onclick="closeCellModal()" class="modal-close">&times;</button>
    </div>
    <form action="{{ route('asrama.keuangan.matriks.update') }}" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <input type="hidden" id="cell-bulan" name="bulan" value="">
        <input type="hidden" id="cell-penghuni-id" name="penghuni_id" value="">
        <input type="hidden" id="cell-fasilitas-key" name="fasilitas_key" value="">

        <div style="padding: 0.5rem 0;">
            <p class="task-meta" style="margin-bottom: 1.25rem; color: var(--text-primary); font-size: 0.95rem;">
                Target: <strong id="cell-item-name" style="color: #6ee7b7; font-size: 1.05rem;">-</strong><br>
                Bulan: <strong id="cell-month-name" style="color: #fde047;">-</strong> {{ $tahun }}
            </p>

            <div id="wrapper-nominal" class="form-group">
                <label class="form-label">Nominal Pembayaran</label>
                <div style="display: flex; align-items: center; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--radius-md, 8px); padding: 0 0.75rem;">
                    <span style="font-weight: 700; color: #6ee7b7; margin-right: 0.4rem; font-size: 0.95rem;">Rp</span>
                    <input type="text" id="cell-nominal-formatted" class="form-control" style="border: none; background: transparent; padding-left: 0; font-weight: 600;" placeholder="100.000" onkeyup="formatCurrencyInput(this)">
                    <input type="hidden" id="cell-nominal-raw" name="nominal" value="0">
                </div>

                {{-- DYNAMIC PRESET SHORTCUT BUTTONS --}}
                <div style="display: flex; gap: 0.4rem; margin-top: 0.75rem; flex-wrap: wrap;">
                    <button type="button" id="btn-prorata-preset" class="preset-btn" style="display: none; background: rgba(245, 158, 11, 0.2); border-color: #f59e0b; color: #fde047; font-weight: 700;"></button>
                    <button type="button" onclick="setNominal({{ $tarifDefault }})" class="preset-btn" style="background: rgba(16, 185, 129, 0.2); border-color: #10b981; color: #6ee7b7; font-weight: 700;">
                        Standar (Rp {{ number_format($tarifDefault, 0, ',', '.') }})
                    </button>
                    @if($tarifDefault != 100000)
                    <button type="button" onclick="setNominal(100000)" class="preset-btn">Rp 100.000</button>
                    @endif
                    <button type="button" onclick="setNominal({{ (int)($tarifDefault / 2) }})" class="preset-btn">1/2 (Rp {{ number_format((int)($tarifDefault / 2), 0, ',', '.') }})</button>
                    <button type="button" onclick="setNominal(0)" class="preset-btn" style="color: #f87171;">Reset 0</button>
                </div>
            </div>

            <div id="wrapper-status" class="form-group" style="display: flex; align-items: center; gap: 0.6rem; margin-top: 1rem; background: rgba(255,255,255,0.03); padding: 0.75rem; border-radius: 8px;">
                <input type="checkbox" id="cell-status-lunas" name="status_lunas" value="1" style="width: 20px; height: 20px; cursor: pointer;">
                <label for="cell-status-lunas" class="form-label" style="margin: 0; cursor: pointer; font-weight: 600; color: #f8fafc;">Tandai Lunas</label>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 1.25rem;">
            <button type="button" onclick="closeCellModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Data</button>
        </div>
    </form>
</div>
<div id="modal-matriks-overlay" class="modal-overlay" onclick="closeCellModal()"></div>

{{-- MODAL DISTRIBUSI PASSWORD WIFI --}}
<div id="modal-wifi-distribusi" class="modal modal-lg" onclick="event.stopPropagation()" style="max-width: 780px;">
    <div class="modal-header" style="align-items: center;">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.4); display: flex; align-items: center; justify-content: center; color: #38bdf8;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
                    <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                    <line x1="12" y1="20" x2="12.01" y2="20"></line>
                </svg>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; color: #f8fafc;">Distribusi Akses Password WiFi</h3>
                <p style="margin: 0; font-size: 0.8rem; color: #94a3b8;">Kirim kredensial WiFi ke penghuni lunas atau kirim pengingat tagihan</p>
            </div>
        </div>
        <button onclick="closeWifiDistributionModal()" class="modal-close">&times;</button>
    </div>

    {{-- TOP MONTH PICKER & CURRENT CREDENTIAL CARD --}}
    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 0.9rem 1.1rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <span style="font-size: 0.78rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Bulan Akses WiFi</span>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                <select id="wifi-select-bulan" class="form-control" style="width: 140px; padding: 0.35rem 0.6rem; font-size: 0.85rem;" onchange="loadWifiDistributionData()">
                    @foreach($bulanNames as $bNum => $bName)
                    <option value="{{ $bNum }}" {{ $bNum == (int)date('n') ? 'selected' : '' }}>{{ $bName }}</option>
                    @endforeach
                </select>
                <select id="wifi-select-tahun" class="form-control" style="width: 100px; padding: 0.35rem 0.6rem; font-size: 0.85rem;" onchange="loadWifiDistributionData()">
                    @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.4rem 0.8rem;">
                <div style="font-size: 0.72rem; color: #94a3b8;">SSID Aktif:</div>
                <div id="wifi-display-ssid" style="font-weight: 700; color: #38bdf8; font-size: 0.9rem;">-</div>
            </div>
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.4rem 0.8rem; position: relative;">
                <div style="font-size: 0.72rem; color: #94a3b8;">Password Aktif:</div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span id="wifi-display-password" style="font-weight: 700; color: #fde047; font-size: 0.9rem; font-family: monospace;">-</span>
                    <button type="button" onclick="copyWifiPassword()" style="background: none; border: none; color: #94a3b8; cursor: pointer; padding: 0;" title="Salin Password">📋</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL NAVIGATION TABS --}}
    <div style="display: flex; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 1.25rem; gap: 0.5rem;">
        <button type="button" id="tab-btn-lunas" onclick="switchWifiTab('lunas')" class="btn" style="background: transparent; border: none; border-bottom: 2px solid #10b981; color: #6ee7b7; font-weight: 700; border-radius: 0; padding: 0.5rem 1rem; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.4rem;">
            <span>🟢 Berhak Dapat WiFi (Lunas)</span>
            <span id="badge-count-lunas" style="background: #10b981; color: #000; font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 12px; font-weight: 800;">0</span>
        </button>
        <button type="button" id="tab-btn-unpaid" onclick="switchWifiTab('unpaid')" class="btn" style="background: transparent; border: none; border-bottom: 2px solid transparent; color: #94a3b8; font-weight: 600; border-radius: 0; padding: 0.5rem 1rem; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.4rem;">
            <span>🔴 Belum Lunas (WiFi Ditahan)</span>
            <span id="badge-count-unpaid" style="background: #ef4444; color: #fff; font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 12px; font-weight: 800;">0</span>
        </button>
        <button type="button" id="tab-btn-settings" onclick="switchWifiTab('settings')" class="btn" style="background: transparent; border: none; border-bottom: 2px solid transparent; color: #94a3b8; font-weight: 600; border-radius: 0; padding: 0.5rem 1rem; font-size: 0.88rem; margin-left: auto; display: inline-flex; align-items: center; gap: 0.4rem;">
            <span>⚙️ Pengaturan Password & Template</span>
        </button>
    </div>

    {{-- TAB CONTENT 1: PENGHUNI LUNAS --}}
    <div id="wifi-tab-content-lunas">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.9rem; flex-wrap: wrap; gap: 0.5rem;">
            <p style="margin: 0; font-size: 0.84rem; color: #94a3b8;">
                Daftar penghuni yang telah melunasi iuran dan berhak menerima password WiFi:
            </p>
            <div style="display: flex; gap: 0.4rem;">
                <button type="button" onclick="copyAllLunasPhones()" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.15); color: #f8fafc; font-weight: 600;" title="Salin semua nomor WhatsApp penghuni lunas untuk Broadcast List">
                    📋 Salin Daftar No. WA
                </button>
                <button type="button" onclick="copyBroadcastText()" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; font-weight: 600;" title="Salin teks pesan broadcast ke clipboard">
                    📢 Salin Pesan Broadcast
                </button>
            </div>
        </div>

        <div id="wifi-lunas-container" style="max-height: 45vh; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.25rem;">
            {{-- Dynamically populated --}}
            <div style="text-align: center; padding: 2rem; color: #94a3b8;">Memuat data penghuni lunas...</div>
        </div>
    </div>

    {{-- TAB CONTENT 2: PENGHUNI BELUM LUNAS --}}
    <div id="wifi-tab-content-unpaid" style="display: none;">
        <div style="margin-bottom: 0.9rem;">
            <p style="margin: 0; font-size: 0.84rem; color: #fca5a5;">
                Daftar penghuni yang belum melunasi iuran. Kirim pengingat tagihan agar iuran segera dilunasi:
            </p>
        </div>

        <div id="wifi-unpaid-container" style="max-height: 45vh; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.25rem;">
            {{-- Dynamically populated --}}
            <div style="text-align: center; padding: 2rem; color: #94a3b8;">Memuat data tagihan...</div>
        </div>
    </div>

    {{-- TAB CONTENT 3: PENGATURAN WIFI & TEMPLATE PESAN --}}
    <div id="wifi-tab-content-settings" style="display: none;">
        <form action="{{ route('asrama.wifi.config.save') }}" method="POST">
            @csrf
            <input type="hidden" id="setting-form-bulan" name="bulan" value="{{ date('n') }}">
            <input type="hidden" id="setting-form-tahun" name="tahun" value="{{ $tahun }}">

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Nama WiFi (SSID) <span class="required">*</span></label>
                    <input type="text" id="setting-wifi-ssid" name="ssid" class="form-control" placeholder="cth: Asrama-Mahulu-Lt1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password WiFi Bulan Ini <span class="required">*</span></label>
                    <input type="text" id="setting-wifi-password" name="password" class="form-control" placeholder="cth: MahuluSep2026!" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <input type="text" id="setting-wifi-catatan" name="catatan" class="form-control" placeholder="cth: Password berlaku s.d akhir bulan">
            </div>

            <div class="form-group">
                <label class="form-label">Template Pesan WhatsApp: Penghuni Lunas</label>
                <textarea id="setting-template-lunas" name="template_lunas" class="form-control" rows="3" style="font-size: 0.85rem; font-family: monospace;"></textarea>
                <span style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px; display: block;">Gunakan variabel otomatis: <code>[NAMA]</code>, <code>[BULAN_TAHUN]</code>, <code>[SSID]</code>, <code>[PASSWORD]</code></span>
            </div>

            <div class="form-group">
                <label class="form-label">Template Pesan WhatsApp: Pengingat Tagihan Belum Lunas</label>
                <textarea id="setting-template-tagihan" name="template_tagihan" class="form-control" rows="3" style="font-size: 0.85rem; font-family: monospace;"></textarea>
                <span style="font-size: 0.72rem; color: #94a3b8; margin-top: 2px; display: block;">Gunakan variabel otomatis: <code>[NAMA]</code>, <code>[BULAN_TAHUN]</code>, <code>[TAGIHAN]</code>, <code>[SSID]</code></span>
            </div>

            <div class="form-actions" style="margin-top: 1.25rem;">
                <button type="button" onclick="closeWifiDistributionModal()" class="btn btn-secondary">Tutup</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Pengaturan WiFi</button>
            </div>
        </form>
    </div>
</div>
<div id="modal-wifi-distribusi-overlay" class="modal-overlay" onclick="closeWifiDistributionModal()"></div>

@endsection

@push('scripts')
<script>
    let currentWifiDistribution = null;

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

    function setNominal(val) {
        document.getElementById('cell-nominal-formatted').value = formatNumberWithDots(val);
        document.getElementById('cell-nominal-raw').value = val;
        document.getElementById('cell-status-lunas').checked = val > 0;
    }

    function openCellModal(penghuniId, fasilitasKey, itemName, bulanNum, bulanName, currentNominal, isLunas, proratedFee = 0, sisaHari = 0) {
        document.getElementById('cell-penghuni-id').value = penghuniId || '';
        document.getElementById('cell-fasilitas-key').value = fasilitasKey || '';
        document.getElementById('cell-bulan').value = bulanNum;
        document.getElementById('cell-item-name').textContent = itemName;
        document.getElementById('cell-month-name').textContent = bulanName;

        let nom = currentNominal || 0;
        document.getElementById('cell-nominal-formatted').value = formatNumberWithDots(nom);
        document.getElementById('cell-nominal-raw').value = nom;
        document.getElementById('cell-status-lunas').checked = isLunas == 1;

        let btnProrata = document.getElementById('btn-prorata-preset');
        if (proratedFee > 0 && !fasilitasKey) {
            btnProrata.style.display = 'inline-block';
            btnProrata.textContent = '⚡ Prorata (' + sisaHari + ' Hari: Rp ' + formatNumberWithDots(proratedFee) + ')';
            btnProrata.onclick = function() {
                setNominal(proratedFee);
            };
        } else {
            if (btnProrata) btnProrata.style.display = 'none';
        }

        if (fasilitasKey) {
            document.getElementById('wrapper-nominal').style.display = 'none';
        } else {
            document.getElementById('wrapper-nominal').style.display = 'block';
        }

        const m = document.getElementById('modal-matriks-cell');
        const o = document.getElementById('modal-matriks-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closeCellModal() {
        const m = document.getElementById('modal-matriks-cell');
        const o = document.getElementById('modal-matriks-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    function filterMatriksTable() {
        const input = document.getElementById('search-matriks');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('.matriks-row');

        rows.forEach(row => {
            const nameCell = row.querySelector('.col-nama');
            if (nameCell) {
                const text = nameCell.textContent || nameCell.innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // ===== WIFI DISTRIBUTION MODAL FUNCTIONS =====
    function openWifiDistributionModal() {
        const m = document.getElementById('modal-wifi-distribusi');
        const o = document.getElementById('modal-wifi-distribusi-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }

        switchWifiTab('lunas');
        loadWifiDistributionData();
    }

    function closeWifiDistributionModal() {
        const m = document.getElementById('modal-wifi-distribusi');
        const o = document.getElementById('modal-wifi-distribusi-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    function switchWifiTab(tab) {
        const tabLunas = document.getElementById('wifi-tab-content-lunas');
        const tabUnpaid = document.getElementById('wifi-tab-content-unpaid');
        const tabSettings = document.getElementById('wifi-tab-content-settings');

        const btnLunas = document.getElementById('tab-btn-lunas');
        const btnUnpaid = document.getElementById('tab-btn-unpaid');
        const btnSettings = document.getElementById('tab-btn-settings');

        tabLunas.style.display = 'none';
        tabUnpaid.style.display = 'none';
        tabSettings.style.display = 'none';

        btnLunas.style.borderBottomColor = 'transparent';
        btnLunas.style.color = '#94a3b8';
        btnUnpaid.style.borderBottomColor = 'transparent';
        btnUnpaid.style.color = '#94a3b8';
        btnSettings.style.borderBottomColor = 'transparent';
        btnSettings.style.color = '#94a3b8';

        if (tab === 'lunas') {
            tabLunas.style.display = 'block';
            btnLunas.style.borderBottomColor = '#10b981';
            btnLunas.style.color = '#6ee7b7';
        } else if (tab === 'unpaid') {
            tabUnpaid.style.display = 'block';
            btnUnpaid.style.borderBottomColor = '#ef4444';
            btnUnpaid.style.color = '#fca5a5';
        } else if (tab === 'settings') {
            tabSettings.style.display = 'block';
            btnSettings.style.borderBottomColor = '#38bdf8';
            btnSettings.style.color = '#38bdf8';
        }
    }

    function loadWifiDistributionData() {
        const bulan = document.getElementById('wifi-select-bulan').value;
        const tahun = document.getElementById('wifi-select-tahun').value;
        const tarifDefault = "{{ $tarifDefault }}";

        const lunasContainer = document.getElementById('wifi-lunas-container');
        const unpaidContainer = document.getElementById('wifi-unpaid-container');

        lunasContainer.innerHTML = '<div style="text-align: center; padding: 2rem; color: #94a3b8;">⏳ Memuat data...</div>';
        unpaidContainer.innerHTML = '<div style="text-align: center; padding: 2rem; color: #94a3b8;">⏳ Memuat data...</div>';

        fetch(`{{ route('asrama.wifi.distribusi') }}?bulan=${bulan}&tahun=${tahun}&tarif_default=${tarifDefault}`)
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    currentWifiDistribution = data;

                    // Update Top Card Display
                    document.getElementById('wifi-display-ssid').textContent = data.config.ssid || '-';
                    document.getElementById('wifi-display-password').textContent = data.config.password || '-';
                    document.getElementById('badge-count-lunas').textContent = data.total_lunas;
                    document.getElementById('badge-count-unpaid').textContent = data.total_unpaid;

                    // Populate Settings Tab Form
                    document.getElementById('setting-form-bulan').value = bulan;
                    document.getElementById('setting-form-tahun').value = tahun;
                    document.getElementById('setting-wifi-ssid').value = data.config.ssid || '';
                    document.getElementById('setting-wifi-password').value = data.config.password || '';
                    document.getElementById('setting-wifi-catatan').value = data.config.catatan || '';
                    document.getElementById('setting-template-lunas').value = data.config.template_lunas || '';
                    document.getElementById('setting-template-tagihan').value = data.config.template_tagihan || '';

                    // Render Lunas List
                    renderLunasList(data.lunas_list);

                    // Render Unpaid List
                    renderUnpaidList(data.unpaid_list);
                } else {
                    lunasContainer.innerHTML = '<div style="color: #ef4444; text-align: center; padding: 1.5rem;">Gagal memuat data WiFi.</div>';
                    unpaidContainer.innerHTML = '<div style="color: #ef4444; text-align: center; padding: 1.5rem;">Gagal memuat data tagihan.</div>';
                }
            })
            .catch(err => {
                lunasContainer.innerHTML = '<div style="color: #ef4444; text-align: center; padding: 1.5rem;">Terjadi kesalahan: ' + err.message + '</div>';
            });
    }

    function renderLunasList(list) {
        const container = document.getElementById('wifi-lunas-container');
        if (!list || list.length === 0) {
            container.innerHTML = '<div style="text-align: center; padding: 2rem; color: #94a3b8; background: rgba(255,255,255,0.02); border-radius: 8px;">Belum ada penghuni yang melunasi iuran pada bulan ini.</div>';
            return;
        }

        let html = '';
        list.forEach((item, index) => {
            html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 10px; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10b981; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; justify-content: center;">
                        ${index + 1}
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #f8fafc; font-size: 0.92rem;">${item.nama}</div>
                        <div style="font-size: 0.75rem; color: #94a3b8; display: flex; gap: 0.6rem; align-items: center; margin-top: 1px;">
                            <span>🏢 Kamar ${item.kamar}</span>
                            <span>•</span>
                            <span>📱 ${item.nomor_hp}</span>
                            <span>•</span>
                            <span style="color: #6ee7b7; font-weight: 600;">Lunas (Rp ${formatNumberWithDots(item.nominal_bayar)})</span>
                        </div>
                    </div>
                </div>

                <div>
                    ${item.wa_url ? `
                    <a href="${item.wa_url}" target="_blank" class="btn btn-sm" style="background: #22c55e; color: #000; font-weight: 700; font-size: 0.78rem; padding: 0.35rem 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none;" title="Buka WhatsApp untuk kirim Password WiFi">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span>Kirim Password</span>
                    </a>
                    ` : `
                    <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">No. HP kosong</span>
                    `}
                </div>
            </div>
            `;
        });
        container.innerHTML = html;
    }

    function renderUnpaidList(list) {
        const container = document.getElementById('wifi-unpaid-container');
        if (!list || list.length === 0) {
            container.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6ee7b7; background: rgba(16,185,129,0.06); border-radius: 8px; font-weight: 600;">🎉 Hebat! Seluruh penghuni aktif telah melunasi iuran pada bulan ini!</div>';
            return;
        }

        let html = '';
        list.forEach((item, index) => {
            html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 10px; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; justify-content: center;">
                        ${index + 1}
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #f8fafc; font-size: 0.92rem;">${item.nama}</div>
                        <div style="font-size: 0.75rem; color: #94a3b8; display: flex; gap: 0.6rem; align-items: center; margin-top: 1px;">
                            <span>🏢 Kamar ${item.kamar}</span>
                            <span>•</span>
                            <span>📱 ${item.nomor_hp}</span>
                            <span>•</span>
                            <span style="color: #f87171; font-weight: 700;">Tunggakan: Rp ${formatNumberWithDots(item.sisa_tagihan)}</span>
                        </div>
                    </div>
                </div>

                <div>
                    ${item.wa_url ? `
                    <a href="${item.wa_url}" target="_blank" class="btn btn-sm" style="background: #f59e0b; color: #000; font-weight: 700; font-size: 0.78rem; padding: 0.35rem 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none;" title="Buka WhatsApp untuk kirim pengingat tagihan">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span>Kirim Tagihan</span>
                    </a>
                    ` : `
                    <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">No. HP kosong</span>
                    `}
                </div>
            </div>
            `;
        });
        container.innerHTML = html;
    }

    function copyWifiPassword() {
        const pass = document.getElementById('wifi-display-password').textContent;
        if (!pass || pass === '-') {
            showToast('Password belum diatur', 'error');
            return;
        }
        navigator.clipboard.writeText(pass).then(() => {
            showToast('Password WiFi berhasil disalin ke clipboard!', 'success');
        });
    }

    function copyAllLunasPhones() {
        if (!currentWifiDistribution || !currentWifiDistribution.all_lunas_phones_string) {
            showToast('Tidak ada nomor telepon penghuni lunas untuk disalin', 'error');
            return;
        }
        navigator.clipboard.writeText(currentWifiDistribution.all_lunas_phones_string).then(() => {
            showToast('Daftar no. WhatsApp penghuni lunas berhasil disalin ke clipboard!', 'success');
        });
    }

    function copyBroadcastText() {
        if (!currentWifiDistribution || !currentWifiDistribution.broadcast_text) {
            showToast('Teks broadcast belum tersedia', 'error');
            return;
        }
        navigator.clipboard.writeText(currentWifiDistribution.broadcast_text).then(() => {
            showToast('Teks pesan broadcast WiFi berhasil disalin ke clipboard!', 'success');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Matriks ready
    });
</script>
@endpush