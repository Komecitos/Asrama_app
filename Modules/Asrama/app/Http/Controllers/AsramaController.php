<?php

namespace Modules\Asrama\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Asrama\Models\AsramaKamar;
use Modules\Asrama\Models\AsramaPenghuni;
use Modules\Asrama\Models\AsramaKeuangan;

class AsramaController extends Controller
{
    public function data()
    {
        $kamars = AsramaKamar::with('penghunis')->orderBy('nomor_kamar')->get();
        $penghunis = AsramaPenghuni::with('kamar')->orderBy('nama')->get();

        $totalKamar = $kamars->count();
        $kamarTersedia = $kamars->where('status', 'Tersedia')->count();
        $kamarPenuh = $kamars->where('status', 'Penuh')->count();
        $totalPenghuni = $penghunis->where('status_penghuni', 'Aktif')->count();

        $summary = [
            'total_kamar' => $totalKamar,
            'kamar_tersedia' => $kamarTersedia,
            'kamar_penuh' => $kamarPenuh,
            'total_penghuni' => $totalPenghuni,
        ];

        return view('asrama::data', compact('kamars', 'penghunis', 'summary'));
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

    public function storeKamar(Request $request)
    {
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:50',
            'lantai' => 'required|integer|min:1',
            'kapasitas' => 'required|integer|min:1',
            'harga_per_bulan' => 'required|numeric|min:0',
            'status' => 'required|in:Tersedia,Penuh,Perbaikan',
            'fasilitas' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        AsramaKamar::create($validated);

        return redirect()->route('asrama.data')->with('success', 'Data Kamar berhasil ditambahkan!');
    }

    public function updateKamar(Request $request, $id)
    {
        $kamar = AsramaKamar::findOrFail($id);

        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:50',
            'lantai' => 'required|integer|min:1',
            'kapasitas' => 'required|integer|min:1',
            'harga_per_bulan' => 'required|numeric|min:0',
            'status' => 'required|in:Tersedia,Penuh,Perbaikan',
            'fasilitas' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $kamar->update($validated);

        return redirect()->route('asrama.data')->with('success', 'Data Kamar berhasil diperbarui!');
    }

    public function destroyKamar($id)
    {
        $kamar = AsramaKamar::findOrFail($id);
        $kamar->delete();

        return redirect()->route('asrama.data')->with('success', 'Data Kamar berhasil dihapus!');
    }

    public function storePenghuni(Request $request)
    {
        $validated = $request->validate([
            'kamar_id' => 'nullable|exists:asrama_kamars,id',
            'nama' => 'required|string|max:255',
            'nomor_hp' => 'nullable|string|max:50',
            'status_penghuni' => 'required|in:Aktif,Keluar',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        AsramaPenghuni::create($validated);

        return redirect()->route('asrama.data')->with('success', 'Data Penghuni berhasil ditambahkan!');
    }

    public function updatePenghuni(Request $request, $id)
    {
        $penghuni = AsramaPenghuni::findOrFail($id);

        $validated = $request->validate([
            'kamar_id' => 'nullable|exists:asrama_kamars,id',
            'nama' => 'required|string|max:255',
            'nomor_hp' => 'nullable|string|max:50',
            'status_penghuni' => 'required|in:Aktif,Keluar',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        $penghuni->update($validated);

        return redirect()->route('asrama.data')->with('success', 'Data Penghuni berhasil diperbarui!');
    }

    public function destroyPenghuni($id)
    {
        $penghuni = AsramaPenghuni::findOrFail($id);
        $penghuni->delete();

        return redirect()->route('asrama.data')->with('success', 'Data Penghuni berhasil dihapus!');
    }

    public function storeKeuangan(Request $request)
    {
        $validated = $request->validate([
            'tipe' => 'required|in:pemasukan,pengeluaran',
            'kategori' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
            'penghuni_id' => 'nullable|exists:asrama_penghunis,id',
            'keterangan' => 'nullable|string',
        ]);

        AsramaKeuangan::create($validated);

        return redirect()->route('asrama.keuangan')->with('success', 'Catatan Keuangan berhasil disimpan!');
    }

    public function destroyKeuangan($id)
    {
        $keuangan = AsramaKeuangan::findOrFail($id);
        $keuangan->delete();

        return redirect()->route('asrama.keuangan')->with('success', 'Catatan Keuangan berhasil dihapus!');
    }
}
