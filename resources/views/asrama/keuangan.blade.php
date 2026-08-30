@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
@endpush



@section('content')

<div class="asrama-wrapper">
    {{-- STATS KEUANGAN GRID --}}
    <div class="asrama-stats-grid">
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.4rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
            </div>
            <p class="task-meta">Total Pemasukan</p>
            <h3 style="color: #6ee7b7; margin: 0.2rem 0 0 0; font-size: 1.45rem; font-weight: 800;">Rp {{ number_format($summary['total_pemasukan'], 0, ',', '.') }}</h3>
        </div>
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fb7185; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.4rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                    <polyline points="17 18 23 18 23 12"></polyline>
                </svg>
            </div>
            <p class="task-meta">Total Pengeluaran</p>
            <h3 style="color: #f87171; margin: 0.2rem 0 0 0; font-size: 1.45rem; font-weight: 800;">Rp {{ number_format($summary['total_pengeluaran'], 0, ',', '.') }}</h3>
        </div>
        <div class="asrama-stat-card">
            <div class="stat-card-icon" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #fbbf24; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.4rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
            </div>
            <p class="task-meta">Saldo Kas Saat Ini</p>
            <h3 style="color: {{ $summary['saldo_kas'] >= 0 ? '#fde047' : '#f87171' }}; margin: 0.2rem 0 0 0; font-size: 1.45rem; font-weight: 800;">
                Rp {{ number_format($summary['saldo_kas'], 0, ',', '.') }}
            </h3>
        </div>
    </div>

    {{-- KEUANGAN SECTION --}}
    <div class="widget-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">Riwayat Transaksi Keuangan</h3>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <button type="button" onclick="openKeuanganModal()" class="btn btn-primary btn-sm" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Catat Transaksi</span>
                </button>
                <input type="text" id="search-keuangan" onkeyup="filterKeuanganTable()" placeholder="Cari transaksi..." class="form-control" style="width: 200px; font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                <select id="filter-tipe" onchange="filterKeuanganTable()" class="form-control" style="width: 130px; font-size: 0.85rem; padding: 0.35rem 0.75rem; cursor: pointer;">
                    <option value="">Semua Tipe</option>
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
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
            </div>
        </div>

        @if($keuangans->isEmpty())
        <p class="empty-state">Belum ada catatan keuangan. Klik <strong>+ Catat Transaksi</strong> di atas untuk menambahkan transaksi baru.</p>
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
                        <th style="min-width: 150px;">Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $avatarColors = ['teal', 'blue', 'purple', 'amber', 'rose', 'emerald'];
                    @endphp
                    @foreach($keuangans as $k)
                    <tr class="keuangan-row" data-tipe="{{ $k->tipe }}">
                        <td class="task-title" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($k->tanggal)->format('d M Y') }}</td>
                        <td>
                            @if($k->tipe === 'pemasukan')
                            <span class="badge badge-tipe-pemasukan">Masuk</span>
                            @else
                            <span class="badge badge-tipe-pengeluaran">Keluar</span>
                            @endif
                        </td>
                        <td><span class="badge-chip">{{ $k->kategori }}</span></td>
                        <td style="font-weight: 700; color: {{ $k->tipe === 'pemasukan' ? '#6ee7b7' : '#f87171' }};">
                            {{ $k->tipe === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($k->nominal, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($k->penghuni)
                            @php
                            $words = explode(' ', trim($k->penghuni->nama));
                            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                            $cClass = 'resident-avatar-' . $avatarColors[$k->penghuni->id % count($avatarColors)];
                            @endphp
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div class="resident-avatar {{ $cClass }}" style="width: 26px; height: 26px; font-size: 0.68rem;">{{ $initials }}</div>
                                <span style="font-weight: 600; color: #f8fafc;">{{ $k->penghuni->nama }}</span>
                            </div>
                            @else
                            <span class="task-meta">-</span>
                            @endif
                        </td>
                        <td style="font-size: 0.78rem; color: #94a3b8; line-height: 1.35; max-width: 220px; word-break: break-word;">
                            {{ $k->keterangan ?: '-' }}
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.35rem; align-items: center;">
                                <button type="button" onclick="openEditKeuanganModal({{ $k->id }}, '{{ $k->tanggal }}', '{{ $k->tipe }}', '{{ addslashes($k->kategori) }}', {{ $k->nominal }}, '{{ $k->penghuni_id ?: '' }}', '{{ addslashes($k->keterangan ?: '') }}')" class="btn btn-secondary btn-sm" title="Edit catatan transaksi">
                                    Edit
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="openDeleteModal('{{ route('asrama.keuangan.destroy', $k->id) }}', '{{ addslashes($k->kategori) }} - Rp {{ number_format($k->nominal, 0, ',', '.') }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p id="empty-search-msg" class="empty-state" style="display: none; margin-top: 1rem;">Tidak ada transaksi yang cocok dengan kata kunci pencarian/filter.</p>
        </div>
        @endif
    </div>
</div>

{{-- MODAL KONFIRMASI HAPUS TRANSAKSI --}}
<div id="modal-delete-keuangan" class="modal modal-sm" aria-hidden="true" onclick="event.stopPropagation()" style="display: none; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 16px; padding: 1.75rem; background: #1e293b; color: #f8fafc; z-index: 10001;">
    <div style="text-align: center; margin-bottom: 1.25rem;">
        <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
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

{{-- MODAL CATAT / EDIT TRANSAKSI KEUANGAN --}}
<div id="modal-keuangan" class="modal modal-create" aria-hidden="true" onclick="event.stopPropagation()">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-keuangan-title">Catat Transaksi Keuangan</h3>
            <button type="button" class="modal-close" onclick="closeKeuanganModal()">&times;</button>
        </div>
        <form id="form-keuangan" action="{{ route('asrama.keuangan.store') }}" method="POST">
            @csrf
            <div id="method-keuangan-field"></div>
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Tipe Transaksi <span class="required">*</span></label>
                    <select name="tipe" id="tx-tipe-select" class="form-control" required onchange="updateKategoriByTipe()">
                        <option value="pemasukan">🟢 Pemasukan (+)</option>
                        <option value="pengeluaran">🔴 Pengeluaran (-)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori <span class="required">*</span></label>
                    <select name="kategori" id="tx-kategori-select" class="form-control" required onchange="togglePenghuniField()">
                        <option value="Iuran Bulanan">Iuran Bulanan</option>
                        <option value="Sumbangan / Donasi">Sumbangan / Donasi</option>
                        <option value="Denda / Uang Jaminan">Denda / Uang Jaminan</option>
                        <option value="Pemasukan Lain-lain">Pemasukan Lain-lain</option>
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
                    <input type="date" name="tanggal" id="tx-tanggal-input" class="form-control" value="{{ date('Y-m-d') }}" required>
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
                <textarea name="keterangan" id="tx-keterangan-input" class="form-control" rows="2" placeholder="Contoh: Iuran bulan Juli, Pembayaran WiFi Biznet, dll."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeKeuanganModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>
<div id="modal-keuangan-overlay" class="modal-overlay" onclick="closeKeuanganModal()"></div>

@endsection

@push('scripts')
<script>
    function formatNumberWithDots(val) {
        val = val.toString().replace(/\D/g, '');
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function filterKeuanganTable() {
        const searchInput = document.getElementById('search-keuangan');
        const filterTipe = document.getElementById('filter-tipe');
        if (!searchInput || !filterTipe) return;

        const keyword = searchInput.value.toLowerCase().trim();
        const selectedTipe = filterTipe.value.toLowerCase();
        const rows = document.querySelectorAll('.keuangan-row');

        let visibleCount = 0;
        rows.forEach(row => {
            const rowTipe = (row.getAttribute('data-tipe') || '').toLowerCase();
            const rowText = (row.textContent || row.innerText).toLowerCase();

            const matchesSearch = !keyword || rowText.includes(keyword);
            const matchesTipe = !selectedTipe || rowTipe === selectedTipe;

            if (matchesSearch && matchesTipe) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptySearchMsg = document.getElementById('empty-search-msg');
        if (emptySearchMsg) {
            emptySearchMsg.style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
        }
    }

    function formatCurrencyInput(elem) {
        let rawVal = elem.value.replace(/\D/g, '');
        elem.value = formatNumberWithDots(rawVal);
        let hiddenInputId = elem.id.replace('-formatted', '-raw');
        let hiddenElem = document.getElementById(hiddenInputId);
        if (hiddenElem) hiddenElem.value = rawVal || 0;
    }

    const kategoriOptions = {
        pemasukan: [{
                value: 'Iuran Bulanan',
                label: 'Iuran Bulanan'
            },
            {
                value: 'Sumbangan / Donasi',
                label: 'Sumbangan / Donasi'
            },
            {
                value: 'Denda / Uang Jaminan',
                label: 'Denda / Uang Jaminan'
            },
            {
                value: 'Pemasukan Lain-lain',
                label: 'Pemasukan Lain-lain'
            }
        ],
        pengeluaran: [{
                value: 'Listrik & Air',
                label: 'Listrik & Air'
            },
            {
                value: 'Pembayaran WiFi',
                label: 'Pembayaran WiFi'
            },
            {
                value: 'Pembayaran Sampah',
                label: 'Pembayaran Sampah'
            },
            {
                value: 'Kebersihan & Keamanan',
                label: 'Kebersihan & Keamanan'
            },
            {
                value: 'Perbaikan & Maintenance',
                label: 'Perbaikan & Maintenance'
            },
            {
                value: 'Pembelian Peralatan',
                label: 'Pembelian Peralatan'
            },
            {
                value: 'Pengeluaran Lain-lain',
                label: 'Pengeluaran Lain-lain'
            }
        ]
    };

    function updateKategoriByTipe(selectedKategori = '') {
        const tipeSelect = document.getElementById('tx-tipe-select');
        const kategoriSelect = document.getElementById('tx-kategori-select');
        if (!tipeSelect || !kategoriSelect) return;

        const tipe = tipeSelect.value || 'pemasukan';
        const options = kategoriOptions[tipe] || [];

        kategoriSelect.innerHTML = '';
        options.forEach(opt => {
            const optionElem = document.createElement('option');
            optionElem.value = opt.value;
            optionElem.textContent = opt.label;
            if (selectedKategori && selectedKategori === opt.value) {
                optionElem.selected = true;
            }
            kategoriSelect.appendChild(optionElem);
        });

        if (selectedKategori && !options.some(o => o.value === selectedKategori)) {
            const customOpt = document.createElement('option');
            customOpt.value = selectedKategori;
            customOpt.textContent = selectedKategori;
            customOpt.selected = true;
            kategoriSelect.appendChild(customOpt);
        }

        togglePenghuniField();
    }

    function togglePenghuniField() {
        const tipeSelect = document.getElementById('tx-tipe-select');
        const kategoriSelect = document.getElementById('tx-kategori-select');
        const groupPenghuni = document.getElementById('form-group-penghuni');
        const penghuniSelect = document.getElementById('tx-penghuni-select');

        if (!kategoriSelect || !groupPenghuni) return;

        const tipe = tipeSelect ? tipeSelect.value : 'pemasukan';
        const val = kategoriSelect.value;
        if (tipe === 'pemasukan' && (val === 'Iuran Bulanan' || val.toLowerCase().includes('iuran'))) {
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
        const title = document.getElementById('modal-keuangan-title');
        const form = document.getElementById('form-keuangan');
        const methodField = document.getElementById('method-keuangan-field');

        if (title) title.textContent = 'Catat Transaksi Keuangan';
        if (form) form.action = "{{ route('asrama.keuangan.store') }}";
        if (methodField) methodField.innerHTML = '';

        if (document.getElementById('tx-tipe-select')) document.getElementById('tx-tipe-select').value = 'pemasukan';
        updateKategoriByTipe('Iuran Bulanan');

        if (document.getElementById('tx-nominal-formatted')) document.getElementById('tx-nominal-formatted').value = '';
        if (document.getElementById('tx-nominal-raw')) document.getElementById('tx-nominal-raw').value = '0';
        if (document.getElementById('tx-tanggal-input')) document.getElementById('tx-tanggal-input').value = '{{ date("Y-m-d") }}';
        if (document.getElementById('tx-penghuni-select')) document.getElementById('tx-penghuni-select').value = '';
        if (document.getElementById('tx-keterangan-input')) document.getElementById('tx-keterangan-input').value = '';

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

    function openEditKeuanganModal(id, tanggal, tipe, kategori, nominal, penghuniId, keterangan) {
        const title = document.getElementById('modal-keuangan-title');
        const form = document.getElementById('form-keuangan');
        const methodField = document.getElementById('method-keuangan-field');

        if (title) title.textContent = 'Edit Catatan Transaksi';
        if (form) form.action = "/asrama/keuangan/" + id;
        if (methodField) methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        if (document.getElementById('tx-tipe-select')) document.getElementById('tx-tipe-select').value = tipe;
        updateKategoriByTipe(kategori);

        if (document.getElementById('tx-nominal-formatted')) document.getElementById('tx-nominal-formatted').value = formatNumberWithDots(nominal);
        if (document.getElementById('tx-nominal-raw')) document.getElementById('tx-nominal-raw').value = nominal;
        if (document.getElementById('tx-tanggal-input')) document.getElementById('tx-tanggal-input').value = tanggal;
        if (document.getElementById('tx-penghuni-select')) document.getElementById('tx-penghuni-select').value = penghuniId;
        if (document.getElementById('tx-keterangan-input')) document.getElementById('tx-keterangan-input').value = keterangan;

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
        updateKategoriByTipe('Iuran Bulanan');
    });
</script>
@endpush