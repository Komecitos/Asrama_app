<?php

namespace Modules\Kuliah\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    protected $table = 'kuliah_semesters';

    protected $fillable = [
        'number',
        'name',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(SemesterCourse::class);
    }
}
