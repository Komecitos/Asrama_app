<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsramaIuran extends Model
{
    protected $table = 'asrama_iurans';

    protected $fillable = [
        'tahun',
        'bulan',
        'penghuni_id',
        'fasilitas_key',
        'nominal',
        'status_lunas',
    ];

    public function penghuni()
    {
        return $this->belongsTo(AsramaPenghuni::class, 'penghuni_id');
    }
}
