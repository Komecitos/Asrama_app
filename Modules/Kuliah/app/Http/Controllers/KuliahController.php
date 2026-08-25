<?php

namespace Modules\Kuliah\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KuliahController extends Controller
{
    private function defaultCourses(): array
    {
        return [
            ['kode' => 'MK-101', 'nama' => 'Web Programming', 'sks' => 3, 'nilai' => ['A', 'A'], 'status' => 'Passed', 'jenis' => 'Core', 'dosen' => 'Budi Santoso'],
            ['kode' => 'MK-102', 'nama' => 'Database Systems', 'sks' => 3, 'nilai' => ['A', 'B'], 'status' => 'Passed', 'jenis' => 'Core', 'dosen' => 'Siti Aminah'],
            ['kode' => 'MK-103', 'nama' => 'Data Structures', 'sks' => 2, 'nilai' => ['B'], 'status' => 'In Progress', 'jenis' => 'Elective Core', 'dosen' => 'Andi Pratama'],
            ['kode' => 'MK-104', 'nama' => 'English Language', 'sks' => 2, 'nilai' => ['B', 'A'], 'status' => 'Passed', 'jenis' => 'Supporting', 'dosen' => 'Rina Wijaya'],
        ];
    }

    private function getCourses(): array
    {
        $courses = session('kuliah_courses');

        if (!is_array($courses)) {
            $courses = $this->defaultCourses();
            $courses = array_map(function ($course) {
                $course['nilai'] = $this->normalizeGradeEntries($course['nilai'] ?? []);
                return $course;
            }, $courses);
            $courses = $this->applyStatuses($courses);
            session()->put('kuliah_courses', $courses);
            return $courses;
        }

        $normalizedCourses = array_map(function ($course) {
            if (!isset($course['nilai'])) {
                return $course;
            }

            $course['nilai'] = $this->normalizeGradeEntries($course['nilai']);

            return $course;
        }, $courses);

        $normalizedCourses = $this->applyStatuses($normalizedCourses);

        session()->put('kuliah_courses', $normalizedCourses);

        return $normalizedCourses;
    }

