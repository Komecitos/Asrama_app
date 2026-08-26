@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/kuliah.css') }}">
@endpush

@section('topbar')
<a href="{{ route('kuliah.jadwal') }}" class="btn btn-secondary {{ Request::is('kuliah/jadwal*') ? 'active' : '' }}">Semester</a>
<a href="{{ route('kuliah.matakuliah') }}" class="btn btn-secondary {{ Request::is('kuliah/matakuliah*') ? 'active' : '' }}">Matakuliah</a>
@endsection

@section('content')

@php
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
$hours = range(6, 17);
$dayAliases = [
    'senin' => 'Senin',
    'selasa' => 'Selasa',
    'rabu' => 'Rabu',
    'kamis' => 'Kamis',
    'jumat' => 'Jumat',
];

$todayMap = [
    1 => 'Senin',
    2 => 'Selasa',
    3 => 'Rabu',
    4 => 'Kamis',
    5 => 'Jumat',
];
$currentDayName = $todayMap[now()->dayOfWeek] ?? null;

$totalCourses = $courses->count();
$totalCredits = $courses->sum('sks');
$activeDaysCount = $courses->pluck('hari')->filter()->unique()->count();
$todayCoursesCount = $currentDayName ? $courses->filter(fn($c) => strtolower(trim((string)$c->hari)) === strtolower($currentDayName))->count() : 0;
@endphp

