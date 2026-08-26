<?php

namespace Modules\Asrama\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Asrama\Models\AsramaKamar;
use Modules\Asrama\Models\AsramaPenghuni;
use Modules\Asrama\Models\AsramaKeuangan;
use Modules\Asrama\Models\AsramaIuran;

class AsramaController extends Controller
{
    private function syncKamarStatus($kamarId)
    {
        if (!$kamarId) return;
        $kamar = AsramaKamar::find($kamarId);
        if ($kamar && !in_array($kamar->status, ['Perbaikan', 'Gudang'])) {
            $activeCount = $kamar->penghunis()->where('status_penghuni', 'Aktif')->count();
            $status = ($activeCount >= $kamar->kapasitas) ? 'Penuh' : 'Tersedia';
            $kamar->update(['status' => $status]);
        }
    }

    public function data()
    {
        $kamars = AsramaKamar::with('penghunis')->orderBy('nomor_kamar')->get();
        $penghunis = AsramaPenghuni::with('kamar')->orderBy('nama')->get();

        // Auto-sync room statuses based on active occupant counts
        foreach ($kamars as $k) {
            if (!in_array($k->status, ['Perbaikan', 'Gudang'])) {
                $activeCount = $k->penghunis->where('status_penghuni', 'Aktif')->count();
                $expectedStatus = ($activeCount >= $k->kapasitas) ? 'Penuh' : 'Tersedia';
                if ($k->status !== $expectedStatus) {
                    $k->update(['status' => $expectedStatus]);
                    $k->status = $expectedStatus;
                }
            }
        }

        $totalKamar = $kamars->count();
        $totalKapasitasBett = $kamars->sum('kapasitas');
        $totalPenghuniAktif = $penghunis->where('status_penghuni', 'Aktif')->count();
        $slotTersedia = max(0, $totalKapasitasBett - $totalPenghuniAktif);
        $kamarTersedia = $kamars->filter(function ($k) {
            $active = $k->penghunis->where('status_penghuni', 'Aktif')->count();
            return !in_array($k->status, ['Perbaikan', 'Gudang']) && $active < $k->kapasitas;
        })->count();
        $kamarPenuh = $kamars->where('status', 'Penuh')->count();

        $summary = [
            'total_kamar' => $totalKamar,
            'total_kapasitas' => $totalKapasitasBett,
            'total_penghuni' => $totalPenghuniAktif,
            'slot_tersedia' => $slotTersedia,
            'kamar_tersedia' => $kamarTersedia,
            'kamar_penuh' => $kamarPenuh,
        ];

        return view('asrama::data', compact('kamars', 'penghunis', 'summary'));
    }

