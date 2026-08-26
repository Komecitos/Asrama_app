<?php

namespace Modules\Kuliah\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Kuliah\Models\KuliahCourse;
use Modules\Kuliah\Models\Semester;
use Modules\Kuliah\Models\SemesterCourse;

class KuliahController extends Controller
{
    private function getCourses(): array
    {
        return KuliahCourse::query()->orderBy('id')->get()->map(function ($course) {
            $payload = $course->toArray();
            $payload['nilai'] = $this->normalizeGradeEntries($payload['nilai'] ?? []);
            return $payload;
        })->all();
    }

    private function normalizeGrades($value): array
    {
        $rawGrades = is_array($value)
            ? $value
            : ((preg_split('/[,|\n]/', (string) $value) ?: []));

        $grades = array_values(array_filter(array_map('trim', $rawGrades), fn($grade) => $grade !== ''));

        $validGrades = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'E', 'F'];

        return array_values(array_map(function ($grade) use ($validGrades) {
            $normalized = strtoupper(trim((string) $grade));

            if (in_array($normalized, $validGrades, true)) {
                return $normalized;
            }

            if (is_numeric($normalized)) {
                $numericValue = (float) $normalized;

                if ($numericValue >= 85) return 'A';
                if ($numericValue >= 80) return 'A-';
                if ($numericValue >= 75) return 'B+';
                if ($numericValue >= 70) return 'B';
                if ($numericValue >= 65) return 'B-';
                if ($numericValue >= 60) return 'C+';
                if ($numericValue >= 55) return 'C';
                if ($numericValue >= 50) return 'C-';
                if ($numericValue >= 40) return 'D';
                if ($numericValue >= 30) return 'E';
                return 'F';
            }

            return $normalized;
        }, $grades));
    }

    private function normalizeGradeEntries($value, ?int $semester = null): array
    {
        if (!is_array($value)) {
            return [['semester' => $semester, 'grade' => $this->normalizeGrades($value)[0] ?? 'F']];
        }

        return array_values(array_map(function ($entry) use ($semester) {
            if (is_array($entry)) {
                return [
                    'semester' => isset($entry['semester']) ? (int) $entry['semester'] : $semester,
                    'grade' => $this->normalizeGrades($entry['grade'] ?? $entry['nilai'] ?? 'F')[0] ?? 'F',
                ];
            }

            return [
                'semester' => $semester,
                'grade' => $this->normalizeGrades($entry)[0] ?? 'F',
            ];
        }, $value));
    }

    private function gradesFromCourse(array $course): array
    {
        return array_map(
            fn($entry) => is_array($entry) ? ($entry['grade'] ?? 'F') : $entry,
            $course['nilai'] ?? []
        );
    }

    private function bestGrade(array $course): string
    {
        $gradeOrder = ['A' => 10, 'A-' => 9.5, 'B+' => 9, 'B' => 8, 'B-' => 7.5, 'C+' => 6.5, 'C' => 6, 'C-' => 5.5, 'D' => 3, 'E' => 2, 'F' => 1];
        $grades = $this->gradesFromCourse($course);

        if (empty($grades)) {
            return 'Belum Diambil';
        }

        return collect($grades)->sortByDesc(fn($grade) => $gradeOrder[strtoupper((string)$grade)] ?? 0)->first();
    }

    private function applyStatuses(array $courses): array
    {
        $totalCredits = array_sum(array_column($courses, 'sks'));
        $dCredits = array_sum(array_map(function ($course) {
            return $this->bestGrade($course) === 'D' ? (int) ($course['sks'] ?? 0) : 0;
        }, $courses));
        $dGradeAllowed = $totalCredits > 0 && ($dCredits / $totalCredits) < 0.2;

        return array_map(function ($course) use ($dGradeAllowed) {
            if (empty($course['nilai'])) {
                $course['status'] = 'Not Taken';
                return $course;
            }

            $bestGrade = $this->bestGrade($course);
            $course['status'] = in_array($bestGrade, ['A', 'B', 'C'], true)
                || ($bestGrade === 'D' && $dGradeAllowed)
                ? 'Passed'
                : 'Failed';

            return $course;
        }, $courses);
    }

    private function gradeValue(string $grade): int
    {
        return match (strtoupper($grade)) {
            'A' => 4,
            'B' => 3,
            'C' => 2,
            'D' => 1,
            'E' => 0,
            'F' => 0,
            default => 0,
        };
    }

    private function gradeLetterFromAverage(float $averagePoints): string
    {
        if ($averagePoints >= 3.5) return 'A';
        if ($averagePoints >= 2.5) return 'B';
        if ($averagePoints >= 1.5) return 'C';
        if ($averagePoints >= 0.5) return 'D';
        if ($averagePoints >= 0.25) return 'E';
        return 'F';
    }

    private function semesterIdFromInput(array $semesters): ?int
    {
        $semesterNumber = collect($semesters)
            ->filter(fn($semester) => $semester !== '' && $semester !== null)
            ->map(fn($semester) => (int) $semester)
            ->first();

        return $semesterNumber
            ? Semester::query()->where('number', $semesterNumber)->value('id')
            : null;
    }

    public function matakuliah(Request $request)
    {
        $allSemesters = Semester::query()->orderBy('number')->get();
        $matakuliah = $this->getCourses();

        $passedCourses = array_filter($matakuliah, function ($course) {
            $st = strtolower(trim($course['status'] ?? ''));
            return str_starts_with($st, 'lulus') || $st === 'passed';
        });
        $totalCourseCredits = array_sum(array_column($matakuliah, 'sks'));
        $dGradeCredits = array_sum(array_map(function ($course) {
            $st = strtolower(trim($course['status'] ?? ''));
            return ($st === 'lulus (d)' || $this->bestGrade($course) === 'D') ? (int) ($course['sks'] ?? 0) : 0;
        }, $matakuliah));

        $summary = [
            'all' => array_sum(array_column($passedCourses, 'sks')),
            'core' => array_sum(array_column(array_filter($passedCourses, fn($course) => in_array(strtolower($course['jenis'] ?? ''), ['core', 'wajib'], true)), 'sks')),
            'elective' => array_sum(array_column(array_filter($passedCourses, fn($course) => in_array(strtolower($course['jenis'] ?? ''), ['elective core', 'pilihan inti'], true)), 'sks')),
            'supporting' => array_sum(array_column(array_filter($passedCourses, fn($course) => in_array(strtolower($course['jenis'] ?? ''), ['supporting', 'pilihan pendukung'], true)), 'sks')),
            'd_credits' => $dGradeCredits,
            'd_percentage' => $totalCourseCredits > 0 ? round(($dGradeCredits / $totalCourseCredits) * 100, 1) : 0,
            'total_credits' => $totalCourseCredits,
            'total_courses' => count($matakuliah),
        ];

        return view('kuliah::matakuliah', compact('matakuliah', 'summary', 'allSemesters'));
    }

    public function jadwal(Request $request)
    {
        $allSemesters = Semester::query()->orderBy('number')->get();
        $hasCoursesSemesters = Semester::query()->whereHas('courses')->pluck('number')->toArray();

        $requestedSemester = (int) $request->input('semester');
        if ($requestedSemester >= 1 && $requestedSemester <= 14) {
            $currentSemester = $requestedSemester;
        } else {
            $currentSemester = !empty($hasCoursesSemesters) ? max($hasCoursesSemesters) : 9;
        }

        $semester = Semester::query()->where('number', $currentSemester)->first();
        $courses = $semester
            ? $semester->courses()->orderBy('id')->get()
            : collect();

        $jadwal = $courses;

        $summary = [
            'total_courses' => $courses->count(),
            'total_credits' => $courses->sum('sks'),
            'passed_courses' => $courses->where('status', 'Passed')->count(),
            'passed_credits' => $courses->where('status', 'Passed')->sum('sks'),
        ];

        return view('kuliah::jadwal', compact('jadwal', 'courses', 'summary', 'allSemesters', 'currentSemester', 'hasCoursesSemesters'));
    }

    public function semester(Semester $semester)
    {
        $courses = $semester->courses()->orderBy('id')->get();

        return view('kuliah::semester.show', compact('semester', 'courses'));
    }

    public function store(Request $request)
    {
        $semesters = $request->input('semester', []);
        $grades = $request->input('nilai', []);
        $request->merge([
            'semester' => is_array($semesters) ? $semesters : [$semesters],
            'nilai' => is_array($grades) ? $grades : [$grades],
        ]);

        $validated = $request->validate([
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'ruangan' => 'nullable|string|max:100',
            'dosen' => 'nullable|string|max:255',
            'hari' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'sks' => 'required|integer|min:1|max:10',
            'nilai' => 'nullable|array|max:5',
            'nilai.*' => 'nullable|in:A,A-,B+,B,B-,C+,C,C-,D,E,F',
            'semester' => 'nullable|array|max:5',
            'semester.*' => 'nullable|integer|min:1|max:14|distinct',
            'status' => 'required|string',
            'jenis' => 'required|in:Wajib,Pilihan Inti,Pilihan Pendukung,Core,Elective Core,Supporting',
        ], [
            'kode.required' => 'Kode Mata Kuliah wajib diisi.',
            'nama.required' => 'Nama Mata Kuliah wajib diisi.',
            'sks.required' => 'SKS wajib diisi.',
            'sks.integer' => 'SKS harus berupa angka antara 1-10.',
            'jenis.required' => 'Kategori Mata Kuliah wajib dipilih.',
        ]);

        $rawGrades = array_values(array_filter($validated['nilai'] ?? [], fn($g) => $g !== null && $g !== ''));

        if (($validated['status'] === 'Auto' || $validated['status'] === 'Lulus' || $validated['status'] === 'Belum Lulus') && empty($rawGrades)) {
            return back()->withErrors(['nilai' => 'Pilih minimal satu nilai untuk mata kuliah yang sudah/pernah diambil.'])->withInput();
        }

        $semesterId = $this->semesterIdFromInput($validated['semester'] ?? [])
            ?? Semester::firstOrCreate(['number' => 1], ['name' => 'Semester 1'])->id;

        $jamSelesai = $validated['jam_selesai']
            ?? (!empty($validated['jam_mulai']) ? Carbon::createFromFormat('H:i', $validated['jam_mulai'])->addHours((int) $validated['sks'])->format('H:i') : null);

        if ($request->boolean('from_semester')) {
            $semNumber = Semester::where('id', $semesterId)->value('number') ?: 9;

            SemesterCourse::query()->create([
                'semester_id' => $semesterId,
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
                'sks' => (int) $validated['sks'],
                'jenis' => $validated['jenis'],
                'dosen' => $validated['dosen'] ?? null,
                'ruangan' => $validated['ruangan'] ?? null,
                'hari' => $validated['hari'] ?? null,
                'jam_mulai' => $validated['jam_mulai'] ?? null,
                'jam_selesai' => $jamSelesai,
            ]);

            // Sync to master KuliahCourse
            $kCourse = KuliahCourse::query()->whereRaw('UPPER(kode) = ?', [strtoupper($validated['kode'])])->first();
            if ($kCourse) {
                $kCourse->update([
                    'semester_id' => $semesterId,
                    'nama' => $validated['nama'],
                    'sks' => (int) $validated['sks'],
                    'jenis' => $validated['jenis'],
                    'dosen' => $validated['dosen'] ?? null,
                    'ruangan' => $validated['ruangan'] ?? null,
                    'hari' => $validated['hari'] ?? null,
                    'jam_mulai' => $validated['jam_mulai'] ?? null,
                    'jam_selesai' => $jamSelesai,
                ]);
            } else {
                KuliahCourse::create([
                    'semester_id' => $semesterId,
                    'kode' => strtoupper($validated['kode']),
                    'nama' => $validated['nama'],
                    'sks' => (int) $validated['sks'],
                    'jenis' => $validated['jenis'],
                    'dosen' => $validated['dosen'] ?? null,
                    'ruangan' => $validated['ruangan'] ?? null,
                    'hari' => $validated['hari'] ?? null,
                    'jam_mulai' => $validated['jam_mulai'] ?? null,
                    'jam_selesai' => $jamSelesai,
                    'status' => 'Belum Diambil',
                    'nilai' => [],
                ]);
            }

            return redirect()->route('kuliah.jadwal', ['tab' => 'matakuliah', 'semester' => $semNumber])
                ->with('success', 'Jadwal matakuliah berhasil ditambahkan.');
        }

        $courseCode = strtoupper($validated['kode']);
        $rawSemesters = $validated['semester'] ?? [];
        $gradeEntries = [];
        foreach ($rawGrades as $idx => $gVal) {
            $semVal = $rawSemesters[$idx] ?? null;
            $gradeEntries[] = [
                'semester' => $semVal !== '' && $semVal !== null ? (int) $semVal : null,
                'grade' => $gVal,
            ];
        }

        // Determine status
        if ($validated['status'] === 'Not Taken' || $validated['status'] === 'Belum Diambil' || empty($gradeEntries)) {
            $status = 'Belum Diambil';
            $gradeEntries = [];
        } else {
            $gradeOrder = ['A' => 10, 'A-' => 9.5, 'B+' => 9, 'B' => 8, 'B-' => 7.5, 'C+' => 6.5, 'C' => 6, 'C-' => 5.5, 'D' => 3, 'E' => 2, 'F' => 1];
            $bestVal = 0;
            $bestGrade = 'F';
            foreach ($gradeEntries as $gItem) {
                $gStr = strtoupper($gItem['grade'] ?? 'F');
                $val = $gradeOrder[$gStr] ?? 0;
                if ($val > $bestVal) {
                    $bestVal = $val;
                    $bestGrade = $gStr;
                }
            }
            if (in_array($bestGrade, ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-'], true)) {
                $status = 'Lulus';
            } elseif ($bestGrade === 'D') {
                $status = 'Lulus (D)';
            } else {
                $status = 'Belum Lulus';
            }
        }

        $course = KuliahCourse::query()->whereRaw('UPPER(kode) = ?', [$courseCode])->first();

        if ($course) {
            $course->update([
                'semester_id' => $semesterId,
                'kode' => $courseCode,
                'nama' => $validated['nama'],
                'ruangan' => $validated['ruangan'] ?? null,
                'dosen' => $validated['dosen'] ?? null,
                'hari' => $validated['hari'] ?? null,
                'jam_mulai' => $validated['jam_mulai'] ?? null,
                'jam_selesai' => $jamSelesai,
                'sks' => (int) $validated['sks'],
                'nilai' => $gradeEntries,
                'status' => $status,
                'jenis' => $validated['jenis'],
            ]);
        } else {
            KuliahCourse::query()->create([
                'semester_id' => $semesterId,
                'kode' => $courseCode,
                'nama' => $validated['nama'],
                'ruangan' => $validated['ruangan'] ?? null,
                'dosen' => $validated['dosen'] ?? null,
                'hari' => $validated['hari'] ?? null,
                'jam_mulai' => $validated['jam_mulai'] ?? null,
                'jam_selesai' => $jamSelesai,
                'sks' => (int) $validated['sks'],
                'nilai' => $gradeEntries,
                'status' => $status,
                'jenis' => $validated['jenis'],
            ]);
        }

        return redirect()->route('kuliah.matakuliah')->with('success', 'Matakuliah berhasil ditambahkan.');

        // Also sync to SemesterCourse if semester is set
        if ($semesterId) {
            $sc = SemesterCourse::query()->where('semester_id', $semesterId)->whereRaw('UPPER(kode) = ?', [$courseCode])->first();
            if ($sc) {
                $sc->update([
                    'nama' => $validated['nama'],
                    'sks' => (int) $validated['sks'],
                    'jenis' => $validated['jenis'],
                    'dosen' => $validated['dosen'] ?? null,
                    'ruangan' => $validated['ruangan'] ?? null,
                    'hari' => $validated['hari'] ?? null,
                    'jam_mulai' => $validated['jam_mulai'] ?? null,
                    'jam_selesai' => $jamSelesai,
                ]);
            } else {
                SemesterCourse::create([
                    'semester_id' => $semesterId,
                    'kode' => $courseCode,
                    'nama' => $validated['nama'],
                    'sks' => (int) $validated['sks'],
                    'jenis' => $validated['jenis'],
                    'dosen' => $validated['dosen'] ?? null,
                    'ruangan' => $validated['ruangan'] ?? null,
                    'hari' => $validated['hari'] ?? null,
                    'jam_mulai' => $validated['jam_mulai'] ?? null,
                    'jam_selesai' => $jamSelesai,
                ]);
            }
        }

        return redirect()->route('kuliah.matakuliah')->with('success', 'Course created successfully.');
    }

    public function update(Request $request, $kode)
    {
        $semester = $request->input('semester');
        $grade = $request->input('nilai');
        $request->merge([
            'semester' => is_array($semester) ? $semester : [$semester],
            'nilai' => is_array($grade) ? $grade : [$grade],
        ]);

        $validated = $request->validate([
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'ruangan' => 'nullable|string|max:100',
            'dosen' => 'nullable|string|max:255',
            'hari' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'sks' => 'required|integer|min:1|max:10',
            'nilai' => 'nullable|array|max:5',
            'nilai.*' => 'nullable|in:A,A-,B+,B,B-,C+,C,C-,D,E,F',
            'semester' => 'nullable|array|max:5',
            'semester.*' => 'nullable|integer|min:1|max:14|distinct',
            'status' => 'required|string',
            'jenis' => 'required|in:Wajib,Pilihan Inti,Pilihan Pendukung,Core,Elective Core,Supporting',
        ]);

        $course = KuliahCourse::query()->whereRaw('UPPER(kode) = ?', [strtoupper($kode)])->first();

        if (!$course) {
            abort(404);
        }

        $rawGrades = array_values(array_filter($validated['nilai'] ?? [], fn($g) => $g !== null && $g !== ''));

        if (($validated['status'] === 'Auto' || $validated['status'] === 'Lulus' || $validated['status'] === 'Belum Lulus') && empty($rawGrades)) {
            return back()->withErrors(['nilai' => 'Pilih minimal satu nilai untuk mata kuliah yang sudah/pernah diambil.'])->withInput();
        }

        if ($validated['status'] === 'Not Taken' || $validated['status'] === 'Belum Diambil') {
            $nilaiEntries = [];
            $status = 'Belum Diambil';
        } else {
            $rawSemesters = $validated['semester'] ?? [];
            $nilaiEntries = [];
            foreach ($rawGrades as $idx => $gVal) {
                $semVal = $rawSemesters[$idx] ?? null;
                $nilaiEntries[] = [
                    'semester' => $semVal !== '' && $semVal !== null ? (int) $semVal : null,
                    'grade' => $gVal,
                ];
            }

            // Automatic status determination from best grade
            $gradeOrder = ['A' => 10, 'A-' => 9.5, 'B+' => 9, 'B' => 8, 'B-' => 7.5, 'C+' => 6.5, 'C' => 6, 'C-' => 5.5, 'D' => 3, 'E' => 2, 'F' => 1];
            $bestVal = 0;
            $bestGrade = 'F';
            foreach ($nilaiEntries as $gItem) {
                $gStr = strtoupper($gItem['grade'] ?? 'F');
                $val = $gradeOrder[$gStr] ?? 0;
                if ($val > $bestVal) {
                    $bestVal = $val;
                    $bestGrade = $gStr;
                }
            }

            if (in_array($bestGrade, ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-'], true)) {
                $status = 'Lulus';
            } elseif ($bestGrade === 'D') {
                $status = 'Lulus (D)';
            } else {
                $status = 'Belum Lulus';
            }
        }

        $course->update([
            'semester_id' => $this->semesterIdFromInput($validated['semester'] ?? []),
            'kode' => strtoupper($validated['kode']),
            'nama' => $validated['nama'],
            'ruangan' => $validated['ruangan'] ?? null,
            'dosen' => $validated['dosen'] ?? null,
            'hari' => $validated['hari'] ?? null,
            'jam_mulai' => $validated['jam_mulai'] ?? null,
            'jam_selesai' => $validated['jam_selesai'] ?? null,
            'sks' => (int) $validated['sks'],
            'nilai' => $nilaiEntries,
            'status' => $status,
            'jenis' => $validated['jenis'],
        ]);

        return redirect()->route('kuliah.matakuliah')->with('success', 'Matakuliah berhasil diperbarui.');
    }

    public function updateSchedule(Request $request, SemesterCourse $semesterCourse)
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kuliah_semester_courses', 'kode')
                    ->where(fn($query) => $query->where('semester_id', $semesterCourse->semester_id))
                    ->ignore($semesterCourse->id),
            ],
            'nama' => 'required|string|max:255',
            'sks' => 'required|integer|min:1|max:10',
            'ruangan' => 'nullable|string|max:100',
            'dosen' => 'nullable|string|max:255',
            'hari' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
        ]);

        $jamSelesai = $validated['jam_selesai']
            ?? (!empty($validated['jam_mulai']) ? Carbon::createFromFormat('H:i', $validated['jam_mulai'])->addHours((int) $validated['sks'])->format('H:i') : null);

        $semesterCourse->update([
            ...$validated,
            'jam_selesai' => $jamSelesai,
            'kode' => strtoupper($validated['kode']),
        ]);

        return redirect()->route('kuliah.jadwal', ['tab' => 'matakuliah'])->with('success', 'Schedule updated successfully.');
    }

    public function destroySemesterCourse(SemesterCourse $semesterCourse)
    {
        $semesterCourse->delete();

        return redirect()->route('kuliah.jadwal', ['tab' => 'matakuliah'])
            ->with('success', 'Semester course deleted successfully.');
    }

    public function destroy($kode)
    {
        $courseModel = request()->boolean('from_semester') ? SemesterCourse::class : KuliahCourse::class;
        $courseModel::query()->whereRaw('UPPER(kode) = ?', [strtoupper($kode)])->delete();

        $redirectRoute = request()->boolean('from_semester')
            ? 'kuliah.jadwal'
            : 'kuliah.matakuliah';

        return redirect()->route(
            $redirectRoute,
            request()->boolean('from_semester') ? ['tab' => 'matakuliah'] : []
        )->with('success', 'Course deleted successfully.');
    }
}
