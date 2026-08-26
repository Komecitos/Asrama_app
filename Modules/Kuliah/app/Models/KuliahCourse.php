<?php

namespace Modules\Kuliah\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuliahCourse extends Model
{
    protected $table = 'kuliah_courses';

    protected $fillable = [
        'semester_id',
        'kode',
        'nama',
        'ruangan',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'sks',
        'jp',
        'nilai',
        'status',
        'jenis',
        'dosen',
    ];

    protected $casts = [
        'nilai' => 'array',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
