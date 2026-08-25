@extends('layouts.app')

@section('topbar')
<a href="{{ route('kuliah.matakuliah') }}" class="btn btn-secondary">Course</a>
@endsection

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h2 class="title">Course List</h2>
    <button onclick="openModal()" class="btn btn-primary">+ Create Course</button>
</div>

@php
$targets = [
'all' => 144,
'core' => 102,
'elective' => 24,
'supporting' => 12,
];

$progressCards = [
['key' => 'all', 'label' => 'All SKS', 'value' => $summary['all'] ?? 0],
['key' => 'core', 'label' => 'Core', 'value' => $summary['core'] ?? 0],
['key' => 'elective', 'label' => 'Elective Core', 'value' => $summary['elective'] ?? 0],
['key' => 'supporting', 'label' => 'Supporting', 'value' => $summary['supporting'] ?? 0],
];
@endphp

<div class="row g-3 mb-4" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: var(--space-md); margin-bottom: var(--space-xl);">
    @foreach ($progressCards as $card)
    @php
    $target = $targets[$card['key']];
    $percent = min(100, round(($card['value'] / max(1, $target)) * 100));
    $progressHue = $percent * 1.2;
    @endphp
    <div class="col-md-3" style="width: auto;">
        <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(180deg, hsla({{ $progressHue }}, 70%, 45%, 0.2), var(--bg-card-2)); border-color: hsla({{ $progressHue }}, 70%, 45%, 0.45);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">{{ $card['label'] }}</div>
                    <strong>{{ $card['value'] }} / {{ $target }}</strong>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background-color: hsl({{ $progressHue }}, 70%, 45%);" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted mt-2">{{ $percent }}% target</div>
            </div>
        </div>
    </div>
    @endforeach
    @php
    $dPercent = min(100, round($summary['d_percentage'] ?? 0, 1));
    $dQualityPercent = max(0, min(100, round((1 - ($dPercent / 20)) * 100, 1)));
    $dProgressHue = $dQualityPercent * 1.2;
    @endphp
    <div style="width: auto;">
        <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(180deg, hsla({{ $dProgressHue }}, 70%, 45%, 0.2), var(--bg-card-2)); border-color: hsla({{ $dProgressHue }}, 70%, 45%, 0.45);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">Nilai D</div>
                    <strong>{{ $summary['d_credits'] ?? 0 }} SKS</strong>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $dQualityPercent }}%; background-color: hsl({{ $dProgressHue }}, 70%, 45%);" aria-valuenow="{{ $dQualityPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted mt-2">{{ $summary['d_percentage'] ?? 0 }}% dari total SKS</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="form-group" style="margin-bottom: var(--space-lg);">
            <label class="form-label" for="course-search">Search Course</label>
            <input type="search" id="course-search" class="form-control" autocomplete="off" placeholder="Search by code or course name...">
        </div>

        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Course Name</th>
                    <th>Credits</th>
                    <th>Semester</th>
                    <th>Grades</th>
                    <th>Status</th>
                    <th>Course Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($matakuliah as $mk)
                <tr class="course-row">
                    <td>{{ $mk['kode'] }}</td>
                    <td>{{ $mk['nama'] }}</td>
                    <td>{{ $mk['sks'] }}</td>
                    <td>
                        @php
                        $gradeEntries = is_array($mk['nilai']) ? $mk['nilai'] : [];
                        $semesterLabels = collect($gradeEntries)->map(function ($entry) {
                        return is_array($entry) && !empty($entry['semester']) ? 'Sem ' . $entry['semester'] : null;
                        })->filter()->unique()->implode(', ');
                        @endphp
                        {{ $semesterLabels ?: '-' }}
                    </td>
                    <td>
                        @php
                        $gradeOrder = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 0];
                        $bestGrade = '';

                        if (count($gradeEntries)) {
                        $bestGrade = collect($gradeEntries)->sortByDesc(function ($entry) use ($gradeOrder) {
                        $grade = is_array($entry) ? ($entry['grade'] ?? 'F') : $entry;
                        return $gradeOrder[strtoupper((string) $grade)] ?? 0;
                        })->first();
                        $bestGrade = is_array($bestGrade) ? ($bestGrade['grade'] ?? 'F') : $bestGrade;
                        } else {
                        $bestGrade = '';
                        }
                        @endphp
                        {{ $bestGrade }}
                    </td>
                    <td>
                        @php
                        $statusClass = match (strtolower($mk['status'])) {
                        'passed' => 'badge-success',
                        'failed' => 'badge-danger',
                        'not taken' => 'badge-warning',
                        default => 'badge-secondary',
                        };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $mk['status'] }}</span>
                    </td>
                    <td>
                        @php
                        $jenisLabel = match (strtolower($mk['jenis'])) {
                        'core' => 'Inti',
                        'elective core' => 'Pilihan Inti',
                        'supporting' => 'Pendukung',
                        default => $mk['jenis'],
                        };
                        @endphp
                        {{ $jenisLabel }}
                    </td>
                    <td>
                        <div class="table-actions" style="justify-content: flex-start; flex-wrap: nowrap; white-space: nowrap;">
                            <button type="button" onclick="openEditModal('{{ $mk['kode'] }}')" class="btn btn-sm btn-secondary">Edit</button>
                            <button type="button" onclick="openDeleteModal('{{ $mk['kode'] }}')" class="btn btn-sm btn-danger">Delete</button>
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr id="course-empty-state" style="display: none;">
                    <td colspan="8" class="table-empty">No courses found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-overlay" class="modal-overlay" onclick="closeAllModals()"></div>

