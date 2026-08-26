@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
<style>
    .matriks-table-wrapper {
        overflow-x: auto;
        border-radius: var(--radius-lg, 12px);
        border: 1px solid var(--border-subtle, rgba(255,255,255,0.08));
        background: #0f172a;
    }
    .matriks-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1200px;
        font-size: 0.85rem;
    }
    .matriks-table th, .matriks-table td {
        padding: 0.6rem 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
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
    .matriks-table th.col-nama, .matriks-table td.col-nama {
        text-align: left;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: #1e293b;
        z-index: 2;
        min-width: 150px;
    }
    .matriks-table td.col-nama {
        background: #0f172a;
    }
    .cell-paid {
        background: #15803d !important;
        color: #ffffff !important;
        font-weight: 600;
        cursor: pointer;
        transition: filter 0.2s;
    }
    .cell-paid:hover {
        filter: brightness(1.15);
    }
    .cell-empty {
        background: #0f172a;
        color: #94a3b8;
        cursor: pointer;
    }
    .cell-empty:hover {
        background: #1e293b;
    }
    .cell-not-joined {
        background: #334155 !important;
        color: #cbd5e1 !important;
        font-style: italic;
        font-size: 0.75rem;
    }
    .row-divider {
        background: #334155 !important;
        color: #f1f5f9;
        font-weight: 700;
        font-style: italic;
        text-align: left !important;
        letter-spacing: 1px;
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
    <div class="widget-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">📅 Matriks Pembayaran Iuran & Fasilitas (Tahun {{ $tahun }})</h3>
                <p class="task-meta" style="margin: 0.2rem 0 0 0;">Klik pada sel bulan untuk memperbarui pembayaran iuran</p>
            </div>
            <form action="{{ route('asrama.keuangan.matriks') }}" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
                <label class="form-label" style="margin: 0; white-space: nowrap;">Pilih Tahun:</label>
                <select name="tahun" class="form-control" style="width: auto; padding: 0.35rem 0.75rem;" onchange="this.form.submit()">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="matriks-table-wrapper">
            <table class="matriks-table">
                <thead>
                    <tr>
                        <th class="col-nama">Nama</th>
                        @foreach($bulanNames as $blnNum => $blnName)
                            <th>{{ $blnName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- FIXED EXPENSES ROWS (WIFI & Iuran Sampah) --}}
                    @foreach(['wifi' => 'WIFI', 'sampah' => 'Iuran Sampah'] as $key => $label)
                    <tr>
                        <td class="col-nama">{{ $label }}</td>
                        @foreach($bulanNames as $bNum => $bName)
                            @php
                                $cell = $iuranMap['fasilitas_' . $key . '_' . $bNum] ?? null;
                                $isLunas = $cell ? $cell->status_lunas : false;
                            @endphp
                            <td class="{{ $isLunas ? 'cell-paid' : 'cell-empty' }}" onclick="openCellModal(null, '{{ $key }}', '{{ $label }}', {{ $bNum }}, '{{ $bName }}', {{ $cell ? $cell->nominal : 0 }}, {{ $isLunas ? 1 : 0 }})">
                                @if($isLunas)
                                    <span style="font-size: 1.1rem; color: #fff;">☑</span>
                                @else
                                    <span style="font-size: 1.1rem; color: #64748b;">☐</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach

                    {{-- ACTIVE RESIDENTS ROWS --}}
                    @foreach($penghuniAktif as $p)
                    <tr>
                        <td class="col-nama">{{ $p->nama }}</td>
                        @foreach($bulanNames as $bNum => $bName)
                            @php
                                $cell = $iuranMap['penghuni_' . $p->id . '_' . $bNum] ?? null;
                                $nominal = $cell ? $cell->nominal : 0;
                                
                                // Check if month is prior to joining date
                                $isPriorToJoin = false;
                                if ($p->tanggal_masuk) {
                                    $joinYear = (int)\Carbon\Carbon::parse($p->tanggal_masuk)->format('Y');
                                    $joinMonth = (int)\Carbon\Carbon::parse($p->tanggal_masuk)->format('m');
                                    if ($tahun < $joinYear || ($tahun == $joinYear && $bNum < $joinMonth)) {
                                        $isPriorToJoin = true;
                                    }
                                }
                            @endphp

                            @if($isPriorToJoin && $nominal == 0)
                                <td class="cell-not-joined" title="Masuk: {{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d/m/Y') }}">
                                    masuk : {{ \Carbon\Carbon::parse($p->tanggal_masuk)->format('d/m/Y') }}
                                </td>
                            @else
                                <td class="{{ $nominal > 0 ? 'cell-paid' : 'cell-empty' }}" onclick="openCellModal({{ $p->id }}, null, '{{ addslashes($p->nama) }}', {{ $bNum }}, '{{ $bName }}', {{ $nominal }}, {{ $nominal > 0 ? 1 : 0 }})">
                                    Rp {{ number_format($nominal, 0, ',', '.') }}
                                </td>
                            @endif
                        @endforeach
                    </tr>
                    @endforeach

                    {{-- KELUAR DIVIDER ROW --}}
                    <tr>
                        <td colspan="13" class="row-divider">
                            Keluar
                        </td>
                    </tr>

                    {{-- FORMER RESIDENTS ROWS --}}
                    @foreach($penghuniKeluar as $p)
                    <tr>
                        <td class="col-nama" style="color: #94a3b8;">{{ $p->nama }}</td>
                        @foreach($bulanNames as $bNum => $bName)
                            @php
                                $cell = $iuranMap['penghuni_' . $p->id . '_' . $bNum] ?? null;
                                $nominal = $cell ? $cell->nominal : 0;
                            @endphp
                            <td class="{{ $nominal > 0 ? 'cell-paid' : 'cell-empty' }}" onclick="openCellModal({{ $p->id }}, null, '{{ addslashes($p->nama) }}', {{ $bNum }}, '{{ $bName }}', {{ $nominal }}, {{ $nominal > 0 ? 1 : 0 }})">
                                Rp {{ number_format($nominal, 0, ',', '.') }}
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
        <h3 id="cell-modal-title">Update Pembayaran Iuran</h3>
        <button onclick="closeCellModal()" class="modal-close">&times;</button>
    </div>
    <form action="{{ route('asrama.keuangan.matriks.update') }}" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <input type="hidden" id="cell-bulan" name="bulan" value="">
        <input type="hidden" id="cell-penghuni-id" name="penghuni_id" value="">
        <input type="hidden" id="cell-fasilitas-key" name="fasilitas_key" value="">

        <div style="padding: 0.5rem 0;">
            <p class="task-meta" style="margin-bottom: 1rem; color: var(--text-primary);">
                Item: <strong id="cell-item-name" style="color: #6ee7b7;">-</strong> | Bulan: <strong id="cell-month-name" style="color: #fde047;">-</strong> {{ $tahun }}
            </p>

            <div id="wrapper-nominal" class="form-group">
                <label class="form-label">Nominal Pembayaran (Rp)</label>
                <input type="number" id="cell-nominal" name="nominal" class="form-control" placeholder="100000" min="0">
            </div>

            <div id="wrapper-status" class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
                <input type="checkbox" id="cell-status-lunas" name="status_lunas" value="1" style="width: 18px; height: 18px;">
                <label for="cell-status-lunas" class="form-label" style="margin: 0; cursor: pointer;">Tandai Lunas / Centang ☑</label>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 1.25rem;">
            <button type="button" onclick="closeCellModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Data</button>
        </div>
    </form>
</div>
<div id="modal-matriks-overlay" class="modal-overlay" onclick="closeCellModal()"></div>

@endsection

@push('scripts')
<script>
    function openCellModal(penghuniId, fasilitasKey, itemName, bulanNum, bulanName, currentNominal, isLunas) {
        document.getElementById('cell-penghuni-id').value = penghuniId || '';
        document.getElementById('cell-fasilitas-key').value = fasilitasKey || '';
        document.getElementById('cell-bulan').value = bulanNum;
        document.getElementById('cell-item-name').textContent = itemName;
        document.getElementById('cell-month-name').textContent = bulanName;
        document.getElementById('cell-nominal').value = currentNominal || 0;
        document.getElementById('cell-status-lunas').checked = isLunas == 1;

        if (fasilitasKey) {
            document.getElementById('wrapper-nominal').style.display = 'none';
        } else {
            document.getElementById('wrapper-nominal').style.display = 'block';
        }

        const m = document.getElementById('modal-matriks-cell');
        const o = document.getElementById('modal-matriks-overlay');
        if (m) { m.classList.add('show'); m.style.display = 'block'; }
        if (o) { o.classList.add('show'); o.style.display = 'block'; }
    }

    function closeCellModal() {
        const m = document.getElementById('modal-matriks-cell');
        const o = document.getElementById('modal-matriks-overlay');
        if (m) { m.classList.remove('show'); m.style.display = 'none'; }
        if (o) { o.classList.remove('show'); o.style.display = 'none'; }
    }
</script>
@endpush