{{-- ==========================================================
     PAGE HEADER & STATS SUMMARY
=========================================================== --}}
<div class="page-header jadwal-header-wrapper">
    <div>
        <h2 class="title">Jadwal Perkuliahan</h2>
        <p class="subtitle">Semester {{ $currentSemester }} &bull; Manajemen jadwal dan kelas semester aktif</p>
    </div>
    <div class="jadwal-header-actions">
        <div class="semester-picker-wrapper">
            <select id="semester-picker" class="form-control semester-dropdown" onchange="changeSemester(this.value)" title="Pilih Semester">
                @foreach ($allSemesters ?? [] as $sem)
                <option value="{{ $sem->number }}" {{ $sem->number == $currentSemester ? 'selected' : '' }}>
                    Semester {{ $sem->number }} {{ in_array($sem->number, $hasCoursesSemesters ?? []) ? '●' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn btn-primary" onclick="openSemesterCreateModal()">
            <span style="font-size: 1.1rem; line-height: 1;">+</span> Tambah Matakuliah
        </button>
    </div>
</div>

{{-- STATS CARDS --}}
<div class="jadwal-stats-grid">
    <div class="widget-card stat-card-hover">
        <div class="stat-card-inner">
            <div>
                <span class="stat-label">Total Matakuliah</span>
                <div class="stat-number">{{ $totalCourses }} <span class="stat-unit">MK</span></div>
            </div>
            <div class="stat-icon-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path><path d="M6 6h10"></path><path d="M6 10h10"></path></svg>
            </div>
        </div>
    </div>
    <div class="widget-card stat-card-hover">
        <div class="stat-card-inner">
            <div>
                <span class="stat-label">Beban Kredit</span>
                <div class="stat-number">{{ $totalCredits }} <span class="stat-unit">SKS</span></div>
            </div>
            <div class="stat-icon-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
            </div>
        </div>
    </div>
    <div class="widget-card stat-card-hover">
        <div class="stat-card-inner">
            <div>
                <span class="stat-label">Hari Kuliah Aktif</span>
                <div class="stat-number">{{ $activeDaysCount }} <span class="stat-unit">Hari</span></div>
            </div>
            <div class="stat-icon-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
        </div>
    </div>
    <div class="widget-card stat-card-hover {{ $todayCoursesCount > 0 ? 'stat-card-active' : '' }}">
        <div class="stat-card-inner">
            <div>
                <span class="stat-label">Kuliah Hari Ini ({{ $currentDayName ?: 'Libur' }})</span>
                <div class="stat-number {{ $todayCoursesCount > 0 ? 'text-accent' : '' }}">
                    {{ $todayCoursesCount }} <span class="stat-unit">Kelas</span>
                </div>
            </div>
            <div class="stat-icon-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================================
     TAB NAVIGATION & MAIN CONTAINER
=========================================================== --}}
<div data-tabs data-initial-tab="{{ request('tab', 'jadwal') }}">

    <div class="jadwal-tab-bar">
        <div class="jadwal-tabs-pills" role="tablist" aria-label="Semester {{ $currentSemester }}">
            <button
                type="button"
                class="jadwal-tab-btn is-active"
                role="tab"
                aria-selected="true"
                aria-controls="tab-jadwal"
                data-tab-target="jadwal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Jadwal Mingguan
            </button>
            <button
                type="button"
                class="jadwal-tab-btn"
                role="tab"
                aria-selected="false"
                aria-controls="tab-matakuliah"
                data-tab-target="matakuliah">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                Daftar Matakuliah Semester Ini ({{ $totalCourses }})
            </button>
        </div>

        {{-- Legend Course Types --}}
        <div class="jadwal-legend">
            <span class="legend-item"><span class="legend-dot dot-core"></span> Inti (Core)</span>
            <span class="legend-item"><span class="legend-dot dot-elective"></span> Pilihan Inti</span>
            <span class="legend-item"><span class="legend-dot dot-supporting"></span> Pendukung</span>
        </div>
    </div>

    {{-- ==========================================================
         TAB 1: JADWAL MINGGUAN
    =========================================================== --}}
    <div id="tab-jadwal" role="tabpanel" data-tab-panel="jadwal">
        <div class="card shadow-sm border-0 schedule-main-card">
            
            {{-- Toolbar: View Switcher (Grid vs Agenda) --}}
            <div class="schedule-toolbar">
                <div class="schedule-view-toggle">
                    <button type="button" class="view-btn is-active" id="btn-view-grid" onclick="switchScheduleView('grid')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Grid Timetable
                    </button>
                    <button type="button" class="view-btn" id="btn-view-agenda" onclick="switchScheduleView('agenda')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                        Agenda Harian
                    </button>
                </div>
                <div class="schedule-hint">
                    <span>Klik pada kartu jadwal untuk mengedit ruangan, dosen, atau waktu.</span>
                </div>
            </div>

            {{-- 1. TIMETABLE GRID VIEW --}}
            <div id="schedule-view-grid" class="schedule-view-wrapper">
                <div class="schedule-scroll custom-scrollbar">
                    <table class="schedule-grid-table">
                        <thead>
                            <tr>
                                <th class="schedule-day-header">
                                    <div class="day-header-title">Hari \ Jam</div>
                                </th>
                                @foreach ($hours as $hour)
                                <th class="schedule-hour-header">
                                    <span class="hour-time">{{ sprintf('%02d:00', $hour) }}</span>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($days as $day)
                            @php
                            $isCurrentDay = ($currentDayName === $day);
                            $coursesInThisDay = collect($jadwal)->filter(function ($course) use ($day, $dayAliases) {
                                $courseDay = strtolower(trim((string) data_get($course, 'hari', '')));
                                return ($dayAliases[$courseDay] ?? '') === $day;
                            });
                            $dayTotalSks = $coursesInThisDay->sum('sks');
                            @endphp
                            <tr class="{{ $isCurrentDay ? 'day-row-current' : '' }}">
                                {{-- Sticky Day Header --}}
                                <th class="schedule-day-cell {{ $isCurrentDay ? 'is-today' : '' }}">
                                    <div class="day-cell-content">
                                        <div class="day-name-wrapper">
                                            <span class="day-name">{{ $day }}</span>
                                            @if ($isCurrentDay)
                                            <span class="badge badge-today">Hari Ini</span>
                                            @endif
                                        </div>
                                        <span class="day-meta">{{ $coursesInThisDay->count() }} MK &bull; {{ $dayTotalSks }} SKS</span>
                                    </div>
                                </th>

                                @php $occupiedHours = []; @endphp

                                @foreach ($hours as $hour)
                                @continue(in_array($hour, $occupiedHours, true))

                                @php
                                $coursesAtSlot = collect($jadwal)->filter(function ($course) use ($day, $hour, $dayAliases) {
                                    $courseDay = strtolower(trim((string) data_get($course, 'hari', '')));
                                    $startHour = (int) substr((string) data_get($course, 'jam_mulai', ''), 0, 2);

                                    return ($dayAliases[$courseDay] ?? '') === $day && $startHour === $hour;
                                });

                                $course = $coursesAtSlot->first();
                                
                                if ($course) {
                                    $rawEnd = (string) data_get($course, 'jam_selesai', '');
                                    if (!empty($rawEnd)) {
                                        $endHour = (int) substr($rawEnd, 0, 2);
                                        $endMinute = (int) substr($rawEnd, 3, 2);
                                        $effectiveEnd = $endHour + ($endMinute > 10 ? 1 : 0);
                                        $duration = max(1, min(18, $effectiveEnd) - $hour);
                                    } else {
                                        $duration = max(1, min(4, (int) ($course->sks ?? 2)));
                                    }
                                } else {
                                    $duration = 1;
                                }

                                if ($duration > 1) {
                                    $occupiedHours = array_merge($occupiedHours, range($hour + 1, $hour + $duration - 1));
                                }
                                @endphp

                                <td class="schedule-slot-cell {{ $course ? 'has-course' : 'slot-empty' }}" colspan="{{ $duration }}">
                                    @if ($coursesAtSlot->isNotEmpty())
                                        @foreach ($coursesAtSlot as $item)
                                        @php
                                        $jenis = strtolower((string) data_get($item, 'jenis', 'core'));
                                        $courseTypeClass = match($jenis) {
                                            'core', 'inti' => 'course-type-core',
                                            'elective core', 'pilihan inti', 'elective' => 'course-type-elective',
                                            default => 'course-type-supporting',
                                        };
                                        $jenisBadge = match($jenis) {
                                            'core', 'inti' => 'Inti',
                                            'elective core', 'pilihan inti', 'elective' => 'Pilihan',
                                            default => 'Pendukung',
                                        };
                                        @endphp
                                        <div class="schedule-course-card {{ $courseTypeClass }}" onclick="openScheduleEditModal({{ Js::from($item) }})" title="Klik untuk edit jadwal">
                                            <div class="course-card-top">
                                                <span class="course-code-pill">{{ data_get($item, 'kode', '-') }}</span>
                                                <span class="course-sks-pill">{{ data_get($item, 'sks', 0) }} SKS</span>
                                            </div>
                                            
                                            <div class="course-card-body">
                                                <h4 class="course-title">{{ data_get($item, 'nama', '-') }}</h4>
                                            </div>

                                            <div class="course-card-footer">
                                                @if(data_get($item, 'jam_mulai'))
                                                <span class="course-meta-pill time-pill">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                    {{ substr((string)data_get($item, 'jam_mulai'), 0, 5) }}{{ data_get($item, 'jam_selesai') ? ' - ' . substr((string)data_get($item, 'jam_selesai'), 0, 5) : '' }}
                                                </span>
                                                @endif

                                                @if(data_get($item, 'ruangan'))
                                                <span class="course-meta-pill room-pill">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                                    {{ data_get($item, 'ruangan') }}
                                                </span>
                                                @endif
                                            </div>

                                            @if(data_get($item, 'dosen'))
                                            <div class="course-lecturer-row">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                                <span class="lecturer-name">{{ data_get($item, 'dosen') }}</span>
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="empty-slot-placeholder"></div>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (collect($jadwal)->isEmpty())
                <div class="schedule-empty-state">
                    <div class="empty-state-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <h4>Belum ada jadwal untuk Semester {{ $currentSemester }}</h4>
                    <p>Tambahkan mata kuliah beserta hari dan jam untuk menyusun jadwal perkuliahan Anda.</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openSemesterCreateModal()">+ Tambah Matakuliah</button>
                </div>
                @elseif (collect($jadwal)->every(fn ($course) => empty(data_get($course, 'hari')) || empty(data_get($course, 'jam_mulai'))))
                <div class="schedule-empty-state">
                    <div class="empty-state-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <h4>Hari dan jam kuliah belum diatur</h4>
                    <p>Mata kuliah sudah ada, tetapi belum memiliki hari atau jam pelaksanaan. Klik tombol edit pada daftar mata kuliah untuk mengaturnya.</p>
                </div>
                @endif
            </div>

            {{-- 2. DAILY AGENDA / LIST VIEW (Alternative & Mobile View) --}}
            <div id="schedule-view-agenda" class="schedule-view-wrapper" style="display: none;">
                <div class="agenda-days-grid">
                    @foreach ($days as $day)
                    @php
                    $isCurrentDay = ($currentDayName === $day);
                    $dayCourses = collect($jadwal)->filter(function ($course) use ($day, $dayAliases) {
                        $courseDay = strtolower(trim((string) data_get($course, 'hari', '')));
                        return ($dayAliases[$courseDay] ?? '') === $day;
                    })->sortBy(fn($c) => data_get($c, 'jam_mulai', '99:99'));
                    @endphp
                    <div class="agenda-day-card {{ $isCurrentDay ? 'is-today' : '' }}">
                        <div class="agenda-day-header">
                            <div class="agenda-day-title">
                                <h3>{{ $day }}</h3>
                                @if ($isCurrentDay)
                                <span class="badge badge-today">Hari Ini</span>
                                @endif
                            </div>
                            <span class="agenda-day-count">{{ $dayCourses->count() }} Kelas &bull; {{ $dayCourses->sum('sks') }} SKS</span>
                        </div>

                        <div class="agenda-courses-list">
                            @forelse ($dayCourses as $item)
                            @php
                            $jenis = strtolower((string) data_get($item, 'jenis', 'core'));
                            $courseTypeClass = match($jenis) {
                                'core', 'inti' => 'course-type-core',
                                'elective core', 'pilihan inti', 'elective' => 'course-type-elective',
                                default => 'course-type-supporting',
                            };
                            $jenisLabel = match($jenis) {
                                'core', 'inti' => 'Inti',
                                'elective core', 'pilihan inti', 'elective' => 'Pilihan Inti',
                                default => 'Pendukung',
                            };
                            @endphp
                            <div class="agenda-course-item {{ $courseTypeClass }}">
                                <div class="agenda-item-left">
                                    <div class="agenda-item-time">
                                        <span class="time-start">{{ substr((string)data_get($item, 'jam_mulai', '00:00'), 0, 5) }}</span>
                                        <span class="time-separator">-</span>
                                        <span class="time-end">{{ data_get($item, 'jam_selesai') ? substr((string)data_get($item, 'jam_selesai'), 0, 5) : 'Selesai' }}</span>
                                    </div>
                                </div>
                                <div class="agenda-item-body">
                                    <div class="agenda-badges">
                                        <span class="badge badge-info">{{ data_get($item, 'kode', '-') }}</span>
                                        <span class="badge badge-secondary">{{ data_get($item, 'sks', 0) }} SKS</span>
                                        <span class="badge badge-secondary">{{ $jenisLabel }}</span>
                                    </div>
                                    <h4 class="agenda-title">{{ data_get($item, 'nama', '-') }}</h4>
                                    <div class="agenda-details">
                                        @if(data_get($item, 'ruangan'))
                                        <span class="agenda-detail-item">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                            {{ data_get($item, 'ruangan') }}
                                        </span>
                                        @endif
                                        @if(data_get($item, 'dosen'))
                                        <span class="agenda-detail-item">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            {{ data_get($item, 'dosen') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="agenda-item-actions">
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="openScheduleEditModal({{ Js::from($item) }})" title="Edit Jadwal">
                                        Edit
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="agenda-empty-day">
                                <span>Tidak ada jadwal kuliah di hari {{ $day }}</span>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ==========================================================
         TAB 2: MATAKULIAH (KHUSUS SEMESTER INI)
    =========================================================== --}}
    <div id="tab-matakuliah" role="tabpanel" data-tab-panel="matakuliah" hidden>
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="semester-course-header">
                    <div>
                        <h3 style="margin: 0 0 var(--space-xs) 0; color: var(--text-primary); font-size: 1.15rem;">Daftar Mata Kuliah Semester {{ $currentSemester }}</h3>
                        <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Kelola mata kuliah, ruang kelas, jadwal, dan dosen pengampu semester ini.</p>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="openSemesterCreateModal()">
                        <span>+</span> Tambah Matakuliah
                    </button>
                </div>

                {{-- Search Box --}}
                <div class="course-search-bar">
                    <div class="search-input-wrapper">
                        <span class="search-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input type="search" id="semester-course-search" class="form-control" autocomplete="off" placeholder="Cari kode, nama mata kuliah, dosen, atau ruangan..." oninput="filterSemesterCourses(this.value)">
                    </div>
                </div>

                {{-- Tabel Matakuliah Semester Ini --}}
                @if (empty($courses) || $courses->isEmpty())
                <div class="schedule-empty-state">
                    <div class="empty-state-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.4;"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    </div>
                    <h4>Belum ada mata kuliah terdaftar</h4>
                    <p>Belum ada mata kuliah yang terdaftar untuk Semester {{ $currentSemester }}.</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openSemesterCreateModal()">+ Tambah Matakuliah Sekarang</button>
                </div>
                @else
                <div class="table-wrapper">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Kode</th>
                                <th>Nama Mata Kuliah</th>
                                <th style="width: 70px; text-align: center;">SKS</th>
                                <th style="width: 110px;">Jenis</th>
                                <th>Dosen</th>
                                <th>Ruangan</th>
                                <th>Hari & Jam</th>
                                <th style="width: 130px; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="semester-courses-tbody">
                            @foreach ($courses as $mk)
                            @php
                            $jenis = strtolower((string) ($mk->jenis ?? $mk['jenis'] ?? ''));
                            $jenisBadgeClass = match($jenis) {
                                'core', 'inti' => 'badge-info',
                                'elective core', 'pilihan inti', 'elective' => 'badge-epic',
                                default => 'badge-secondary',
                            };
                            $jenisLabel = match($jenis) {
                                'core', 'inti' => 'Inti',
                                'elective core', 'pilihan inti', 'elective' => 'Pilihan Inti',
                                'supporting', 'pendukung' => 'Pendukung',
                                default => $mk->jenis ?? $mk['jenis'] ?? '-',
                            };
                            $kodeMk = $mk->kode ?? $mk['kode'] ?? '-';
                            $namaMk = $mk->nama ?? $mk['nama'] ?? '-';
                            $dosenMk = $mk->dosen ?? $mk['dosen'] ?? '';
                            $ruanganMk = $mk->ruangan ?? $mk['ruangan'] ?? '';
                            @endphp
                            <tr class="semester-course-row" data-search="{{ strtolower($kodeMk . ' ' . $namaMk . ' ' . $dosenMk . ' ' . $ruanganMk) }}">
                                <td>
                                    <span class="badge badge-info font-monospace">{{ $kodeMk }}</span>
                                </td>
                                <td>
                                    <strong style="color: var(--text-primary);">{{ $namaMk }}</strong>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-secondary">{{ $mk->sks ?? $mk['sks'] ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $jenisBadgeClass }}">{{ $jenisLabel }}</span>
                                </td>
                                <td>
                                    @if($dosenMk)
                                    <span style="color: var(--text-secondary);">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        {{ $dosenMk }}
                                    </span>
                                    @else
                                    <span class="text-muted" style="font-size: 0.8rem;">Belum diatur</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ruanganMk)
                                    <span class="badge badge-secondary" style="font-weight: 500;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        {{ $ruanganMk }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($mk->hari) && !empty($mk->jam_mulai))
                                    <div class="schedule-pill-display">
                                        <span class="badge badge-success">{{ $mk->hari }}</span>
                                        <span style="font-size: 0.82rem; color: var(--text-secondary);">
                                            {{ substr($mk->jam_mulai, 0, 5) }}{{ $mk->jam_selesai ? ' - ' . substr($mk->jam_selesai, 0, 5) : '' }}
                                        </span>
                                    </div>
                                    @else
                                    <span class="badge badge-warning" style="font-size: 0.72rem;">Belum ada jadwal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="openScheduleEditModal({{ Js::from($mk) }})">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="openSemesterDeleteModal({{ $mk->id }}, '{{ addslashes($namaMk) }}', '{{ addslashes($kodeMk) }}')">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            <tr id="semester-search-empty" style="display: none;">
                                <td colspan="8" class="table-empty">
                                    <p>Tidak ada mata kuliah yang cocok dengan pencarian.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
        </div>
    </div>

