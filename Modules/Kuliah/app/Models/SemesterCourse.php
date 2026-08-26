<?php

namespace Modules\Kuliah\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterCourse extends Model
{
    protected $table = 'kuliah_semester_courses';

    protected $fillable = [
        'semester_id',
        'kode',
        'nama',
        'sks',
        'jenis',
        'dosen',
        'ruangan',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
