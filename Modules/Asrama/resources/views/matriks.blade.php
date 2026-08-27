@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
<style>
    .matriks-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .matriks-stat-card {
        background: var(--bg-card-2, rgba(30, 41, 59, 0.6));
        border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.08));
        border-radius: var(--radius-lg, 12px);
        padding: 1.15rem 1.25rem;
        backdrop-filter: blur(8px);
    }

    .matriks-table-wrapper {
        overflow-x: auto;
        border-radius: var(--radius-lg, 12px);
        border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.08));
        background: #0f172a;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
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
        border: 1px solid rgba(255, 255, 255, 0.07);
        text-align: center;
        vertical-align: middle;
    }

    .matriks-table th {
        background: #1e293b;
        color: #f8fafc;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.75rem;
    }

    .matriks-table th.col-current-month {
        background: #334155;
        border-bottom: 2px solid #f59e0b;
        color: #fde047;
    }

    .matriks-table th.col-nama,
    .matriks-table td.col-nama {
        text-align: left;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: #1e293b;
        z-index: 2;
        min-width: 190px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3);
    }

    .matriks-table td.col-nama {
        background: #0f172a;
    }

    .cell-paid {
        background: #15803d !important;
        color: #ffffff !important;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.15s ease, filter 0.15s ease;
    }

    .cell-paid:hover {
        filter: brightness(1.2);
        transform: scale(1.02);
    }

    .cell-empty {
        background: #0f172a;
        color: #64748b;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .cell-empty:hover {
        background: #1e293b;
        color: #cbd5e1;
    }

    .cell-not-joined {
        background: #1e293b !important;
        color: #94a3b8 !important;
        font-style: italic;
        font-size: 0.72rem;
    }

    .row-divider {
        background: #334155 !important;
        color: #cbd5e1;
        font-weight: 700;
        font-style: italic;
        text-align: left !important;
        letter-spacing: 1px;
        padding-left: 1rem !important;
    }

    .preset-btn {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #f8fafc;
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
</style>
@endpush

@section('topbar')
<a href="{{ route('asrama.data') }}" class="btn btn-secondary">Data Asrama</a>
<a href="{{ route('asrama.keuangan') }}" class="btn btn-secondary active">Keuangan</a>
@endsection

@section('content')

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <h2 class="title" style="margin: 0;">Keuangan Asrama</h2>
    <div class="sub-nav-tabs" style="display: flex; gap: 0.5rem;">
        <a href="{{ route('asrama.keuangan') }}" class="btn btn-sm {{ request()->routeIs('asrama.keuangan') ? 'btn-primary' : 'btn-secondary' }}">
            📊 Riwayat Transaksi Kas
        </a>
        <a href="{{ route('asrama.keuangan.matriks') }}" class="btn btn-sm {{ request()->routeIs('asrama.keuangan.matriks') ? 'btn-primary' : 'btn-secondary' }}">
            📅 Matriks Iuran Bulanan
        </a>
    </div>
</div>

<div class="asrama-wrapper">
    {{-- STATS GRID MATRIKS --}}
    <div class="matriks-stats-grid">
        <div class="matriks-stat-card">
            <p class="task-meta" style="margin: 0;">Total Terbayar Tahun {{ $tahun }}</p>
            <h3 style="color: #6ee7b7; margin: 0.35rem 0 0 0; font-size: 1.4rem; font-weight: 700;">
                Rp {{ number_format($statsMatriks['total_terbayar'], 0, ',', '.') }}
            </h3>
            <p class="task-meta" style="font-size: 0.75rem; margin-top: 0.2rem;">Total iuran terkumpul</p>
        </div>
        <div class="matriks-stat-card">
            <p class="task-meta" style="margin: 0;">Lunas Bulan Ini ({{ \Carbon\Carbon::now()->format('F') }})</p>
            <h3 style="color: #fde047; margin: 0.35rem 0 0 0; font-size: 1.4rem; font-weight: 700;">
                {{ $statsMatriks['lunas_bulan_ini'] }} / {{ $statsMatriks['total_aktif'] }} Penghuni
            </h3>
            <p class="task-meta" style="font-size: 0.75rem; margin-top: 0.2rem;">Penghuni yang telah bayar</p>
        </div>
        <div class="matriks-stat-card">
            <p class="task-meta" style="margin: 0;">Tarif Standar / Bulan</p>
            <h3 style="color: #6ee7b7; margin: 0.35rem 0 0 0; font-size: 1.4rem; font-weight: 700;">
                Rp {{ number_format($tarifDefault, 0, ',', '.') }}
            </h3>
            <p class="task-meta" style="font-size: 0.75rem; margin-top: 0.2rem;">Konfigurasi iuran bulanan</p>
        </div>
        <div class="matriks-stat-card">
            <p class="task-meta" style="margin: 0;">Penghuni Aktif</p>
            <h3 style="color: #93c5fd; margin: 0.35rem 0 0 0; font-size: 1.4rem; font-weight: 700;">
                {{ $statsMatriks['total_aktif'] }} Orang
            </h3>
            <p class="task-meta" style="font-size: 0.75rem; margin-top: 0.2rem;">Penghuni aktif saat ini</p>
        </div>
    </div>

    {{-- TABLE WIDGET CARD --}}
    <div class="widget-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">📅 Matriks Pembayaran Iuran & Fasilitas (Tahun {{ $tahun }})</h3>
                <p class="task-meta" style="margin: 0.2rem 0 0 0;">Klik sel manapun pada tabel untuk memperbarui status & nominal iuran (Mendukung perhitungan prorata otomatis)</p>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                {{-- QUICK SEARCH INPUT --}}
                <div style="position: relative;">
                    <input type="text" id="search-matriks" class="form-control" placeholder="🔍 Cari nama..." onkeyup="filterMatriksTable()" style="padding: 0.35rem 0.75rem 0.35rem 2rem; font-size: 0.85rem; width: 150px;">
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
                            <span style="font-size: 0.95rem;">{{ $key === 'wifi' ? '📶' : '🧹' }}</span> {{ $label }}
                        </td>
                        @foreach($bulanNames as $bNum => $bName)
                        @php
                        $cell = $iuranMap['fasilitas_' . $key . '_' . $bNum] ?? null;
                        $isLunas = $cell ? $cell->status_lunas : false;
                        @endphp
                        <td class="{{ $isLunas ? 'cell-paid' : 'cell-empty' }}" onclick="openCellModal(null, '{{ $key }}', '{{ $label }}', {{ $bNum }}, '{{ $bName }}', {{ $cell ? $cell->nominal : 0 }}, {{ $isLunas ? 1 : 0 }})" title="Klik untuk ubah {{ $label }} {{ $bName }}">
                            @if($isLunas)
                            <span style="font-size: 1.15rem; color: #fff; font-weight: bold;">☑</span>
                            @else
                            <span style="font-size: 1.15rem; color: #475569;">☐</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach

                    {{-- ACTIVE RESIDENTS ROWS --}}
                    @foreach($penghuniAktif as $p)
                    <tr class="matriks-row">
                        <td class="col-nama">
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 600; color: #f8fafc;">{{ $p->nama }}</span>
                                @if($p->kamar)
                                <span style="font-size: 0.72rem; color: #94a3b8; margin-top: 1px;">Kamar {{ $p->kamar->nomor_kamar }}</span>
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
                            🚪 Keluar (Penghuni Non-Aktif)
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
<div id="modal-matriks-cell" class="modal modal-create" aria-hidden="true">
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
                        ✓ Standar (Rp {{ number_format($tarifDefault, 0, ',', '.') }})
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
                <label for="cell-status-lunas" class="form-label" style="margin: 0; cursor: pointer; font-weight: 600; color: #f8fafc;">Tandai Lunas / Centang ☑</label>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 1.25rem;">
            <button type="button" onclick="closeCellModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Data</button>
        </div>
    </form>
</div>
<div id="modal-matriks-overlay" class="modal-overlay" onclick="closeCellModal()"></div>

@endsection

@push('scripts')
<script>
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
</script>
@endpush