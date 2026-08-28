<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsramaPenghuni extends Model
{
    protected $table = 'asrama_penghunis';

    protected $fillable = [
        'nama',
        'nomor_hp',
        'kampus',
        'asal_kampung',
        'kamar_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'status_penghuni',
        'catatan',
    ];

    public function kamar()
    {
        return $this->belongsTo(AsramaKamar::class, 'kamar_id');
    }

    public function iurans()
    {
        return $this->hasMany(AsramaIuran::class, 'penghuni_id');
    }
}
