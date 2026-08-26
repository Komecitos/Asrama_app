@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/kuliah.css') }}">
@endpush

@section('topbar')
<a href="{{ route('kuliah.jadwal') }}" class="btn btn-secondary">Semester</a>
<a href="{{ route('kuliah.matakuliah') }}" class="btn btn-secondary active">Matakuliah</a>
@endsection

@section('content')

@php
$targets = [
'all' => 144,
'core' => 102,
'elective' => 24,
'supporting' => 12,
];
$progressCards = [
['key' => 'all', 'label' => 'All SKS', 'value' => $summary['all'] ?? 0],
['key' => 'core', 'label' => 'Wajib', 'value' => $summary['core'] ?? 0],
['key' => 'elective', 'label' => 'Pilihan Inti', 'value' => $summary['elective'] ?? 0],
['key' => 'supporting', 'label' => 'Pilihan Pendukung', 'value' => $summary['supporting'] ?? 0],
];
@endphp

{{-- Page Header --}}
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-md); margin-bottom: var(--space-lg);">
    <div>
        <h2 class="title">Daftar Matakuliah</h2>
        <p class="subtitle">{{ $summary['total_courses'] ?? count($matakuliah) }} Mata Kuliah &bull; {{ $summary['total_credits'] ?? 0 }} Total SKS &bull; {{ $summary['all'] ?? 0 }} SKS Lulus</p>
    </div>
    <button onclick="openModal()" class="btn btn-primary">+ Create Course</button>
</div>