    private function normalizeGrades($value): array
    {
        $rawGrades = is_array($value)
            ? $value
            : ((preg_split('/[,|\n]/', (string) $value) ?: []));

        $grades = array_values(array_filter(array_map('trim', $rawGrades), fn($grade) => $grade !== ''));

        return array_values(array_map(function ($grade) {
            $normalized = strtoupper(trim((string) $grade));

            if (in_array($normalized, ['A', 'B', 'C', 'D', 'E', 'F'], true)) {
                return $normalized;
            }

            if (is_numeric($normalized)) {
                $numericValue = (float) $normalized;

                if ($numericValue >= 85) return 'A';
                if ($numericValue >= 75) return 'B';
                if ($numericValue >= 65) return 'C';
                if ($numericValue >= 55) return 'D';
                if ($numericValue >= 45) return 'E';
                return 'F';
            }

            return 'F';
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
        $gradeOrder = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 0];
        $grades = $this->gradesFromCourse($course);

        if (empty($grades)) {
            return 'F';
        }

        return collect($grades)->sortByDesc(fn($grade) => $gradeOrder[strtoupper($grade)] ?? 0)->first();
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

    public function matakuliah()
    {
        $matakuliah = $this->getCourses();
        $passedCourses = array_filter($matakuliah, fn($course) => strtolower($course['status'] ?? '') === 'passed');
        $totalCourseCredits = array_sum(array_column($matakuliah, 'sks'));
        $dGradeCredits = array_sum(array_map(function ($course) {
            return $this->bestGrade($course) === 'D' ? (int) ($course['sks'] ?? 0) : 0;
        }, $matakuliah));

        $summary = [
            'all' => array_sum(array_column($passedCourses, 'sks')),
            'core' => array_sum(array_column(array_filter($passedCourses, fn($course) => strtolower($course['jenis'] ?? '') === 'core'), 'sks')),
            'elective' => array_sum(array_column(array_filter($passedCourses, fn($course) => strtolower($course['jenis'] ?? '') === 'elective core'), 'sks')),
            'supporting' => array_sum(array_column(array_filter($passedCourses, fn($course) => strtolower($course['jenis'] ?? '') === 'supporting'), 'sks')),
            'd_credits' => $dGradeCredits,
            'd_percentage' => $totalCourseCredits > 0 ? round(($dGradeCredits / $totalCourseCredits) * 100, 1) : 0,
        ];

        return view('kuliah::matakuliah', compact('matakuliah', 'summary'));
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
            'sks' => 'required|integer|min:1|max:10',
            'nilai' => 'nullable|array|max:3',
            'nilai.*' => 'nullable|in:A,B,C,D,E,F',
            'semester' => 'nullable|array|max:3',
            'semester.*' => 'nullable|integer|min:1|max:14|distinct',
            'status' => 'required|in:Auto,Not Taken',
            'jenis' => 'required|in:Core,Elective Core,Supporting',
        ]);

        if ($validated['status'] === 'Auto' && empty($validated['nilai'])) {
            return back()->withErrors(['nilai' => 'Select at least one grade for a course that has been taken.'])->withInput();
        }

        $courses = $this->getCourses();
        $courseCode = strtoupper($validated['kode']);
        $gradeEntries = array_map(function ($grade, $index) use ($validated) {
            $semester = $validated['semester'][$index] ?? null;

            return [
                'semester' => $semester !== '' && $semester !== null ? (int) $semester : null,
                'grade' => $grade,
            ];
        }, $validated['nilai'] ?? [], array_keys($validated['nilai'] ?? []));
        $courseIndex = collect($courses)->search(fn($course) => strtoupper($course['kode']) === $courseCode);

        if ($courseIndex !== false) {
            $existingEntries = $courses[$courseIndex]['nilai'] ?? [];
            foreach ($gradeEntries as $gradeEntry) {
                $entriesWithoutSemester = array_filter($existingEntries, fn($entry) => !is_array($entry) || ($entry['semester'] ?? null) !== $gradeEntry['semester']);
                $existingEntries = [...$entriesWithoutSemester, $gradeEntry];
            }
            $courses[$courseIndex]['nilai'] = $validated['status'] === 'Not Taken'
                ? []
                : $this->normalizeGradeEntries($existingEntries);
        } else {
            $courses[] = [
                'kode' => strtoupper($validated['kode']),
                'nama' => $validated['nama'],
                'sks' => (int) $validated['sks'],
                'nilai' => $gradeEntries,
                'status' => $validated['status'] === 'Not Taken' ? 'Not Taken' : 'Failed',
                'jenis' => $validated['jenis'],
                'dosen' => 'Not assigned',
            ];
        }

        $courses = $this->applyStatuses($courses);

        session()->put('kuliah_courses', $courses);

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
            'sks' => 'required|integer|min:1|max:10',
            'nilai' => 'nullable|array|max:3',
            'nilai.*' => 'nullable|in:A,B,C,D,E,F',
            'semester' => 'nullable|array|max:3',
            'semester.*' => 'nullable|integer|min:1|max:14|distinct',
            'status' => 'required|in:Auto,Not Taken',
            'jenis' => 'required|in:Core,Elective Core,Supporting',
        ]);

        if ($validated['status'] === 'Auto' && empty($validated['nilai'])) {
            return back()->withErrors(['nilai' => 'Select at least one grade for a course that has been taken.'])->withInput();
        }

        $courses = $this->getCourses();
        $index = collect($courses)->search(fn($item) => strtoupper($item['kode']) === strtoupper($kode));

        if ($index === false) {
            abort(404);
        }

        $courses[$index] = [
            'kode' => strtoupper($validated['kode']),
            'nama' => $validated['nama'],
            'sks' => (int) $validated['sks'],
            'nilai' => $validated['status'] === 'Not Taken'
                ? []
                : $this->normalizeGradeEntries(array_map(function ($grade, $index) use ($validated) {
                    $semester = $validated['semester'][$index] ?? null;

                    return ['semester' => $semester !== '' && $semester !== null ? (int) $semester : null, 'grade' => $grade];
                }, $validated['nilai'], array_keys($validated['nilai']))),
            'status' => $validated['status'] === 'Not Taken' ? 'Not Taken' : 'Failed',
            'jenis' => $validated['jenis'],
            'dosen' => $courses[$index]['dosen'] ?? 'Not assigned',
        ];

        $courses = $this->applyStatuses($courses);

        session()->put('kuliah_courses', $courses);

        return redirect()->route('kuliah.matakuliah')->with('success', 'Course updated successfully.');
    }

    public function destroy($kode)
    {
        $courses = $this->getCourses();
        $courses = array_values(array_filter($courses, fn($course) => strtoupper($course['kode']) !== strtoupper($kode)));

        session()->put('kuliah_courses', $courses);

        return redirect()->route('kuliah.matakuliah')->with('success', 'Course deleted successfully.');
    }
}
