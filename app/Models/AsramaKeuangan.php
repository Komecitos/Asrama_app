<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsramaKeuangan extends Model
{
    protected $table = 'asrama_keuangans';

    protected $fillable = [
        'tipe',
        'kategori',
        'nominal',
        'tanggal',
        'penghuni_id',
        'keterangan',
    ];

    public function penghuni()
    {
        return $this->belongsTo(AsramaPenghuni::class, 'penghuni_id');
    }
}
