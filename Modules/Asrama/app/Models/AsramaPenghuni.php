<?php

namespace Modules\Asrama\Models;

use Illuminate\Database\Eloquent\Model;

class AsramaPenghuni extends Model
{
    protected $table = 'asrama_penghunis';

    protected $fillable = [
        'kamar_id',
        'nama',
        'nomor_hp',
        'kampus',
        'asal_kampung',
        'status_penghuni',
        'tanggal_masuk',
        'tanggal_keluar',
        'catatan',
    ];

    public function kamar()
    {
        return $this->belongsTo(AsramaKamar::class, 'kamar_id');
    }

    public function keuangans()
    {
        return $this->hasMany(AsramaKeuangan::class, 'penghuni_id');
    }
}
