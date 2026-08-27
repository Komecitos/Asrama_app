@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/asrama.css') }}">
@endpush

@section('topbar')
<a href="{{ route('asrama.data') }}" class="btn btn-secondary active">Data Asrama</a>
<a href="{{ route('asrama.keuangan') }}" class="btn btn-secondary">Keuangan</a>
@endsection

@section('content')

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <h2 class="title" style="margin: 0;">Data Asrama</h2>

    {{-- TOGGLE SHOW/HIDE AKSI COLUMN BUTTON --}}
    <div style="display: flex; align-items: center; background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.12); padding: 0.35rem 0.85rem; border-radius: 30px; gap: 0.6rem;">
        <span style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #94a3b8;"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            Tombol Aksi:
        </span>
        <button type="button" id="btn-toggle-aksi" onclick="toggleAksiColumn()" style="display: flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.85rem; border-radius: 20px; font-weight: 800; font-size: 0.8rem; border: none; cursor: pointer; transition: all 0.2s ease;">
            <span id="aksi-status-dot" style="width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
            <span id="aksi-status-text">OFF</span>
        </button>
    </div>
</div>

<div class="asrama-wrapper">
    {{-- STATS GRID --}}
    <div class="asrama-stats-grid">
        <div class="asrama-stat-card">
            <p class="task-meta">Total Kamar</p>
            <h3 style="color: var(--text-primary); margin: 0.25rem 0 0 0; font-size: 1.8rem;">{{ $summary['total_kamar'] }} Kamar</h3>
        </div>
        <div class="asrama-stat-card">
            <p class="task-meta">Kamar Ada Slot / Kosong</p>
            <h3 style="color: #38bdf8; margin: 0.25rem 0 0 0; font-size: 1.8rem;">{{ $summary['kamar_tersedia'] }} Unit</h3>
        </div>
        <div class="asrama-stat-card">
            <p class="task-meta">Total Penghuni Aktif</p>
            <h3 style="color: #fde047; margin: 0.25rem 0 0 0; font-size: 1.8rem;">{{ $summary['total_penghuni'] }} Orang</h3>
        </div>
    </div>

    {{-- PENGHUNI SECTION --}}
    <div class="widget-card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">Data Penghuni Asrama</h3>
            </div>
            <button type="button" onclick="openPenghuniModal()" class="btn btn-primary btn-sm">+ Tambah Penghuni</button>
        </div>

        @php
        $penghuniAktifList = $penghunis->where('status_penghuni', 'Aktif');
        @endphp

        @if($penghuniAktifList->isEmpty())
        <p class="empty-state">Belum ada data penghuni aktif. Klik <strong>+ Tambah Penghuni</strong> untuk mencatat penghuni baru.</p>
        @else
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Penghuni</th>
                        <th>No. Telepon / HP</th>
                        <th>Kampus</th>
                        <th>Asal Kampung</th>
                        <th>Kamar</th>
                        <th>Status</th>
                        <th>Tgl Masuk</th>
                        <th class="col-aksi-header">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penghuniAktifList as $penghuni)
                    <tr>
                        <td class="task-title" style="font-weight: 600;">{{ $penghuni->nama }}</td>
                        <td class="task-meta">{{ $penghuni->nomor_hp ?: '-' }}</td>
                        <td>{{ $penghuni->kampus ?: '-' }}</td>
                        <td class="task-meta">{{ $penghuni->asal_kampung ?: '-' }}</td>
                        <td>
                            @if($penghuni->kamar)
                            <span class="badge badge-info">{{ $penghuni->kamar->nomor_kamar }}</span>
                            @else
                            <span class="task-meta">Belum Ada Kamar</span>
                            @endif
                        </td>
                        <td>
                            @if($penghuni->status_penghuni === 'Aktif')
                            <span class="badge badge-success">Aktif</span>
                            @else
                            <span class="badge badge-secondary">Keluar</span>
                            @endif
                        </td>
                        <td class="task-meta">{{ $penghuni->tanggal_masuk ? \Carbon\Carbon::parse($penghuni->tanggal_masuk)->format('d M Y') : '-' }}</td>
                        <td class="col-aksi-cell">
                            <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                {{-- EDIT BUTTON --}}
                                <button type="button" onclick="openEditPenghuniModal({{ $penghuni->id }}, '{{ addslashes($penghuni->nama) }}', '{{ addslashes($penghuni->nomor_hp ?: '') }}', '{{ addslashes($penghuni->kampus ?: '') }}', '{{ addslashes($penghuni->asal_kampung ?: '') }}', '{{ $penghuni->kamar_id ?: '' }}', '{{ $penghuni->tanggal_masuk ?: '' }}', '{{ addslashes($penghuni->catatan ?: '') }}')" class="btn btn-secondary btn-sm" title="Edit data penghuni">Edit</button>

                                {{-- KELUAR BUTTON (FOR ACTIVE RESIDENTS) --}}
                                @if($penghuni->status_penghuni === 'Aktif')
                                <button type="button" onclick="openKeluarPenghuniModal({{ $penghuni->id }}, '{{ addslashes($penghuni->nama) }}')" class="btn btn-warning btn-sm" style="background: #eab308; color: #000; border: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" title="Tandai penghuni keluar asrama">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                                @endif

                                {{-- DELETE BUTTON --}}
                                <button type="button" onclick="openHapusPenghuniModal({{ $penghuni->id }}, '{{ addslashes($penghuni->nama) }}')" class="btn btn-danger btn-sm" title="Hapus penghuni">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- KAMAR SECTION --}}
    <div class="widget-card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">Daftar Kamar Asrama</h3>
            </div>
            <button type="button" onclick="openKamarModal()" class="btn btn-primary btn-sm">+ Tambah Kamar</button>
        </div>

        @if($kamars->isEmpty())
        <p class="empty-state">Belum ada data kamar. Klik <strong>+ Tambah Kamar</strong> untuk membuat kamar baru.</p>
        @else
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nomor Kamar</th>
                        <th>Lantai</th>
                        <th>Penghuni Aktif</th>
                        <th>Status</th>
                        <th>Fasilitas</th>
                        <th class="col-aksi-header">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kamars as $kamar)
                    @php
                    $activeCount = $kamar->penghunis->where('status_penghuni', 'Aktif')->count();
                    @endphp
                    <tr>
                        <td class="task-title" style="font-weight: 700;">{{ $kamar->nomor_kamar }}</td>
                        <td>Lantai {{ $kamar->lantai }}</td>
                        <td>
                            <strong>{{ $activeCount }}</strong> Orang
                        </td>
                        <td>
                            @if($kamar->status === 'Tersedia')
                            <span class="badge badge-success">Tersedia</span>
                            @elseif($kamar->status === 'Penuh')
                            <span class="badge badge-warning">Penuh</span>
                            @elseif($kamar->status === 'Gudang')
                            <span class="badge badge-secondary" style="background: #64748b; color: #fff;">Gudang</span>
                            @else
                            <span class="badge badge-danger">Perbaikan</span>
                            @endif
                        </td>
                        <td class="task-meta">{{ $kamar->fasilitas ?: '-' }}</td>
                        <td class="col-aksi-cell">
                            <div style="display: flex; gap: 0.4rem;">
                                <button type="button" onclick="openEditKamarModal({{ $kamar->id }}, '{{ addslashes($kamar->nomor_kamar) }}', {{ $kamar->lantai }}, '{{ $kamar->status }}', '{{ addslashes($kamar->fasilitas ?: '') }}', '{{ addslashes($kamar->catatan ?: '') }}')" class="btn btn-secondary btn-sm">Edit</button>
                                <form action="{{ route('asrama.kamar.destroy', $kamar->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus kamar {{ $kamar->nomor_kamar }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- LOG AKTIVITAS / PENGHUNI KELUAR SECTION --}}
    <div class="widget-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div>
                <h3 class="widget-title" style="margin: 0;">Log Aktivitas & Penghuni Keluar</h3>
            </div>
        </div>

        @php
        $penghuniKeluarList = $penghunis->where('status_penghuni', 'Keluar')->sortByDesc('tanggal_keluar');
        @endphp

        @if($penghuniKeluarList->isEmpty())
        <p class="empty-state">Belum ada riwayat log penghuni keluar.</p>
        @else
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal Keluar</th>
                        <th>Nama Penghuni</th>
                        <th>No. Telepon / HP</th>
                        <th>Kampus</th>
                        <th>Asal Kampung</th>
                        <th>Kamar Terakhir</th>
                        <th>Catatan Aktivitas Log</th>
                        <th class="col-aksi-header">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penghuniKeluarList as $pk)
                    <tr>
                        <td style="font-weight: 700; color: #cbd5e1;">
                            {{ $pk->tanggal_keluar ? \Carbon\Carbon::parse($pk->tanggal_keluar)->format('d M Y') : '-' }}
                        </td>
                        <td class="task-title" style="font-weight: 600; color: #cbd5e1;">{{ $pk->nama }}</td>
                        <td class="task-meta">{{ $pk->nomor_hp ?: '-' }}</td>
                        <td>{{ $pk->kampus ?: '-' }}</td>
                        <td class="task-meta">{{ $pk->asal_kampung ?: '-' }}</td>
                        <td>
                            @if($pk->kamar)
                            <span class="badge badge-info">{{ $pk->kamar->nomor_kamar }}</span>
                            @else
                            <span class="task-meta">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-warning" style="background: rgba(245, 158, 11, 0.18); color: #fde047; border: 1px solid rgba(245, 158, 11, 0.35); font-weight: 600;">Resmi Keluar Asrama</span>
                        </td>
                        <td class="col-aksi-cell">
                            <div style="display: flex; gap: 0.35rem; align-items: center; flex-wrap: wrap;">
                                {{-- REACTIVATE BUTTON --}}
                                <form action="{{ route('asrama.penghuni.reactivate', $pk->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Aktifkan kembali {{ addslashes($pk->nama) }} sebagai penghuni aktif?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm" style="background: #10b981; color: #fff; border: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;" title="Aktifkan kembali penghuni">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="23 4 23 10 17 10"></polyline>
                                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                                        </svg>
                                        <span>Aktifkan</span>
                                    </button>
                                </form>

                                {{-- EDIT BUTTON --}}
                                <button type="button" onclick="openEditPenghuniModal({{ $pk->id }}, '{{ addslashes($pk->nama) }}', '{{ addslashes($pk->nomor_hp ?: '') }}', '{{ addslashes($pk->kampus ?: '') }}', '{{ addslashes($pk->asal_kampung ?: '') }}', '{{ $pk->kamar_id ?: '' }}', '{{ $pk->tanggal_masuk ?: '' }}', '{{ addslashes($pk->catatan ?: '') }}')" class="btn btn-secondary btn-sm" title="Edit data penghuni">Edit</button>

                                {{-- DELETE BUTTON --}}
                                <button type="button" onclick="openHapusPenghuniModal({{ $pk->id }}, '{{ addslashes($pk->nama) }}')" class="btn btn-danger btn-sm" title="Hapus log & data penghuni">Hapus</button>
                            </div>
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

