@extends('layouts.app')

@section('topbar')
<a href="{{ route('kuliah.jadwal', ['semester' => $semester->number]) }}" class="btn btn-secondary active">Semester</a>
<a href="{{ route('kuliah.matakuliah') }}" class="btn btn-secondary">Matakuliah</a>
@endsection

@section('content')

{{-- Header --}}
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-md); margin-bottom: var(--space-lg);">
    <div>
        <div style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: 4px;">
            <a href="{{ route('kuliah.jadwal') }}" class="btn btn-secondary btn-sm" title="Kembali ke Jadwal Utama" style="display: inline-flex; align-items: center; gap: 4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Kembali
            </a>
            <h2 class="title" style="margin: 0;">{{ $semester->name ?: 'Semester ' . $semester->number }}</h2>
        </div>
        <p class="subtitle">{{ $courses->count() }} Mata Kuliah &bull; {{ $courses->sum('sks') }} Total SKS</p>
    </div>
    <a href="{{ route('kuliah.jadwal', ['semester' => $semester->number]) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        Lihat di Timetable Grid
    </a>
</div>

{{-- Stat Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-md); margin-bottom: var(--space-xl);">
    <div class="card shadow-sm border-0" style="background: var(--bg-card-2, rgba(30, 36, 50, 0.6)); border: 1px solid var(--border-default);">
        <div class="card-body" style="padding: var(--space-md);">
            <div class="text-muted small">Total Mata Kuliah</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-top: 4px;">{{ $courses->count() }} MK</div>
        </div>
    </div>
    <div class="card shadow-sm border-0" style="background: var(--bg-card-2, rgba(30, 36, 50, 0.6)); border: 1px solid var(--border-default);">
        <div class="card-body" style="padding: var(--space-md);">
            <div class="text-muted small">Total SKS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-top: 4px;">{{ $courses->sum('sks') }} SKS</div>
        </div>
    </div>
    <div class="card shadow-sm border-0" style="background: var(--bg-card-2, rgba(30, 36, 50, 0.6)); border: 1px solid var(--border-default);">
        <div class="card-body" style="padding: var(--space-md);">
            <div class="text-muted small">Mata Kuliah Inti</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-top: 4px;">{{ $courses->filter(fn($c) => strtolower($c->jenis ?? '') === 'core')->count() }} MK</div>
        </div>
    </div>
</div>

{{-- Main Card --}}
<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- Tab Buttons --}}
        <div style="display: flex; gap: var(--space-xs); border-bottom: 1px solid var(--border-default); margin-bottom: var(--space-lg); padding-bottom: var(--space-xs);">
            <button id="tab-jadwal-button" type="button" class="btn btn-primary btn-sm" onclick="switchSemesterTab('jadwal')" style="display: inline-flex; align-items: center; gap: 4px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Jadwal Kuliah
            </button>
            <button id="tab-matakuliah-button" type="button" class="btn btn-secondary btn-sm" onclick="switchSemesterTab('matakuliah')" style="display: inline-flex; align-items: center; gap: 4px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                Daftar Matakuliah
            </button>
        </div>

        {{-- Section 1: Jadwal --}}
        <section id="panel-jadwal">
            @if ($courses->isEmpty())
            <div style="text-align: center; padding: var(--space-xl); color: var(--text-muted);">
                <p>Belum ada jadwal untuk semester ini.</p>
            </div>
            @else
            <div class="table-wrapper">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Mata Kuliah</th>
                            <th style="text-align:center;">SKS</th>
                            <th>Hari & Jam</th>
                            <th>Ruangan</th>
                            <th>Dosen</th>
                            <th>Jenis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                        @php
                        $jenisLabel = match(strtolower($course->jenis ?? '')) { 'core' => 'Inti', 'elective core' => 'Pilihan Inti', 'supporting' => 'Pendukung', default => $course->jenis ?? '-' };
                        $jenisBadgeClass = match(strtolower($course->jenis ?? '')) { 'core' => 'badge-info', 'elective core' => 'badge-epic', default => 'badge-secondary' };
                        @endphp
                        <tr>
                            <td><span class="badge badge-secondary font-monospace">{{ $course->kode }}</span></td>
                            <td><strong style="color:var(--text-primary);">{{ $course->nama }}</strong></td>
                            <td style="text-align:center;"><span class="badge badge-secondary">{{ $course->sks }}</span></td>
                            <td>
                                @if($course->hari)
                                <span class="badge badge-secondary">{{ $course->hari }} {{ $course->jam_mulai ? substr($course->jam_mulai,0,5) : '' }}{{ $course->jam_selesai ? ' - '.substr($course->jam_selesai,0,5) : '' }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($course->ruangan)
                                <span class="badge badge-secondary">{{ $course->ruangan }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $course->dosen ?: '-' }}</td>
                            <td><span class="badge {{ $jenisBadgeClass }}">{{ $jenisLabel }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>

        {{-- Section 2: Matakuliah --}}
        <section id="panel-matakuliah" style="display: none;">
            @if ($courses->isEmpty())
            <div style="text-align: center; padding: var(--space-xl); color: var(--text-muted);">
                <p>Belum ada mata kuliah yang terdaftar untuk semester ini.</p>
            </div>
            @else
            <div class="table-wrapper">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Mata Kuliah</th>
                            <th style="text-align:center;">SKS</th>
                            <th>Status</th>
                            <th>Nilai</th>
                            <th>Jenis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                        @php
                        $grades = collect($course->nilai ?? [])->map(fn ($entry) => is_array($entry) ? ($entry['grade'] ?? '-') : $entry)->filter()->implode(', ');
                        $statusClass = match(strtolower($course->status ?? '')) { 'passed' => 'badge-success', 'failed' => 'badge-danger', 'not taken' => 'badge-warning', default => 'badge-secondary' };
                        $jenisLabel = match(strtolower($course->jenis ?? '')) { 'core' => 'Inti', 'elective core' => 'Pilihan Inti', 'supporting' => 'Pendukung', default => $course->jenis ?? '-' };
                        $jenisBadgeClass = match(strtolower($course->jenis ?? '')) { 'core' => 'badge-info', 'elective core' => 'badge-epic', default => 'badge-secondary' };
                        @endphp
                        <tr>
                            <td><span class="badge badge-secondary font-monospace">{{ $course->kode }}</span></td>
                            <td><strong style="color:var(--text-primary);">{{ $course->nama }}</strong></td>
                            <td style="text-align:center;"><span class="badge badge-secondary">{{ $course->sks }}</span></td>
                            <td><span class="badge {{ $statusClass }}">{{ $course->status ?: 'Not Taken' }}</span></td>
                            <td>
                                @if($grades)
                                <span class="badge badge-info" style="font-weight:700;">{{ $grades }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $jenisBadgeClass }}">{{ $jenisLabel }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>

    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchSemesterTab(tab) {
        const pJadwal = document.getElementById('panel-jadwal');
        const pMatakuliah = document.getElementById('panel-matakuliah');
        const btnJadwal = document.getElementById('tab-jadwal-button');
        const btnMatakuliah = document.getElementById('tab-matakuliah-button');

        if (tab === 'jadwal') {
            pJadwal.style.display = 'block';
            pMatakuliah.style.display = 'none';
            btnJadwal.classList.replace('btn-secondary', 'btn-primary');
            btnMatakuliah.classList.replace('btn-primary', 'btn-secondary');
        } else {
            pJadwal.style.display = 'none';
            pMatakuliah.style.display = 'block';
            btnMatakuliah.classList.replace('btn-secondary', 'btn-primary');
            btnJadwal.classList.replace('btn-primary', 'btn-secondary');
        }
    }
</script>
@endpush