{{-- Progress Cards --}}
<div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: var(--space-md); margin-bottom: var(--space-xl);">
    @foreach ($progressCards as $card)
    @php
    $target = $targets[$card['key']];
    $percent = min(100, round(($card['value'] / max(1, $target)) * 100));
    $progressHue = $percent * 1.2;
    @endphp
    <div>
        <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(180deg, hsla({{ $progressHue }}, 70%, 45%, 0.2), var(--bg-card-2)); border-color: hsla({{ $progressHue }}, 70%, 45%, 0.45);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">{{ $card['label'] }}</div>
                    <strong>{{ $card['value'] }} / {{ $target }}</strong>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background-color: hsl({{ $progressHue }}, 70%, 45%);" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted mt-1">{{ $percent }}% target</div>
            </div>
        </div>
    </div>
    @endforeach
    @php
    $dPercent = min(100, round($summary['d_percentage'] ?? 0, 1));
    $dQualityPercent = max(0, min(100, round((1 - ($dPercent / 20)) * 100, 1)));
    $dProgressHue = $dQualityPercent * 1.2;
    @endphp
    <div>
        <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(180deg, hsla({{ $dProgressHue }}, 70%, 45%, 0.2), var(--bg-card-2)); border-color: hsla({{ $dProgressHue }}, 70%, 45%, 0.45);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">Nilai D</div>
                    <strong>{{ $summary['d_credits'] ?? 0 }} SKS</strong>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $dQualityPercent }}%; background-color: hsl({{ $dProgressHue }}, 70%, 45%);" aria-valuenow="{{ $dQualityPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted mt-1">{{ $summary['d_percentage'] ?? 0 }}% dari total SKS</div>
            </div>
        </div>
    </div>
</div>

{{-- Collect all semester numbers from nilai entries + semester_id --}}
@php
$allSemNums = collect();
foreach ($matakuliah as $mk) {
$entries = is_array($mk['nilai']) ? $mk['nilai'] : [];
foreach ($entries as $entry) {
$s = is_array($entry) ? ($entry['semester'] ?? null) : null;
if ($s) $allSemNums->push((int)$s);
}
if (empty($entries) && !empty($mk['semester_id'])) {
$semObj = ($allSemesters ?? collect())->firstWhere('id', $mk['semester_id']);
if ($semObj) $allSemNums->push((int)$semObj->number);
}
}
$allSemNums = $allSemNums->unique()->sort()->values();
@endphp

<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- Toolbar: Search + Category Filter --}}
        <div style="display: flex; gap: var(--space-sm); align-items: flex-end; margin-bottom: var(--space-lg); flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 240px; margin-bottom: 0;">
                <label class="form-label" for="course-search">Cari Mata Kuliah</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="search" id="course-search" class="form-control" autocomplete="off" placeholder="Cari kode atau nama mata kuliah..." style="padding-left: 34px;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="kategori-filter">Filter Kategori</label>
                <select id="kategori-filter" class="form-control" onchange="applyFilters()" style="min-width: 170px;">
                    <option value="">Semua Kategori</option>
                    <option value="Wajib">Wajib</option>
                    <option value="Pilihan Inti">Pilihan Inti</option>
                    <option value="Pilihan Pendukung">Pilihan Pendukung</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="status-filter">Filter Status</label>
                <select id="status-filter" class="form-control" onchange="applyFilters()" style="min-width: 170px;">
                    <option value="">Semua Status</option>
                    <option value="Lulus">Lulus</option>
                    <option value="Lulus (D)">Lulus (D)</option>
                    <option value="Belum Lulus">Belum Lulus</option>
                    <option value="Belum Diambil">Belum Diambil</option>
                </select>
            </div>
        </div>

        {{-- AUTHORITATIVE COURSE TABLE LIST --}}
        <div class="table-wrapper" style="overflow-x: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 140px;">Kategori</th>
                        <th style="width: 100px;">Kode</th>
                        <th>Matakuliah</th>
                        <th style="width: 55px; text-align: center;">SKS</th>
                        <th style="width: 65px; text-align: center;">Nilai 1</th>
                        <th style="width: 65px; text-align: center;">Nilai 2</th>
                        <th style="width: 65px; text-align: center;">Nilai 3</th>
                        <th style="width: 100px; text-align: center;">Nilai Terbaik</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 110px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matakuliah as $mk)
                    @php
                    $gradeEntries = is_array($mk['nilai']) ? $mk['nilai'] : [];
                    $g1 = is_array($gradeEntries[0] ?? null) ? ($gradeEntries[0]['grade'] ?? '') : ($gradeEntries[0] ?? '');
                    $g2 = is_array($gradeEntries[1] ?? null) ? ($gradeEntries[1]['grade'] ?? '') : ($gradeEntries[1] ?? '');
                    $g3 = is_array($gradeEntries[2] ?? null) ? ($gradeEntries[2]['grade'] ?? '') : ($gradeEntries[2] ?? '');

                    // Best grade calculation
                    $gradeOrder = ['A'=>10,'A-'=>9.5,'B+'=>9,'B'=>8,'B-'=>7.5,'C+'=>6.5,'C'=>6,'C-'=>5.5,'D'=>3,'E'=>2,'F'=>1];
                    $bestGrade = '';
                    if (count($gradeEntries)) {
                    $bestEntry = collect($gradeEntries)->sortByDesc(function($e) use ($gradeOrder) {
                    $g = strtoupper(is_array($e) ? ($e['grade'] ?? 'F') : $e);
                    return $gradeOrder[$g] ?? 0;
                    })->first();
                    $bestGrade = is_array($bestEntry) ? ($bestEntry['grade'] ?? '') : $bestEntry;
                    }

                    $statusRaw = trim($mk['status'] ?? '');
                    $statusClass = match(strtolower($statusRaw)) {
                    'lulus', 'passed' => 'badge-success',
                    'lulus (d)' => 'badge-warning',
                    'belum lulus', 'failed' => 'badge-danger',
                    default => 'badge-secondary'
                    };

                    $bestGradeClass = match(strtoupper((string)$bestGrade)) {
                    'A', 'A-', 'B+' => 'badge-success',
                    'B', 'B-' => 'badge-info',
                    'C+', 'C', 'C-' => 'badge-warning',
                    'D', 'E', 'F' => 'badge-danger',
                    default => 'badge-secondary'
                    };

                    $jenisRaw = trim($mk['jenis'] ?? '');
                    $jenisBadgeClass = match(strtolower($jenisRaw)) {
                    'wajib', 'core' => 'badge-info',
                    'pilihan inti', 'elective core' => 'badge-epic',
                    default => 'badge-secondary'
                    };
                    @endphp
                    <tr class="course-row" data-kategori="{{ $jenisRaw }}" data-status="{{ $statusRaw }}" data-search="{{ strtolower($mk['kode'].' '.$mk['nama']) }}">
                        <td><span class="badge {{ $jenisBadgeClass }}">{{ $jenisRaw }}</span></td>
                        <td><span class="badge badge-secondary font-monospace">{{ $mk['kode'] }}</span></td>
                        <td><strong style="color: var(--text-primary); font-size: 0.9rem;">{{ $mk['nama'] }}</strong></td>
                        <td style="text-align: center;"><span class="badge badge-secondary">{{ $mk['sks'] }}</span></td>
                        <td style="text-align: center;">{!! $g1 ? '<span class="badge badge-secondary">'.$g1.'</span>' : '<span class="text-muted">-</span>' !!}</td>
                        <td style="text-align: center;">{!! $g2 ? '<span class="badge badge-secondary">'.$g2.'</span>' : '<span class="text-muted">-</span>' !!}</td>
                        <td style="text-align: center;">{!! $g3 ? '<span class="badge badge-secondary">'.$g3.'</span>' : '<span class="text-muted">-</span>' !!}</td>
                        <td style="text-align: center;">
                            @if($bestGrade && $bestGrade !== 'Belum Diambil')
                            <span class="badge {{ $bestGradeClass }}" style="font-size: 0.85rem; font-weight: 700; min-width: 28px; text-align: center;">{{ $bestGrade }}</span>
                            @else
                            <span class="text-muted" style="font-size: 0.78rem;">-</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $statusClass }}">{{ $statusRaw }}</span></td>
                        <td>
                            <div class="table-actions" style="justify-content: flex-end; white-space: nowrap;">
                                <button type="button" onclick="openEditModal('{{ $mk['kode'] }}')" class="btn btn-sm btn-secondary">Edit</button>
                                <button type="button" onclick="openDeleteModal('{{ $mk['kode'] }}')" class="btn btn-sm btn-danger">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    <tr id="course-empty-state" style="display: none;">
                        <td colspan="10" class="table-empty">Tidak ada mata kuliah yang ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<div id="modal-overlay" class="modal-overlay" data-has-errors="{{ count($errors) > 0 ? 'true' : 'false' }}" data-old-kode="{{ old('kode') }}" onclick="closeAllModals()"></div>