{{-- MODAL TAMBAH/EDIT KAMAR --}}
<div id="modal-kamar" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3 id="modal-kamar-title">Tambah Kamar Baru</h3>
        <button onclick="closeKamarModal()" class="modal-close">&times;</button>
    </div>
    <form id="form-kamar" action="{{ route('asrama.kamar.store') }}" method="POST" autocomplete="off">
        @csrf
        <div id="method-kamar-field"></div>
        <div class="form-group">
            <label class="form-label">Nomor Kamar <span class="required">*</span></label>
            <input type="text" id="kamar-nomor" name="nomor_kamar" class="form-control" placeholder="cth: Kamar 101, A-02" required>
        </div>
        <div class="form-group">
            <label class="form-label">Pilih Lantai <span class="required">*</span></label>
            <select id="kamar-lantai" name="lantai" class="form-control" required>
                <option value="1">Lantai 1</option>
                <option value="2">Lantai 2</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Status Kamar <span class="required">*</span></label>
            <select id="kamar-status" name="status" class="form-control" required>
                <option value="Tersedia">Tersedia</option>
                <option value="Penuh">Penuh</option>
                <option value="Perbaikan">Perbaikan</option>
                <option value="Gudang">Gudang</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Fasilitas Kamar</label>
            <input type="text" id="kamar-fasilitas" name="fasilitas" class="form-control" placeholder="cth: AC, Kasur, Lemari, WiFi">
        </div>
        <div class="form-group">
            <label class="form-label">Catatan Tambahan</label>
            <textarea id="kamar-catatan" name="catatan" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-actions">
            <button type="button" onclick="closeKamarModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Kamar</button>
        </div>
    </form>
