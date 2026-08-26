<?php

namespace Modules\Asrama\Models;

use Illuminate\Database\Eloquent\Model;

class AsramaKamar extends Model
{
    protected $table = 'asrama_kamars';

    protected $fillable = [
        'nomor_kamar',
        'lantai',
        'kapasitas',
        'harga_per_bulan',
        'status',
        'fasilitas',
        'catatan',
    ];

    public function penghunis()
    {
        return $this->hasMany(AsramaPenghuni::class, 'kamar_id');
    }

    public function activePenghunis()
    {
        return $this->hasMany(AsramaPenghuni::class, 'kamar_id')->where('status_penghuni', 'Aktif');
    }
}