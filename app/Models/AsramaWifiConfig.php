<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsramaWifiConfig extends Model
{
    protected $fillable = [
        'ssid',
        'password',
        'bulan',
        'tahun',
        'catatan',
        'template_lunas',
        'template_tagihan',
    ];

    public static function getCurrentConfig($bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? (int) date('n');
        $tahun = $tahun ?? (int) date('Y');

        $config = self::where('bulan', $bulan)->where('tahun', $tahun)->first();
        if (!$config) {
            $config = self::latest()->first();
        }

        if (!$config) {
            $config = self::create([
                'ssid' => 'Asrama-Mahulu-HighSpeed',
                'password' => 'MahuluAktif#' . date('Y'),
                'bulan' => $bulan,
                'tahun' => $tahun,
                'catatan' => 'Password WiFi diperbarui setiap awal bulan.',
                'template_lunas' => "Halo *[NAMA]*, terima kasih telah melunasi iuran asrama bulan *[BULAN_TAHUN]*.\n\nBerikut akses WiFi asrama bulan ini:\n📡 *SSID:* [SSID]\n🔑 *Password:* [PASSWORD]\n\nHarap tidak membagikan password ini kepada pihak luar. Terima kasih! 🙏",
                'template_tagihan' => "Halo *[NAMA]*, mengingatkan bahwa iuran asrama bulan *[BULAN_TAHUN]* sebesar *Rp [TAGIHAN]* belum tercatat lunas.\n\nSilakan lakukan pembayaran agar akses password WiFi bulan ini dapat segera kami aktifkan dan bagikan. Terima kasih! 🙏",
            ]);
        }

        return $config;
    }
}
