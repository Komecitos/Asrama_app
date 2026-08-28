<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Models\AsramaPenghuni;
use App\Models\AsramaKeuangan;
use App\Models\AsramaIuran;

class DashboardController extends Controller
{
    public function index()
    {
        $totalKamar = AsramaKamar::count();
        $kamarTersedia = AsramaKamar::where('status', 'Tersedia')->count();
        $kamarPenuh = AsramaKamar::where('status', 'Penuh')->count();
        $kamarPerbaikan = AsramaKamar::where('status', 'Perbaikan')->count();
        $kamarGudang = AsramaKamar::where('status', 'Gudang')->count();

        $totalPenghuni = AsramaPenghuni::where('status_penghuni', 'Aktif')->count();
        $penghuniKeluar = AsramaPenghuni::where('status_penghuni', 'Keluar')->count();

        $totalPemasukan = AsramaKeuangan::where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = AsramaKeuangan::where('tipe', 'pengeluaran')->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $recentTransactions = AsramaKeuangan::with('penghuni')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $kamars = AsramaKamar::with(['penghunis' => function ($q) {
            $q->where('status_penghuni', 'Aktif');
        }])->get();

        return view('dashboard.index', compact(
            'totalKamar',
            'kamarTersedia',
            'kamarPenuh',
            'kamarPerbaikan',
            'kamarGudang',
            'totalPenghuni',
            'penghuniKeluar',
            'totalPemasukan',
            'totalPengeluaran',
            'saldoKas',
            'recentTransactions',
            'kamars'
        ));
    }
}