</div>

{{-- ==========================================================
     MODALS
=========================================================== --}}
<div id="semester-modal-overlay" class="modal-overlay" onclick="closeSemesterModals()"></div>

{{-- ===== Modal 1: Create Course ===== --}}
<div id="semester-create-modal" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Tambah Matakuliah Semester {{ $currentSemester }}</h3>
        <button type="button" onclick="closeSemesterModals()" class="modal-close">&times;</button>
    </div>

    <form id="semester-create-form" action="{{ route('kuliah.course.store') }}" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" name="from_semester" value="1">
        <input type="hidden" name="status" value="Not Taken">

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Semester <span class="required">*</span></label>
                <select name="semester[]" class="form-control" required>
                    @foreach ($allSemesters ?? [] as $sem)
                    <option value="{{ $sem->number }}" {{ $sem->number == $currentSemester ? 'selected' : '' }}>Semester {{ $sem->number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kode Mata Kuliah <span class="required">*</span></label>
                <input type="text" name="kode" class="form-control" placeholder="Contoh: IF2101" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Nama Mata Kuliah <span class="required">*</span></label>
                <input type="text" name="nama" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
            </div>
            <div class="form-group">
                <label class="form-label">SKS <span class="required">*</span></label>
                <input type="number" name="sks" class="form-control" min="1" max="10" value="3" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Jenis Mata Kuliah <span class="required">*</span></label>
                <select name="jenis" class="form-control" required>
                    <option value="Core">Inti (Core)</option>
                    <option value="Elective Core">Pilihan Inti</option>
                    <option value="Supporting">Pendukung</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Hari Kuliah</label>
                <select name="hari" class="form-control">
                    <option value="">-- Pilih Hari --</option>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Jam Mulai</label>
                <input type="time" name="jam_mulai" class="form-control" value="08:00" step="300" aria-label="Jam mulai">
            </div>
            <div class="form-group">
                <label class="form-label">Ruangan</label>
                <input type="text" name="ruangan" class="form-control" placeholder="Contoh: Lab 3 / R. 302">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Dosen Pengampu</label>
            <input type="text" name="dosen" class="form-control" placeholder="Nama dosen pengampu...">
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeSemesterModals()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Matakuliah</button>
        </div>
    </form>
</div>

{{-- ===== Modal 2: Edit Schedule & Course ===== --}}
<div id="semester-edit-modal" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <div>
            <h3 style="margin: 0;">Edit Jadwal & Matakuliah</h3>
            <p id="semester-edit-course" class="task-meta" style="margin: 4px 0 0 0; color: var(--accent-primary); font-weight: 500;"></p>
        </div>
        <button type="button" onclick="closeSemesterModals()" class="modal-close">&times;</button>
    </div>

    <form id="semester-edit-form" method="POST" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Kode Mata Kuliah <span class="required">*</span></label>
                <input id="semester-edit-kode" type="text" name="kode" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">SKS <span class="required">*</span></label>
                <input id="semester-edit-sks" type="number" name="sks" class="form-control" min="1" max="10" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Nama Mata Kuliah <span class="required">*</span></label>
            <input id="semester-edit-nama" type="text" name="nama" class="form-control" required>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Hari Kuliah</label>
                <select id="semester-edit-hari" name="hari" class="form-control">
                    <option value="">-- Tidak ada hari --</option>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Ruangan</label>
                <input id="semester-edit-ruangan" type="text" name="ruangan" class="form-control" placeholder="Contoh: R. 301 / Lab">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Jam Mulai</label>
                <input id="semester-edit-jam-mulai" type="time" name="jam_mulai" class="form-control" step="300" aria-label="Jam mulai">
            </div>
            <div class="form-group">
                <label class="form-label">Jam Selesai</label>
                <input id="semester-edit-jam-selesai" type="time" name="jam_selesai" class="form-control" step="300" aria-label="Jam selesai">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Dosen Pengampu</label>
            <input id="semester-edit-dosen" type="text" name="dosen" class="form-control" placeholder="Nama dosen...">
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeSemesterModals()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

{{-- ===== Modal 3: Delete Semester Course Confirmation ===== --}}
<div id="semester-delete-modal" class="modal modal-sm modal-delete" aria-hidden="true">
    <div class="modal-header">
        <h3>Hapus Matakuliah?</h3>
        <button type="button" onclick="closeSemesterModals()" class="modal-close">&times;</button>
    </div>
    <p id="semester-delete-text" class="task-meta"></p>
    <form id="semester-delete-form" method="POST">
        @csrf
        @method('DELETE')
        <input type="hidden" name="from_semester" value="1">
        <div class="modal-actions">
            <button type="button" onclick="closeSemesterModals()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-danger">Hapus</button>
        </div>
    </form>
</div>

@endsection

@push('styles')
<style>
    /* =========================================================
       PAGE HEADER & STATS CARDS
    ========================================================= */
    .jadwal-header-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--space-md);
        margin-bottom: var(--space-lg);
    }

    .jadwal-header-actions {
        display: flex;
        align-items: center;
        gap: var(--space-sm);
        flex-wrap: wrap;
    }

    .semester-picker-wrapper {
        display: inline-flex;
        align-items: center;
    }

    .semester-dropdown {
        background: rgba(15, 23, 42, 0.85);
        color: var(--text-primary);
        border: 1px solid var(--border-default);
        border-radius: var(--radius-md);
        padding: 0.5rem 0.9rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        outline: none;
        transition: var(--transition-fast);
        font-family: var(--font-base);
    }

    .semester-dropdown:hover,
    .semester-dropdown:focus {
        border-color: var(--accent-primary);
        background: rgba(30, 41, 59, 0.95);
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
    }

    .jadwal-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: var(--space-md);
        margin-bottom: var(--space-xl);
    }

    .stat-card-hover {
        transition: var(--transition-normal);
        position: relative;
    }

    .stat-card-hover:hover {
        transform: translateY(-2px);
    }

    .stat-card-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-unit {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-muted);
        margin-left: 2px;
    }

    .stat-icon-badge {
        font-size: 1.6rem;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-md);
    }

    .stat-card-active {
        border-color: rgba(99, 102, 241, 0.4);
        background: linear-gradient(180deg, rgba(99, 102, 241, 0.12), var(--bg-card-2));
    }

    .text-accent {
        color: #818cf8 !important;
    }

    /* =========================================================
       TAB BAR & PILLS
    ========================================================= */
    .jadwal-tab-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--space-md);
        margin-bottom: var(--space-md);
        padding-bottom: var(--space-xs);
    }

    .jadwal-tabs-pills {
        display: inline-flex;
        background: rgba(15, 23, 42, 0.8);
        padding: 4px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-default);
        gap: 4px;
    }

    .jadwal-tab-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        padding: 0.45rem 1rem;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: var(--transition-fast);
        font-family: var(--font-base);
    }

    .jadwal-tab-btn:hover {
        color: var(--text-primary);
        background: rgba(255, 255, 255, 0.04);
    }

    .jadwal-tab-btn.is-active {
        background: var(--accent-primary);
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
    }

    .jadwal-legend {
        display: flex;
        align-items: center;
        gap: var(--space-md);
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .dot-core {
        background: #6366f1;
        box-shadow: 0 0 6px rgba(99, 102, 241, 0.6);
    }

    .dot-elective {
        background: #ec4899;
        box-shadow: 0 0 6px rgba(236, 72, 153, 0.6);
    }

    .dot-supporting {
        background: #38bdf8;
        box-shadow: 0 0 6px rgba(56, 189, 248, 0.6);
    }

    /* =========================================================
       SCHEDULE MAIN CARD & TOOLBAR
    ========================================================= */
    .schedule-main-card {
        padding: var(--space-md);
        border-radius: var(--radius-lg);
    }

    .schedule-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-md);
        flex-wrap: wrap;
        gap: var(--space-sm);
    }

    .schedule-view-toggle {
        display: inline-flex;
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--border-default);
        border-radius: var(--radius-sm);
        padding: 2px;
        gap: 2px;
    }

    .view-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        padding: 0.35rem 0.75rem;
        border-radius: calc(var(--radius-sm) - 2px);
        font-size: 0.78rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: var(--transition-fast);
        font-family: var(--font-base);
    }

    .view-btn.is-active {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-primary);
    }

    .schedule-hint {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    /* =========================================================
       TIMETABLE GRID TABLE
    ========================================================= */
    .schedule-scroll {
        overflow-x: auto;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-default);
        background: rgba(15, 23, 42, 0.7);
    }

    .custom-scrollbar::-webkit-scrollbar {
        height: 7px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.8);
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: var(--border-default);
        border-radius: 999px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: var(--border-hover);
    }

    .schedule-grid-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 1100px;
        table-layout: fixed;
    }

    /* Column Headers (Hours) */
    .schedule-grid-table thead th {
        background: rgba(15, 23, 42, 0.95);
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 10px 4px;
        text-align: center;
        border-bottom: 1px solid var(--border-default);
        border-left: 1px solid rgba(255, 255, 255, 0.04);
        position: sticky;
        top: 0;
        z-index: 3;
    }

    .schedule-day-header {
        width: 130px;
        min-width: 130px;
        left: 0;
        position: sticky;
        z-index: 4 !important;
        background: rgba(15, 23, 42, 0.98) !important;
        border-right: 1px solid var(--border-default);
        text-align: left !important;
        padding-left: var(--space-md) !important;
    }

    .day-header-title {
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.72rem;
    }

    .hour-time {
        display: block;
        font-variant-numeric: tabular-nums;
        color: var(--text-faint);
    }

    /* Day Row & Left Sticky Cell */
    .schedule-day-cell {
        background: rgba(15, 23, 42, 0.95);
        border-right: 1px solid var(--border-default);
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        left: 0;
        position: sticky;
        z-index: 2;
        padding: var(--space-sm) var(--space-md);
        text-align: left;
        vertical-align: middle;
    }

    .schedule-day-cell.is-today {
        background: rgba(26, 35, 60, 0.98);
        border-left: 3px solid var(--accent-primary);
    }

    .day-cell-content {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .day-name-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .day-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .badge-today {
        background: rgba(99, 102, 241, 0.25);
        color: #a5b4fc;
        font-size: 0.65rem;
        padding: 1px 5px;
        border-radius: 4px;
        border: 1px solid rgba(99, 102, 241, 0.4);
    }

    .day-meta {
        font-size: 0.72rem;
        color: var(--text-muted);
    }

    /* Slot Cells */
    .schedule-slot-cell {
        border-left: 1px solid rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        padding: 5px;
        height: 105px;
        vertical-align: top;
        transition: background 0.15s ease;
    }

    .schedule-slot-cell.slot-empty:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .day-row-current .schedule-slot-cell {
        background: rgba(99, 102, 241, 0.015);
    }

    /* Course Cards inside Grid */
    .schedule-course-card {
        border-radius: var(--radius-sm);
        padding: 8px 10px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        text-align: left;
        box-shadow: var(--shadow-sm);
    }

    .schedule-course-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.45);
        z-index: 1;
        border-color: rgba(255, 255, 255, 0.3) !important;
    }

    /* Course Category Colors */
    .course-type-core {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(30, 41, 59, 0.95));
        border: 1px solid rgba(99, 102, 241, 0.4);
        border-left: 3px solid #6366f1;
    }

    .course-type-elective {
        background: linear-gradient(135deg, rgba(236, 72, 153, 0.22), rgba(30, 41, 59, 0.95));
        border: 1px solid rgba(236, 72, 153, 0.4);
        border-left: 3px solid #ec4899;
    }

    .course-type-supporting {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.22), rgba(30, 41, 59, 0.95));
        border: 1px solid rgba(14, 165, 233, 0.4);
        border-left: 3px solid #38bdf8;
    }

    /* Course Card Internals */
    .course-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 4px;
        margin-bottom: 4px;
    }

    .course-code-pill {
        font-size: 0.72rem;
        font-weight: 700;
        color: #ffffff;
        background: rgba(0, 0, 0, 0.35);
        padding: 1px 6px;
        border-radius: 4px;
        letter-spacing: 0.3px;
        font-family: monospace;
    }

    .course-sks-pill {
        font-size: 0.68rem;
        font-weight: 600;
        color: var(--text-muted);
        background: rgba(255, 255, 255, 0.08);
        padding: 1px 5px;
        border-radius: 4px;
    }

    .course-card-body {
        flex-grow: 1;
        margin-bottom: 4px;
    }

    .course-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.25;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .course-card-footer {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-items: center;
    }

    .course-meta-pill {
        font-size: 0.68rem;
        padding: 1px 5px;
        border-radius: 3px;
        background: rgba(0, 0, 0, 0.25);
        color: var(--text-secondary);
        white-space: nowrap;
    }

    .course-lecturer-row {
        font-size: 0.68rem;
        color: var(--text-muted);
        margin-top: 3px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* =========================================================
       AGENDA / LIST VIEW
    ========================================================= */
    .agenda-days-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: var(--space-md);
    }

    .agenda-day-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--border-default);
        border-radius: var(--radius-md);
        padding: var(--space-md);
        display: flex;
        flex-direction: column;
        gap: var(--space-sm);
    }

    .agenda-day-card.is-today {
        border-color: rgba(99, 102, 241, 0.5);
        background: linear-gradient(180deg, rgba(99, 102, 241, 0.08), rgba(15, 23, 42, 0.8));
    }

    .agenda-day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: var(--space-xs);
        border-bottom: 1px solid var(--border-subtle);
    }

    .agenda-day-title {
        display: flex;
        align-items: center;
        gap: var(--space-xs);
    }

    .agenda-day-title h3 {
        margin: 0;
        font-size: 1.05rem;
        color: var(--text-primary);
    }

    .agenda-day-count {
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    .agenda-courses-list {
        display: flex;
        flex-direction: column;
        gap: var(--space-xs);
    }

    .agenda-course-item {
        display: flex;
        align-items: center;
        gap: var(--space-sm);
        padding: 0.6rem 0.8rem;
        border-radius: var(--radius-sm);
        transition: var(--transition-fast);
    }

    .agenda-course-item:hover {
        transform: translateX(2px);
    }

    .agenda-item-left {
        min-width: 65px;
        text-align: center;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        padding-right: 0.5rem;
    }

    .agenda-item-time {
        display: flex;
        flex-direction: column;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--text-primary);
        font-family: monospace;
    }

    .time-separator {
        color: var(--text-muted);
        font-size: 0.6rem;
        line-height: 1;
    }

    .agenda-item-body {
        flex-grow: 1;
        overflow: hidden;
    }

    .agenda-badges {
        display: flex;
        gap: 4px;
        margin-bottom: 2px;
    }

    .agenda-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 2px 0;
    }

    .agenda-details {
        display: flex;
        gap: var(--space-sm);
        font-size: 0.72rem;
        color: var(--text-muted);
        flex-wrap: wrap;
    }

    .agenda-empty-day {
        padding: var(--space-md);
        text-align: center;
        color: var(--text-muted);
        font-size: 0.8rem;
        background: rgba(255, 255, 255, 0.015);
        border-radius: var(--radius-sm);
        border: 1px dashed rgba(255, 255, 255, 0.05);
    }

    /* =========================================================
       EMPTY STATE & TAB 2 STYLES
    ========================================================= */
    .schedule-empty-state {
        text-align: center;
        padding: var(--space-2xl) var(--space-md);
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 2.5rem;
        margin-bottom: var(--space-sm);
    }

    .schedule-empty-state h4 {
        color: var(--text-primary);
        margin: 0 0 var(--space-xs) 0;
        font-size: 1.1rem;
    }

    .schedule-empty-state p {
        font-size: 0.85rem;
        max-width: 480px;
        margin: 0 auto var(--space-md) auto;
    }

    .semester-course-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--space-md);
        flex-wrap: wrap;
        gap: var(--space-sm);
    }

    .course-search-bar {
        margin-bottom: var(--space-md);
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.85rem;
        pointer-events: none;
        opacity: 0.6;
    }

    .search-input-wrapper input {
        padding-left: 2.4rem;
    }

    .schedule-pill-display {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* =========================================================
       RESPONSIVE DESIGN
    ========================================================= */
    @media (max-width: 960px) {
        .jadwal-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .jadwal-header-wrapper {
            flex-direction: column;
            align-items: flex-start;
        }

        .jadwal-header-actions {
            width: 100%;
        }

        .jadwal-header-actions .btn {
            width: 100%;
        }
    }

    @media (max-width: 560px) {
        .jadwal-stats-grid {
            grid-template-columns: 1fr;
        }

        .jadwal-tabs-pills {
            width: 100%;
            display: flex;
        }

        .jadwal-tab-btn {
            flex: 1;
            justify-content: center;
            font-size: 0.78rem;
            padding: 0.4rem 0.5rem;
        }

        .jadwal-legend {
            display: none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // ===== Change Semester =====
    function changeSemester(sem) {
        const url = new URL(window.location);
        url.searchParams.set('semester', sem);
        window.location.href = url.toString();
    }

    // ===== Tab switching (Jadwal <-> Matakuliah) =====
    document.querySelectorAll('[data-tabs]').forEach(function(tabs) {
        const buttons = tabs.querySelectorAll('[data-tab-target]');
        const panels = tabs.querySelectorAll('[data-tab-panel]');

        function activateTab(target) {
            buttons.forEach(function(button) {
                const active = button.dataset.tabTarget === target;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            panels.forEach(function(panel) {
                panel.hidden = panel.dataset.tabPanel !== target;
            });

            // Sync URL query tab parameter without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', target);
            window.history.replaceState({}, '', url);
        }

        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                activateTab(button.dataset.tabTarget);
            });
        });

        activateTab(tabs.dataset.initialTab === 'matakuliah' ? 'matakuliah' : 'jadwal');
    });

    // ===== View switching in Jadwal Tab (Grid <-> Agenda) =====
    function switchScheduleView(viewType) {
        const gridView = document.getElementById('schedule-view-grid');
        const agendaView = document.getElementById('schedule-view-agenda');
        const btnGrid = document.getElementById('btn-view-grid');
        const btnAgenda = document.getElementById('btn-view-agenda');

        if (viewType === 'agenda') {
            gridView.style.display = 'none';
            agendaView.style.display = 'block';
            btnGrid.classList.remove('is-active');
            btnAgenda.classList.add('is-active');
        } else {
            gridView.style.display = 'block';
            agendaView.style.display = 'none';
            btnGrid.classList.add('is-active');
            btnAgenda.classList.remove('is-active');
        }
    }

    // ===== Search / Filter Matakuliah Semester Ini =====
    function filterSemesterCourses(query) {
        const normalized = query.trim().toLowerCase();
        const rows = document.querySelectorAll('.semester-course-row');
        let visibleCount = 0;

        rows.forEach(function(row) {
            const dataText = row.getAttribute('data-search') || '';
            const match = dataText.includes(normalized);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        const emptyMsg = document.getElementById('semester-search-empty');
        if (emptyMsg) {
            emptyMsg.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    // ===== Modal: Create Course =====
    function openSemesterCreateModal() {
        document.getElementById('semester-create-modal').classList.add('show');
        document.getElementById('semester-modal-overlay').classList.add('show');
    }

    // ===== Modal: Edit Schedule =====
    function openScheduleEditModal(course) {
        document.getElementById('semester-edit-course').textContent = `${course.nama || ''} (${course.kode || ''})`;

        document.getElementById('semester-edit-kode').value = course.kode || '';
        document.getElementById('semester-edit-nama').value = course.nama || '';
        document.getElementById('semester-edit-sks').value = course.sks || 2;
        document.getElementById('semester-edit-ruangan').value = course.ruangan || '';
        document.getElementById('semester-edit-dosen').value = course.dosen || '';
        document.getElementById('semester-edit-hari').value = course.hari || '';

        document.getElementById('semester-edit-jam-mulai').value = course.jam_mulai ? course.jam_mulai.substring(0, 5) : '08:00';
        document.getElementById('semester-edit-jam-selesai').value = course.jam_selesai ? course.jam_selesai.substring(0, 5) : '10:00';

        document.getElementById('semester-edit-form').action = `/kuliah/semester-courses/${course.id}/schedule`;

        document.getElementById('semester-edit-modal').classList.add('show');
        document.getElementById('semester-modal-overlay').classList.add('show');
    }

    // ===== Modal: Delete Semester Course =====
    function openSemesterDeleteModal(id, nama, kode) {
        document.getElementById('semester-delete-text').textContent = `Mata kuliah "${nama}" (${kode}) akan dihapus dari jadwal semester ini.`;
        document.getElementById('semester-delete-form').action = `/kuliah/semester-courses/${id}`;

        document.getElementById('semester-delete-modal').classList.add('show');
        document.getElementById('semester-modal-overlay').classList.add('show');
    }

    // ===== Tutup semua modal =====
    function closeSemesterModals() {
        document.getElementById('semester-create-modal').classList.remove('show');
        document.getElementById('semester-edit-modal').classList.remove('show');
        document.getElementById('semester-delete-modal').classList.remove('show');
        document.getElementById('semester-modal-overlay').classList.remove('show');
    }

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSemesterModals();
        }
    });
</script>
@endpush