<div id="modal-create" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Create Course</h3>
        <button type="button" onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>

    <form action="{{ route('kuliah.course.store') }}" method="POST" autocomplete="off">
        @csrf

        <div class="form-group">
            <label class="form-label">Course Code <span class="required">*</span></label>
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
                <label class="form-label">Course Type <span class="required">*</span></label>
                <select name="jenis" class="form-control" required>
                    <option value="Core" {{ old('jenis') == 'Core' ? 'selected' : '' }}>Inti</option>
                    <option value="Elective Core" {{ old('jenis') == 'Elective Core' ? 'selected' : '' }}>Pilihan Inti</option>
                    <option value="Supporting" {{ old('jenis') == 'Supporting' ? 'selected' : '' }}>Pendukung</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Course</button>
        </div>
    </form>
</div>

<div id="modal-edit" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Edit Course</h3>
        <button type="button" onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>

    <form id="edit-course-form" method="POST" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Course Code <span class="required">*</span></label>
            <input id="edit-kode" type="text" name="kode" class="form-control" required>
        </div>

        <div class="form-group">
            <label class="form-label">Course Name <span class="required">*</span></label>
            <input id="edit-nama" type="text" name="nama" class="form-control" required>
        </div>

        <div class="form-group">
            <label class="form-label">Status <span class="required">*</span></label>
            <select id="edit-status" name="status" class="form-control" onchange="toggleEditStatus(this)" required>
                <option value="Auto">Automatic (Passed / Failed)</option>
                <option value="Not Taken">Not Taken</option>
            </select>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Credits <span class="required">*</span></label>
                <input id="edit-sks" type="number" name="sks" class="form-control" min="1" max="10" required>
            </div>
            <div class="form-group">
                <label class="form-label">Grade <span class="required">*</span></label>
                <select id="edit-nilai-0" name="nilai[]" class="form-control">
                    @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
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
            <label class="form-check">
                <input type="checkbox" id="edit-repeat-course" onchange="toggleEditRepeat(this)">
                Course diambil lebih dari satu kali
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
                        @foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
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
            <label class="form-label">Course Type <span class="required">*</span></label>
            <select id="edit-jenis" name="jenis" class="form-control" required>
                <option value="Core">Inti</option>
                <option value="Elective Core">Pilihan Inti</option>
                <option value="Supporting">Pendukung</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Course</button>
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
        if (modal && editModal && deleteModal && overlay) {
            modal.classList.remove('show');
            editModal.classList.remove('show');
            deleteModal.classList.remove('show');
            overlay.classList.remove('show');
        }
    }

    function openDeleteModal(code) {
        const course = courses.find(item => item.kode === code);
        if (!course) return;

        document.getElementById('delete-course-text').textContent = `Course "${course.nama}" (${course.kode}) will be deleted permanently.`;
        document.getElementById('delete-course-form').action = `/kuliah/courses/${encodeURIComponent(course.kode)}`;
        document.getElementById('modal-delete').classList.add('show');
        document.getElementById('modal-overlay').classList.add('show');
    }

    function openEditModal(code) {
        const course = courses.find(item => item.kode === code);
        if (!course) return;

        const entries = Array.isArray(course.nilai) ? course.nilai : [];
        document.getElementById('edit-kode').value = course.kode;
        document.getElementById('edit-nama').value = course.nama;
        document.getElementById('edit-sks').value = course.sks;
        document.getElementById('edit-jenis').value = course.jenis;
        document.getElementById('edit-status').value = course.status === 'Not Taken' ? 'Not Taken' : 'Auto';
        document.getElementById('edit-course-form').action = `/kuliah/courses/${encodeURIComponent(course.kode)}`;

        entries.slice(0, 3).forEach((entry, index) => {
            const value = typeof entry === 'object' ? entry : {
                semester: '',
                grade: entry
            };
            document.getElementById(`edit-semester-${index}`).value = value.semester || '1';
            document.getElementById(`edit-nilai-${index}`).value = value.grade || 'F';
        });

        const hasFirstRepeat = entries.length > 1;
        const hasSecondRepeat = entries.length > 2;
        document.getElementById('edit-repeat-course').checked = hasFirstRepeat;
        document.getElementById('edit-repeat-second').checked = hasSecondRepeat;
        toggleEditStatus(document.getElementById('edit-status'));
        toggleEditRepeat(document.getElementById('edit-repeat-course'));
        toggleEditSecondRepeat(document.getElementById('edit-repeat-second'));

        document.getElementById('modal-edit').classList.add('show');
        document.getElementById('modal-overlay').classList.add('show');
    }

    function toggleEditRepeat(checkbox) {
        const fields = document.getElementById('edit-repeat-fields');
        fields.style.display = checkbox.checked ? 'block' : 'none';
        document.querySelectorAll('#edit-attempt-1 select').forEach(select => {
            select.disabled = !checkbox.checked;
        });
        if (!checkbox.checked) {
            document.getElementById('edit-repeat-second').checked = false;
        }
        toggleEditSecondRepeat(document.getElementById('edit-repeat-second'));
    }

    function toggleCreateStatus(select) {
        const taken = select.value === 'Auto';
        document.getElementById('create-semester-0').disabled = !taken;
        document.getElementById('create-nilai-0').disabled = !taken;
        document.getElementById('repeat-course').disabled = !taken;
        if (!taken) {
            document.getElementById('repeat-course').checked = false;
        }
        toggleRepeatCourse(document.getElementById('repeat-course'));
    }

    function toggleEditStatus(select) {
        const taken = select.value === 'Auto';
        document.querySelectorAll('#modal-edit select[name="semester[]"], #modal-edit select[name="nilai[]"]').forEach(field => {
            field.disabled = !taken;
        });
        document.getElementById('edit-repeat-course').disabled = !taken;
        document.getElementById('edit-repeat-second').disabled = !taken;
        if (!taken) {
            document.getElementById('edit-repeat-course').checked = false;
            document.getElementById('edit-repeat-second').checked = false;
        }
        toggleEditRepeat(document.getElementById('edit-repeat-course'));
    }

    function toggleEditSecondRepeat(checkbox) {
        const fields = document.getElementById('edit-attempt-2');
        fields.style.display = checkbox.checked ? 'grid' : 'none';
        fields.querySelectorAll('select').forEach(select => {
            select.disabled = !checkbox.checked;
        });
    }

    function toggleRepeatCourse(checkbox) {
        const fields = document.getElementById('repeat-course-fields');
        fields.style.display = checkbox.checked ? 'block' : 'none';
        document.querySelectorAll('#repeat-course-fields > .form-grid-2 select').forEach(select => {
            select.disabled = !checkbox.checked;
        });

        const secondCheckbox = document.getElementById('repeat-course-second');
        if (!checkbox.checked) {
            secondCheckbox.checked = false;
        }
        toggleSecondRepeat(secondCheckbox);
    }

    function toggleSecondRepeat(checkbox) {
        const fields = document.getElementById('repeat-course-second-fields');
        fields.style.display = checkbox.checked ? 'grid' : 'none';
        fields.querySelectorAll('select').forEach(select => {
            select.disabled = !checkbox.checked;
        });
    }

    function filterCourses(query) {
        const normalizedQuery = query.trim().toLowerCase();
        const rows = document.querySelectorAll('.course-row');
        let visibleRows = 0;

        rows.forEach(row => {
            const searchableText = `${row.cells[0].textContent} ${row.cells[1].textContent}`.toLowerCase();
            const isVisible = searchableText.includes(normalizedQuery);
            row.style.display = isVisible ? '' : 'none';
            visibleRows += isVisible ? 1 : 0;
        });

        document.getElementById('course-empty-state').style.display = visibleRows ? 'none' : '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modal-create');
        const editModal = document.getElementById('modal-edit');
        const deleteModal = document.getElementById('modal-delete');
        const overlay = document.getElementById('modal-overlay');
        if (modal && editModal && deleteModal && overlay) {
            modal.classList.remove('show');
            editModal.classList.remove('show');
            deleteModal.classList.remove('show');
            overlay.classList.remove('show');
        }
        toggleCreateStatus(document.getElementById('create-status'));
        document.getElementById('course-search').addEventListener('input', function() {
            filterCourses(this.value);
        });
    });
</script>
@endsection