<div id="modal-create" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Tambah Matakuliah</h3>
        <button type="button" onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem;">
        <ul style="margin: 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="create-course-form" action="{{ route('kuliah.course.store') }}" method="POST" autocomplete="off" onsubmit="return validateCourseForm(this)">
        @csrf

        <div class="form-group">
            <label class="form-label">Kode Matakuliah <span class="required">*</span></label>
            <input type="text" name="kode" class="form-control" value="{{ old('kode') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Course Name <span class="required">*</span></label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Status <span class="required">*</span></label>
            <select id="create-status" name="status" class="form-control" onchange="toggleCreateStatus(this)" required>
                <option value="Auto" {{ old('status', 'Auto') === 'Auto' ? 'selected' : '' }}>Automatic (Passed / Failed)</option>
                <option value="Not Taken" {{ old('status') === 'Not Taken' ? 'selected' : '' }}>Not Taken</option>
            </select>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Credits <span class="required">*</span></label>
                <input type="number" name="sks" class="form-control" min="1" max="10" value="{{ old('sks', 1) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Grades <span class="required">*</span></label>
                <select id="create-nilai-0" name="nilai[]" class="form-control">
                    <option value="" disabled {{ old('nilai') ? '' : 'selected' }}>Select grade</option>
                    @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                    <option value="{{ $grade }}" {{ old('nilai') === $grade ? 'selected' : '' }}>{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Semester</label>
            <select id="create-semester-0" name="semester[]" class="form-control">
                <option value="" disabled {{ old('semester') ? '' : 'selected' }}>Select semester</option>
                @foreach (range(1, 14) as $semester)
                <option value="{{ $semester }}" {{ (string) old('semester') === (string) $semester ? 'selected' : '' }}>Semester {{ $semester }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Dosen</label>
            <input type="text" name="dosen" class="form-control" value="{{ old('dosen') }}">
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Hari</label>
                <select name="hari" class="form-control">
                    <option value="">Pilih hari</option>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <option value="{{ $day }}" {{ old('hari') === $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Jam</label>
                <div class="form-grid-2">
                    <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai') }}" aria-label="Jam mulai">
                    <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai') }}" aria-label="Jam selesai">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-check">
                <input type="checkbox" id="repeat-course" onchange="toggleRepeatCourse(this)">
                Course diambil lebih dari satu kali
            </label>
        </div>

        <div id="repeat-course-fields" class="form-recurring" style="display: none;">
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Semester Ulangan</label>
                    <select name="semester[]" class="form-control" disabled>
                        @foreach (range(1, 14) as $semester)
                        <option value="{{ $semester }}">Semester {{ $semester }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nilai Ulangan</label>
                    <select name="nilai[]" class="form-control" disabled>
                        @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" id="repeat-course-second" onchange="toggleSecondRepeat(this)">
                    Ulangan kedua
                </label>
            </div>

            <div id="repeat-course-second-fields" class="form-grid-2" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Semester Ulangan Kedua</label>
                    <select name="semester[]" class="form-control" disabled>
                        @foreach (range(1, 14) as $semester)
                        <option value="{{ $semester }}">Semester {{ $semester }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nilai Ulangan Kedua</label>
                    <select name="nilai[]" class="form-control" disabled>
                        @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Kategori Matakuliah <span class="required">*</span></label>
                <select name="jenis" class="form-control" required>
                    <option value="Wajib" {{ old('jenis') == 'Wajib' ? 'selected' : '' }}>Wajib</option>
                    <option value="Pilihan Inti" {{ old('jenis') == 'Pilihan Inti' ? 'selected' : '' }}>Pilihan Inti</option>
                    <option value="Pilihan Pendukung" {{ old('jenis') == 'Pilihan Pendukung' ? 'selected' : '' }}>Pilihan Pendukung</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Matakuliah</button>
        </div>
    </form>
</div>

<div id="modal-edit" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Edit Matakuliah</h3>
        <button type="button" onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem;">
        <ul style="margin: 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="edit-course-form" method="POST" autocomplete="off" onsubmit="return validateCourseForm(this)">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Kode Matakuliah <span class="required">*</span></label>
            <input id="edit-kode" type="text" name="kode" class="form-control" required>
        </div>

        <div class="form-group">
            <label class="form-label">Nama Matakuliah <span class="required">*</span></label>
            <input id="edit-nama" type="text" name="nama" class="form-control" required>
        </div>

        <div class="form-group">
            <label class="form-label">Status <span class="required">*</span></label>
            <select id="edit-status" name="status" class="form-control" onchange="toggleEditStatus(this)" required>
                <option value="Auto">Otomatis (Lulus / Belum Lulus)</option>
                <option value="Not Taken">Belum Diambil</option>
            </select>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">SKS <span class="required">*</span></label>
                <input id="edit-sks" type="number" name="sks" class="form-control" min="1" max="10" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nilai Utama <span class="required">*</span></label>
                <select id="edit-nilai-0" name="nilai[]" class="form-control">
                    @foreach (['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'E', 'F'] as $grade)
                    <option value="{{ $grade }}">{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Semester</label>
            <select id="edit-semester-0" name="semester[]" class="form-control">
                @foreach (range(1, 14) as $semester)
                <option value="{{ $semester }}">Semester {{ $semester }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Dosen</label>
            <input id="edit-dosen" type="text" name="dosen" class="form-control">
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Hari</label>
                <select id="edit-hari" name="hari" class="form-control">
                    <option value="">Pilih hari</option>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Jam</label>
                <div class="form-grid-2">
                    <input id="edit-jam-mulai" type="time" name="jam_mulai" class="form-control" aria-label="Jam mulai">
                    <input id="edit-jam-selesai" type="time" name="jam_selesai" class="form-control" aria-label="Jam selesai">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-check">
                <input type="checkbox" id="edit-repeat-course" onchange="toggleEditRepeat(this)">
                Mata kuliah diambil lebih dari satu kali (Ulangan / Retake)
            </label>
        </div>

        <div id="edit-repeat-fields" class="form-recurring" style="display: none;">
            @foreach ([1 => 'Ulangan Pertama', 2 => 'Ulangan Kedua'] as $attempt => $label)
            <div id="edit-attempt-{{ $attempt }}" class="form-grid-2" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Semester {{ $label }}</label>
                    <select id="edit-semester-{{ $attempt }}" name="semester[]" class="form-control" disabled>
                        @foreach (range(1, 14) as $semester)
                        <option value="{{ $semester }}">Semester {{ $semester }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nilai {{ $label }}</label>
                    <select id="edit-nilai-{{ $attempt }}" name="nilai[]" class="form-control" disabled>
                        @foreach (['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'E', 'F'] as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if ($attempt === 1)
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" id="edit-repeat-second" onchange="toggleEditSecondRepeat(this)">
                    Ulangan kedua
                </label>
            </div>
            @endif
            @endforeach
        </div>

        <div class="form-group">
            <label class="form-label">Kategori Matakuliah <span class="required">*</span></label>
            <select id="edit-jenis" name="jenis" class="form-control" required>
                <option value="Wajib">Wajib</option>
                <option value="Pilihan Inti">Pilihan Inti</option>
                <option value="Pilihan Pendukung">Pilihan Pendukung</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

<div id="modal-delete" class="modal modal-sm modal-delete" aria-hidden="true">
    <div class="modal-header">
        <h3>Delete Course?</h3>
        <button type="button" onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <p id="delete-course-text" class="task-meta"></p>
    <form id="delete-course-form" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
        </div>
    </form>
</div>

<script>
    const courses = @json($matakuliah);

    function openModal() {
        const modal = document.getElementById('modal-create');
        const overlay = document.getElementById('modal-overlay');
        if (modal && overlay) {
            modal.classList.add('show');
            overlay.classList.add('show');
        }
    }

    function closeAllModals() {
        const modal = document.getElementById('modal-create');
        const editModal = document.getElementById('modal-edit');
        const deleteModal = document.getElementById('modal-delete');
        const overlay = document.getElementById('modal-overlay');
        if (modal) modal.classList.remove('show');
        if (editModal) editModal.classList.remove('show');
        if (deleteModal) deleteModal.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
    }

    function openDeleteModal(code) {
        if (!code) return;
        const course = courses.find(item => String(item.kode).toUpperCase() === String(code).toUpperCase());
        if (!course) return;

        const txt = document.getElementById('delete-course-text');
        if (txt) txt.textContent = `Mata kuliah "${course.nama}" (${course.kode}) akan dihapus secara permanen.`;
        const form = document.getElementById('delete-course-form');
        if (form) form.action = `/kuliah/courses/${encodeURIComponent(course.kode)}`;

        const delModal = document.getElementById('modal-delete');
        const overlay = document.getElementById('modal-overlay');
        if (delModal && overlay) {
            delModal.classList.add('show');
            overlay.classList.add('show');
        }
    }

    function openEditModal(code) {
        if (!code) return;
        const course = courses.find(item => String(item.kode).toUpperCase() === String(code).toUpperCase());
        if (!course) return;

        const entries = Array.isArray(course.nilai) ? course.nilai : [];

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val !== null && val !== undefined ? val : '';
        };

        setVal('edit-kode', course.kode);
        setVal('edit-nama', course.nama);
        setVal('edit-sks', course.sks);
        setVal('edit-jenis', course.jenis || 'Wajib');
        setVal('edit-dosen', course.dosen);
        setVal('edit-hari', course.hari);
        setVal('edit-jam-mulai', course.jam_mulai ? course.jam_mulai.substring(0, 5) : '');
        setVal('edit-jam-selesai', course.jam_selesai ? course.jam_selesai.substring(0, 5) : '');
        setVal('edit-status', (course.status === 'Not Taken' || course.status === 'Belum Diambil') ? 'Not Taken' : 'Auto');

        const form = document.getElementById('edit-course-form');
        if (form) form.action = `/kuliah/courses/${encodeURIComponent(course.kode)}`;

        // Reset attempts
        [0, 1, 2].forEach(idx => {
            setVal(`edit-semester-${idx}`, '1');
            setVal(`edit-nilai-${idx}`, '');
        });

        entries.slice(0, 3).forEach((entry, index) => {
            const valObj = (typeof entry === 'object' && entry !== null) ? entry : {
                semester: '',
                grade: entry
            };
            setVal(`edit-semester-${index}`, valObj.semester || '1');
            setVal(`edit-nilai-${index}`, valObj.grade || '');
        });

        const hasFirstRepeat = entries.length > 1;
        const hasSecondRepeat = entries.length > 2;

        const repCourseCb = document.getElementById('edit-repeat-course');
        const repSecondCb = document.getElementById('edit-repeat-second');
        if (repCourseCb) repCourseCb.checked = hasFirstRepeat;
        if (repSecondCb) repSecondCb.checked = hasSecondRepeat;

        toggleEditStatus(document.getElementById('edit-status'));
        toggleEditRepeat(repCourseCb);
        toggleEditSecondRepeat(repSecondCb);

        const editModal = document.getElementById('modal-edit');
        const overlay = document.getElementById('modal-overlay');
        if (editModal && overlay) {
            editModal.classList.add('show');
            overlay.classList.add('show');
        }
    }

    function toggleEditRepeat(checkbox) {
        if (!checkbox) return;
        const fields = document.getElementById('edit-repeat-fields');
        if (fields) fields.style.display = checkbox.checked ? 'block' : 'none';
        document.querySelectorAll('#edit-attempt-1 select').forEach(select => {
            select.disabled = !checkbox.checked;
        });
        const secondCb = document.getElementById('edit-repeat-second');
        if (!checkbox.checked && secondCb) {
            secondCb.checked = false;
        }
        toggleEditSecondRepeat(secondCb);
    }

    function toggleCreateStatus(select) {
        if (!select) return;
        const taken = select.value === 'Auto';
        const sem0 = document.getElementById('create-semester-0');
        const nil0 = document.getElementById('create-nilai-0');
        const rep = document.getElementById('repeat-course');
        if (sem0) sem0.disabled = !taken;
        if (nil0) nil0.disabled = !taken;
        if (rep) {
            rep.disabled = !taken;
            if (!taken) rep.checked = false;
        }
        toggleRepeatCourse(rep);
    }

    function toggleEditStatus(select) {
        if (!select) return;
        const taken = select.value === 'Auto';
        document.querySelectorAll('#modal-edit select[name="semester[]"], #modal-edit select[name="nilai[]"]').forEach(field => {
            field.disabled = !taken;
        });
        const rep1 = document.getElementById('edit-repeat-course');
        const rep2 = document.getElementById('edit-repeat-second');
        if (rep1) rep1.disabled = !taken;
        if (rep2) rep2.disabled = !taken;
        if (!taken) {
            if (rep1) rep1.checked = false;
            if (rep2) rep2.checked = false;
        }
        toggleEditRepeat(rep1);
    }

    function toggleEditSecondRepeat(checkbox) {
        if (!checkbox) return;
        const fields = document.getElementById('edit-attempt-2');
        if (fields) {
            fields.style.display = checkbox.checked ? 'grid' : 'none';
            fields.querySelectorAll('select').forEach(select => {
                select.disabled = !checkbox.checked;
            });
        }
    }

    function toggleRepeatCourse(checkbox) {
        if (!checkbox) return;
        const fields = document.getElementById('repeat-course-fields');
        if (fields) fields.style.display = checkbox.checked ? 'block' : 'none';
        document.querySelectorAll('#repeat-course-fields > .form-grid-2 select').forEach(select => {
            select.disabled = !checkbox.checked;
        });

        const secondCheckbox = document.getElementById('repeat-course-second');
        if (!checkbox.checked && secondCheckbox) {
            secondCheckbox.checked = false;
        }
        toggleSecondRepeat(secondCheckbox);
    }

    function toggleSecondRepeat(checkbox) {
        if (!checkbox) return;
        const fields = document.getElementById('repeat-course-second-fields');
        if (fields) {
            fields.style.display = checkbox.checked ? 'grid' : 'none';
            fields.querySelectorAll('select').forEach(select => {
                select.disabled = !checkbox.checked;
            });
        }
    }

    function applyFilters() {
        const query = (document.getElementById('course-search')?.value || '').trim().toLowerCase();
        const selectedKat = (document.getElementById('kategori-filter')?.value || '').toLowerCase();
        const selectedStatus = (document.getElementById('status-filter')?.value || '').toLowerCase();

        const rows = document.querySelectorAll('.course-row');
        let visibleRows = 0;

        rows.forEach(row => {
            const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();
            const rowKat = (row.getAttribute('data-kategori') || '').toLowerCase();
            const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();

            const matchesSearch = !query || rowSearch.includes(query);
            const matchesKat = !selectedKat || rowKat === selectedKat;

            let matchesStatus = true;
            if (selectedStatus) {
                if (selectedStatus === 'lulus') {
                    matchesStatus = rowStatus === 'lulus' || rowStatus === 'passed';
                } else {
                    matchesStatus = rowStatus === selectedStatus;
                }
            }

            const isVisible = matchesSearch && matchesKat && matchesStatus;
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleRows++;
        });

        const emptyState = document.getElementById('course-empty-state');
        if (emptyState) emptyState.style.display = visibleRows ? 'none' : '';
    }

    function validateCourseForm(form) {
        const kodeInput = form.querySelector('[name="kode"]');
        const namaInput = form.querySelector('[name="nama"]');
        const sksInput = form.querySelector('[name="sks"]');
        const jenisSelect = form.querySelector('[name="jenis"]');
        const statusSelect = form.querySelector('[name="status"]');

        form.querySelectorAll('.form-control').forEach(el => el.style.borderColor = '');

        if (!kodeInput || !kodeInput.value.trim()) {
            if (kodeInput) {
                kodeInput.style.borderColor = '#ef4444';
                kodeInput.focus();
            }
            if (typeof showToast === 'function') showToast('Kode Matakuliah tidak boleh kosong!', 'error');
            return false;
        }

        if (!namaInput || !namaInput.value.trim()) {
            if (namaInput) {
                namaInput.style.borderColor = '#ef4444';
                namaInput.focus();
            }
            if (typeof showToast === 'function') showToast('Nama Matakuliah tidak boleh kosong!', 'error');
            return false;
        }

        if (!sksInput || !sksInput.value || parseInt(sksInput.value) < 1) {
            if (sksInput) {
                sksInput.style.borderColor = '#ef4444';
                sksInput.focus();
            }
            if (typeof showToast === 'function') showToast('SKS harus berupa angka minimal 1!', 'error');
            return false;
        }

        if (statusSelect && (statusSelect.value === 'Auto' || statusSelect.value === 'Lulus')) {
            const grades = Array.from(form.querySelectorAll('[name="nilai[]"]:not([disabled])')).map(s => s.value).filter(v => v !== '');
            if (grades.length === 0) {
                if (typeof showToast === 'function') showToast('Pilih minimal satu nilai untuk matakuliah yang diambil!', 'error');
                return false;
            }
        }

        if (!jenisSelect || !jenisSelect.value.trim()) {
            if (jenisSelect) {
                jenisSelect.style.borderColor = '#ef4444';
                jenisSelect.focus();
            }
            if (typeof showToast === 'function') showToast('Kategori Matakuliah wajib dipilih!', 'error');
            return false;
        }

        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        closeAllModals();
        toggleCreateStatus(document.getElementById('create-status'));
        document.getElementById('course-search')?.addEventListener('input', applyFilters);

        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '';
            });
            input.addEventListener('change', function() {
                this.style.borderColor = '';
            });
        });

        const overlay = document.getElementById('modal-overlay');
        if (overlay && overlay.dataset.hasErrors === 'true') {
            const firstErrorKey = overlay.dataset.oldKode || '';
            if (firstErrorKey) {
                openEditModal(firstErrorKey);
            } else {
                openModal();
            }
        }
    });
</script>
@endsection