</div>
<div id="modal-kamar-overlay" class="modal-overlay" onclick="closeKamarModal()"></div>

{{-- MODAL TAMBAH/EDIT PENGHUNI --}}
<div id="modal-penghuni" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3 id="modal-penghuni-title">Tambah Penghuni Baru</h3>
        <button onclick="closePenghuniModal()" class="modal-close">&times;</button>
    </div>
    <form id="form-penghuni" action="{{ route('asrama.penghuni.store') }}" method="POST" autocomplete="off">
        @csrf
        <div id="method-penghuni-field"></div>
        <div class="form-group">
            <label class="form-label">Nama Lengkap Penghuni <span class="required">*</span></label>
            <input type="text" id="penghuni-nama" name="nama" class="form-control" placeholder="cth: Ahmad Subagyo" required>
        </div>
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Nomor Telepon / HP</label>
                <input type="text" id="penghuni-hp" name="nomor_hp" class="form-control" placeholder="08123456789">
            </div>
            <div class="form-group">
                <label class="form-label">Pilih Kamar</label>
                <select id="penghuni-kamar" name="kamar_id" class="form-control">
                    <option value="">-- Tanpa Kamar --</option>
                    @foreach($kamars as $k)
                    <option value="{{ $k->id }}">{{ $k->nomor_kamar }} (Lantai {{ $k->lantai }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Kampus</label>
                <input type="text" id="penghuni-kampus" name="kampus" class="form-control" placeholder="cth: Universitas Indonesia">
            </div>
            <div class="form-group">
                <label class="form-label">Asal Kampung / Daerah</label>
                <input type="text" id="penghuni-asal-kampung" name="asal_kampung" class="form-control" placeholder="cth: Bandung, Jawa Barat">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Tanggal Masuk (Opsional)</label>
            <input type="date" id="penghuni-tgl-masuk" name="tanggal_masuk" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">Catatan</label>
            <textarea id="penghuni-catatan" name="catatan" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-actions">
            <button type="button" onclick="closePenghuniModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Penghuni</button>
        </div>
    </form>
</div>
<div id="modal-penghuni-overlay" class="modal-overlay" onclick="closePenghuniModal()"></div>

{{-- MODAL PROSES KELUAR PENGHUNI --}}
<div id="modal-keluar-penghuni" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3 style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #f59e0b;">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Konfirmasi Penghuni Keluar</span>
        </h3>
        <button onclick="closeKeluarPenghuniModal()" class="modal-close">&times;</button>
    </div>
    <form id="form-keluar-penghuni" action="" method="POST" autocomplete="off">
        @csrf
        @method('PATCH')
        <div style="padding: 0.75rem 0;">
            <p class="task-meta" style="margin-bottom: 1.25rem; color: var(--text-primary); font-size: 0.95rem; line-height: 1.5;">
                Tandai penghuni <strong id="keluar-penghuni-nama" style="color: #fde047; font-size: 1.05rem;">-</strong> sebagai <strong>Keluar / Non-Aktif</strong>.<br>
                <span style="font-size: 0.85rem; color: #94a3b8; margin-top: 0.35rem; display: block;">Tempat tidur/slot kamar yang ditempati akan otomatis dibebaskan.</span>
            </p>
            <div class="form-group">
                <label class="form-label">Tanggal Keluar <span class="required">*</span></label>
                <input type="date" name="tanggal_keluar" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>
        <div class="form-actions" style="margin-top: 1.25rem;">
            <button type="button" onclick="closeKeluarPenghuniModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-warning" style="background: #eab308; color: #000; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Konfirmasi Keluar</span>
            </button>
        </div>
    </form>
</div>
<div id="modal-keluar-penghuni-overlay" class="modal-overlay" onclick="closeKeluarPenghuniModal()"></div>

{{-- MODAL HAPUS PENGHUNI --}}
<div id="modal-hapus-penghuni" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3 style="display: flex; align-items: center; gap: 0.5rem; color: #ef4444;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #ef4444;">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
            <span>Hapus Data Penghuni</span>
        </h3>
        <button onclick="closeHapusPenghuniModal()" class="modal-close">&times;</button>
    </div>
    <form id="form-hapus-penghuni" action="" method="POST">
        @csrf
        @method('DELETE')
        <div style="padding: 0.75rem 0;">
            <p class="task-meta" style="margin-bottom: 1rem; color: var(--text-primary); font-size: 0.95rem; line-height: 1.5;">
                Apakah Anda yakin ingin menghapus data penghuni <strong id="hapus-penghuni-nama" style="color: #ef4444; font-size: 1.05rem;">-</strong>?<br>
                <span style="font-size: 0.85rem; color: #94a3b8; margin-top: 0.35rem; display: block;">Tindakan ini permanen dan data penghuni yang dihapus tidak dapat dikembalikan.</span>
            </p>
        </div>
        <div class="form-actions" style="margin-top: 1.25rem;">
            <button type="button" onclick="closeHapusPenghuniModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-danger" style="background: #ef4444; color: #fff; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span>Hapus Permanent</span>
            </button>
        </div>
    </form>
</div>
<div id="modal-hapus-penghuni-overlay" class="modal-overlay" onclick="closeHapusPenghuniModal()"></div>

@endsection

@push('scripts')
<script>
    function openKamarModal() {
        document.getElementById('modal-kamar-title').textContent = 'Tambah Kamar Baru';
        document.getElementById('form-kamar').action = "{{ route('asrama.kamar.store') }}";
        document.getElementById('method-kamar-field').innerHTML = '';
        document.getElementById('kamar-nomor').value = '';
        document.getElementById('kamar-lantai').value = '1';
        document.getElementById('kamar-status').value = 'Tersedia';
        document.getElementById('kamar-fasilitas').value = '';
        document.getElementById('kamar-catatan').value = '';

        const m = document.getElementById('modal-kamar');
        const o = document.getElementById('modal-kamar-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function openEditKamarModal(id, nomor, lantai, status, fasilitas, catatan) {
        document.getElementById('modal-kamar-title').textContent = 'Edit Kamar ' + nomor;
        document.getElementById('form-kamar').action = "/asrama/kamar/" + id;
        document.getElementById('method-kamar-field').innerHTML = '@method("PUT")';
        document.getElementById('kamar-nomor').value = nomor;
        document.getElementById('kamar-lantai').value = lantai;
        document.getElementById('kamar-status').value = status;
        document.getElementById('kamar-fasilitas').value = fasilitas;
        document.getElementById('kamar-catatan').value = catatan;

        const m = document.getElementById('modal-kamar');
        const o = document.getElementById('modal-kamar-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closeKamarModal() {
        const m = document.getElementById('modal-kamar');
        const o = document.getElementById('modal-kamar-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    function openPenghuniModal() {
        document.getElementById('modal-penghuni-title').textContent = 'Tambah Penghuni Baru';
        document.getElementById('form-penghuni').action = "{{ route('asrama.penghuni.store') }}";
        document.getElementById('method-penghuni-field').innerHTML = '';
        document.getElementById('penghuni-nama').value = '';
        document.getElementById('penghuni-hp').value = '';
        document.getElementById('penghuni-kampus').value = '';
        document.getElementById('penghuni-asal-kampung').value = '';
        document.getElementById('penghuni-kamar').value = '';
        document.getElementById('penghuni-tgl-masuk').value = '';
        document.getElementById('penghuni-catatan').value = '';

        const m = document.getElementById('modal-penghuni');
        const o = document.getElementById('modal-penghuni-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function openEditPenghuniModal(id, nama, hp, kampus, asalKampung, kamarId, tglMasuk, catatan) {
        document.getElementById('modal-penghuni-title').textContent = 'Edit Penghuni ' + nama;
        document.getElementById('form-penghuni').action = "/asrama/penghuni/" + id;
        document.getElementById('method-penghuni-field').innerHTML = '@method("PUT")';
        document.getElementById('penghuni-nama').value = nama;
        document.getElementById('penghuni-hp').value = hp;
        document.getElementById('penghuni-kampus').value = kampus;
        document.getElementById('penghuni-asal-kampung').value = asalKampung;
        document.getElementById('penghuni-kamar').value = kamarId;
        document.getElementById('penghuni-tgl-masuk').value = tglMasuk;
        document.getElementById('penghuni-catatan').value = catatan;

        const m = document.getElementById('modal-penghuni');
        const o = document.getElementById('modal-penghuni-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closePenghuniModal() {
        const m = document.getElementById('modal-penghuni');
        const o = document.getElementById('modal-penghuni-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    function openKeluarPenghuniModal(id, nama) {
        document.getElementById('keluar-penghuni-nama').textContent = nama;
        document.getElementById('form-keluar-penghuni').action = "/asrama/penghuni/" + id + "/keluar";

        const m = document.getElementById('modal-keluar-penghuni');
        const o = document.getElementById('modal-keluar-penghuni-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closeKeluarPenghuniModal() {
        const m = document.getElementById('modal-keluar-penghuni');
        const o = document.getElementById('modal-keluar-penghuni-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    function openHapusPenghuniModal(id, nama) {
        document.getElementById('hapus-penghuni-nama').textContent = nama;
        document.getElementById('form-hapus-penghuni').action = "/asrama/penghuni/" + id;

        const m = document.getElementById('modal-hapus-penghuni');
        const o = document.getElementById('modal-hapus-penghuni-overlay');
        if (m) {
            m.classList.add('show');
            m.style.display = 'block';
        }
        if (o) {
            o.classList.add('show');
            o.style.display = 'block';
        }
    }

    function closeHapusPenghuniModal() {
        const m = document.getElementById('modal-hapus-penghuni');
        const o = document.getElementById('modal-hapus-penghuni-overlay');
        if (m) {
            m.classList.remove('show');
            m.style.display = 'none';
        }
        if (o) {
            o.classList.remove('show');
            o.style.display = 'none';
        }
    }

    function updateAksiToggleUI(isOn) {
        const btn = document.getElementById('btn-toggle-aksi');
        const dot = document.getElementById('aksi-status-dot');
        const text = document.getElementById('aksi-status-text');
        const headers = document.querySelectorAll('.col-aksi-header');
        const cells = document.querySelectorAll('.col-aksi-cell');

        if (isOn) {
            if (btn) {
                btn.style.background = '#10b981';
                btn.style.color = '#ffffff';
                btn.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.4)';
            }
            if (dot) dot.style.background = '#6ee7b7';
            if (text) text.innerText = 'ON (Aktif)';
            headers.forEach(el => el.style.display = '');
            cells.forEach(el => el.style.display = '');
        } else {
            if (btn) {
                btn.style.background = '#334155';
                btn.style.color = '#cbd5e1';
                btn.style.boxShadow = 'none';
            }
            if (dot) dot.style.background = '#ef4444';
            if (text) text.innerText = 'OFF (Sembunyi)';
            headers.forEach(el => el.style.display = 'none');
            cells.forEach(el => el.style.display = 'none');
        }
    }

    function toggleAksiColumn() {
        let current = localStorage.getItem('asrama_aksi_toggle') || 'OFF';
        let next = (current === 'ON') ? 'OFF' : 'ON';
        localStorage.setItem('asrama_aksi_toggle', next);
        updateAksiToggleUI(next === 'ON');
    }

    document.addEventListener('DOMContentLoaded', function() {
        let saved = localStorage.getItem('asrama_aksi_toggle') || 'OFF';
        updateAksiToggleUI(saved === 'ON');
    });
</script>
@endpush