    public function storePenghuni(Request $request)
    {
        $validated = $request->validate([
            'kamar_id' => 'nullable|exists:asrama_kamars,id',
            'nama' => 'required|string|max:255',
            'nomor_hp' => 'nullable|string|max:50',
            'kampus' => 'nullable|string|max:255',
            'asal_kampung' => 'nullable|string|max:255',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);
        $validated['status_penghuni'] = 'Aktif';

        $penghuni = AsramaPenghuni::create($validated);
        if ($penghuni->kamar_id) {
            $this->syncKamarStatus($penghuni->kamar_id);
        }

        return redirect()->route('asrama.data')->with('success', 'Data Penghuni berhasil ditambahkan!');
    }

    public function updatePenghuni(Request $request, $id)
    {
        $penghuni = AsramaPenghuni::findOrFail($id);
        $oldKamarId = $penghuni->kamar_id;

        $validated = $request->validate([
            'kamar_id' => 'nullable|exists:asrama_kamars,id',
            'nama' => 'required|string|max:255',
            'nomor_hp' => 'nullable|string|max:50',
            'kampus' => 'nullable|string|max:255',
            'asal_kampung' => 'nullable|string|max:255',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        $penghuni->update($validated);

        if ($oldKamarId) $this->syncKamarStatus($oldKamarId);
        if ($penghuni->kamar_id) $this->syncKamarStatus($penghuni->kamar_id);

        return redirect()->route('asrama.data')->with('success', 'Data Penghuni berhasil diperbarui!');
    }

    public function keluarPenghuni(Request $request, $id)
    {
        $penghuni = AsramaPenghuni::findOrFail($id);
        $tglKeluar = $request->input('tanggal_keluar') ?: date('Y-m-d');
        $oldKamarId = $penghuni->kamar_id;

        $penghuni->update([
            'status_penghuni' => 'Keluar',
            'tanggal_keluar' => $tglKeluar,
        ]);

        if ($oldKamarId) {
            $this->syncKamarStatus($oldKamarId);
        }

        return redirect()->route('asrama.data')->with('success', 'Penghuni ' . $penghuni->nama . ' telah ditandai keluar asrama!');
    }

    public function destroyPenghuni($id)
    {
        $penghuni = AsramaPenghuni::findOrFail($id);
        $oldKamarId = $penghuni->kamar_id;
        $penghuni->delete();

        if ($oldKamarId) {
            $this->syncKamarStatus($oldKamarId);
        }

        return redirect()->route('asrama.data')->with('success', 'Data Penghuni berhasil dihapus!');
    }

    public function keuangan()
    {
        $keuangans = AsramaKeuangan::with('penghuni')->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();
        $penghunis = AsramaPenghuni::where('status_penghuni', 'Aktif')->orderBy('nama')->get();

        $totalPemasukan = $keuangans->where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = $keuangans->where('tipe', 'pengeluaran')->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $summary = [
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'saldo_kas' => $saldoKas,
        ];

        return view('asrama::keuangan', compact('keuangans', 'penghunis', 'summary'));
    }

    public function matriksKeuangan(Request $request)
    {
        $tahun = (int) ($request->get('tahun', date('Y')));
        $availableYears = range(date('Y') - 2, date('Y') + 2);

        $penghunis = AsramaPenghuni::orderBy('status_penghuni', 'asc')->orderBy('nama', 'asc')->get();
        $penghuniAktif = $penghunis->where('status_penghuni', 'Aktif');
        $penghuniKeluar = $penghunis->where('status_penghuni', 'Keluar');

        $iurans = AsramaIuran::where('tahun', $tahun)->get();

        $iuranMap = [];
        foreach ($iurans as $iuran) {
            if ($iuran->penghuni_id) {
                $iuranMap['penghuni_' . $iuran->penghuni_id . '_' . $iuran->bulan] = $iuran;
            } elseif ($iuran->fasilitas_key) {
                $iuranMap['fasilitas_' . $iuran->fasilitas_key . '_' . $iuran->bulan] = $iuran;
            }
        }

        $totalTerbayarTahunIni = $iurans->whereNotNull('penghuni_id')->sum('nominal');
        $currentMonthNum = (int) date('n');
        $lunasBulanIniCount = $iurans->where('bulan', $currentMonthNum)->whereNotNull('penghuni_id')->where('nominal', '>', 0)->count();

        $statsMatriks = [
            'total_terbayar' => $totalTerbayarTahunIni,
            'lunas_bulan_ini' => $lunasBulanIniCount,
            'total_aktif' => $penghuniAktif->count(),
        ];

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('asrama::matriks', compact('tahun', 'availableYears', 'penghuniAktif', 'penghuniKeluar', 'iuranMap', 'bulanNames', 'statsMatriks'));
    }

    public function updateMatriksIuran(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|min:1|max:12',
            'penghuni_id' => 'nullable|exists:asrama_penghunis,id',
            'fasilitas_key' => 'nullable|string|in:wifi,sampah',
            'nominal' => 'required|integer|min:0',
            'status_lunas' => 'nullable',
        ]);

        $statusLunas = $request->has('status_lunas') ? (bool) $request->input('status_lunas') : ($validated['nominal'] > 0);

        AsramaIuran::updateOrCreate(
            [
                'tahun' => $validated['tahun'],
                'bulan' => $validated['bulan'],
                'penghuni_id' => $validated['penghuni_id'] ?: null,
                'fasilitas_key' => $validated['fasilitas_key'] ?: null,
            ],
            [
                'nominal' => $validated['nominal'],
                'status_lunas' => $statusLunas,
            ]
        );

        return redirect()->back()->with('success', 'Data matriks iuran berhasil diperbarui!');
    }

    public function storeKamar(Request $request)
    {
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:50',
            'lantai' => 'required|integer|in:1,2',
            'kapasitas' => 'required|integer|min:1',
            'status' => 'required|in:Tersedia,Penuh,Perbaikan,Gudang',
            'fasilitas' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);
        $validated['harga_per_bulan'] = 0;

        AsramaKamar::create($validated);

        return redirect()->route('asrama.data')->with('success', 'Data Kamar berhasil ditambahkan!');
    }

    public function updateKamar(Request $request, $id)
    {
        $kamar = AsramaKamar::findOrFail($id);

        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:50',
            'lantai' => 'required|integer|in:1,2',
            'kapasitas' => 'required|integer|min:1',
            'status' => 'required|in:Tersedia,Penuh,Perbaikan,Gudang',
            'fasilitas' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);
        $validated['harga_per_bulan'] = 0;

        $kamar->update($validated);

        return redirect()->route('asrama.data')->with('success', 'Data Kamar berhasil diperbarui!');
    }

    public function destroyKamar($id)
    {
        $kamar = AsramaKamar::findOrFail($id);
        $kamar->delete();

        return redirect()->route('asrama.data')->with('success', 'Data Kamar berhasil dihapus!');
    }
}
