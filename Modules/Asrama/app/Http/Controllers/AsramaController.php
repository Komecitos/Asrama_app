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

    public function reactivatePenghuni($id)
    {
        $penghuni = AsramaPenghuni::findOrFail($id);
        $penghuni->update([
            'status_penghuni' => 'Aktif',
            'tanggal_keluar' => null,
        ]);

        if ($penghuni->kamar_id) {
            $this->syncKamarStatus($penghuni->kamar_id);
        }

        return redirect()->route('asrama.data')->with('success', 'Penghuni ' . $penghuni->nama . ' berhasil diaktifkan kembali sebagai penghuni aktif!');
    }

    public function destroyPenghuni($id)
    {
        $penghuni = AsramaPenghuni::findOrFail($id);
        $oldKamarId = $penghuni->kamar_id;

        // Delete all associated iuran matrix & cash transaction records for this resident
        AsramaIuran::where('penghuni_id', $id)->delete();
        AsramaKeuangan::where('penghuni_id', $id)->delete();

        $penghuni->delete();

        if ($oldKamarId) {
            $this->syncKamarStatus($oldKamarId);
        }

        return redirect()->route('asrama.data')->with('success', 'Data Penghuni beserta riwayat iurannya berhasil dihapus!');
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
        $startYear = 2026;
        $currentYear = (int) date('Y');
        $tahun = max($startYear, (int) ($request->get('tahun', $currentYear)));
        $tarifDefault = (int) ($request->get('tarif_default', session('asrama_tarif_default', 100000)));
        session(['asrama_tarif_default' => $tarifDefault]);

        $endYear = max($startYear, $currentYear) + 2;
        $availableYears = range($startYear, $endYear);

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
            'tarif_default' => $tarifDefault,
        ];

        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return view('asrama::matriks', compact('tahun', 'tarifDefault', 'availableYears', 'penghuniAktif', 'penghuniKeluar', 'iuranMap', 'bulanNames', 'statsMatriks'));
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
        $bulanPadded = str_pad($validated['bulan'], 2, '0', STR_PAD_LEFT);
        $txDate = "{$validated['tahun']}-{$bulanPadded}-01";

        // 1. UPDATE OR CREATE CORRESPONDING CASH TRANSACTION IN asrama_keuangans
        if ($validated['fasilitas_key'] === 'wifi') {
            if ($statusLunas) {
                $nom = $validated['nominal'] > 0 ? $validated['nominal'] : 383000;
                AsramaKeuangan::updateOrCreate(
                    [
                        'tanggal' => $txDate,
                        'kategori' => 'Pembayaran WiFi',
                    ],
                    [
                        'tipe' => 'pengeluaran',
                        'nominal' => $nom,
                        'keterangan' => 'Pembayaran WiFi Bulan ' . $validated['bulan'] . '/' . $validated['tahun'],
                    ]
                );
                AsramaIuran::updateOrCreate(
                    ['tahun' => $validated['tahun'], 'bulan' => $validated['bulan'], 'fasilitas_key' => 'wifi'],
                    ['nominal' => $nom, 'status_lunas' => true]
                );
            } else {
                AsramaKeuangan::whereYear('tanggal', $validated['tahun'])
                    ->whereMonth('tanggal', $validated['bulan'])
                    ->where('kategori', 'Pembayaran WiFi')
                    ->delete();
                AsramaIuran::where('tahun', $validated['tahun'])->where('bulan', $validated['bulan'])->where('fasilitas_key', 'wifi')->delete();
            }
        } elseif ($validated['fasilitas_key'] === 'sampah') {
            if ($statusLunas) {
                $nom = $validated['nominal'] > 0 ? $validated['nominal'] : 125000;
                AsramaKeuangan::updateOrCreate(
                    [
                        'tanggal' => $txDate,
                        'kategori' => 'Pembayaran Sampah',
                    ],
                    [
                        'tipe' => 'pengeluaran',
                        'nominal' => $nom,
                        'keterangan' => 'Pembayaran Sampah Bulan ' . $validated['bulan'] . '/' . $validated['tahun'],
                    ]
                );
                AsramaIuran::updateOrCreate(
                    ['tahun' => $validated['tahun'], 'bulan' => $validated['bulan'], 'fasilitas_key' => 'sampah'],
                    ['nominal' => $nom, 'status_lunas' => true]
                );
            } else {
                AsramaKeuangan::whereYear('tanggal', $validated['tahun'])
                    ->whereMonth('tanggal', $validated['bulan'])
                    ->where('kategori', 'Pembayaran Sampah')
                    ->delete();
                AsramaIuran::where('tahun', $validated['tahun'])->where('bulan', $validated['bulan'])->where('fasilitas_key', 'sampah')->delete();
            }
        } elseif (!empty($validated['penghuni_id'])) {
            $penghuni = AsramaPenghuni::find($validated['penghuni_id']);
            $namaPenghuni = $penghuni ? $penghuni->nama : 'Penghuni';

            if ($validated['nominal'] > 0) {
                $existingTx = AsramaKeuangan::where('penghuni_id', $validated['penghuni_id'])
                    ->whereYear('tanggal', $validated['tahun'])
                    ->whereMonth('tanggal', $validated['bulan'])
                    ->where('kategori', 'Iuran Bulanan')
                    ->first();

                if ($existingTx) {
                    $existingTx->update([
                        'nominal' => $validated['nominal'],
                        'tanggal' => $txDate,
                    ]);
                } else {
                    AsramaKeuangan::create([
                        'tanggal' => $txDate,
                        'tipe' => 'pemasukan',
                        'kategori' => 'Iuran Bulanan',
                        'nominal' => $validated['nominal'],
                        'penghuni_id' => $validated['penghuni_id'],
                        'keterangan' => 'Iuran ' . $namaPenghuni . ' (Bulan ' . $validated['bulan'] . '/' . $validated['tahun'] . ')',
                    ]);
                }
            } else {
                AsramaKeuangan::where('penghuni_id', $validated['penghuni_id'])
                    ->whereYear('tanggal', $validated['tahun'])
                    ->whereMonth('tanggal', $validated['bulan'])
                    ->where('kategori', 'Iuran Bulanan')
                    ->delete();
            }

            // Re-allocate resident payments strictly from asrama_keuangans
            AsramaIuran::where('penghuni_id', $validated['penghuni_id'])->where('tahun', $validated['tahun'])->delete();
            $totalPaidYear = AsramaKeuangan::where('penghuni_id', $validated['penghuni_id'])
                ->whereYear('tanggal', $validated['tahun'])
                ->where(function ($q) {
                    $q->where('tipe', 'pemasukan')->orWhere('kategori', 'Iuran Bulanan');
                })
                ->sum('nominal');

            if ($totalPaidYear > 0) {
                $this->allocateResidentPayment($validated['penghuni_id'], $validated['tahun'], $totalPaidYear);
            }
        }

        return redirect()->back()->with('success', 'Data matriks iuran & Transaksi Kas berhasil tersinkronisasi 100%!');
    }

    public function storeKamar(Request $request)
    {
        $validated = $request->validate([
            'nomor_kamar' => 'required|string|max:50',
            'lantai' => 'required|integer|in:1,2',
            'status' => 'required|in:Tersedia,Penuh,Perbaikan,Gudang',
            'fasilitas' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);
        $validated['kapasitas'] = 100;
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
            'status' => 'required|in:Tersedia,Penuh,Perbaikan,Gudang',
            'fasilitas' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);
        $validated['kapasitas'] = 100;
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

    public function storeKeuangan(Request $request)
    {
        $validated = $request->validate([
            'tipe' => 'required|in:pemasukan,pengeluaran',
            'kategori' => 'required|string|max:255',
            'nominal' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'penghuni_id' => 'nullable|exists:asrama_penghunis,id',
            'keterangan' => 'nullable|string',
        ]);

        $keuangan = AsramaKeuangan::create($validated);

        // AUTO-SYNC TO MATRIKS IURAN BULANAN (asrama_iurans) WITH WATERFALL / FIFO ALLOCATION
        $dateCarbon = \Carbon\Carbon::parse($validated['tanggal']);
        $tahun = (int) $dateCarbon->format('Y');
        $bulan = (int) $dateCarbon->format('n');

        if (!empty($validated['penghuni_id']) && ($validated['tipe'] === 'pemasukan' || $validated['kategori'] === 'Iuran Bulanan')) {
            // Reset and re-allocate total accumulated payments for accuracy
            AsramaIuran::where('penghuni_id', $validated['penghuni_id'])->where('tahun', $tahun)->delete();

            $totalPaidYear = AsramaKeuangan::where('penghuni_id', $validated['penghuni_id'])
                ->whereYear('tanggal', $tahun)
                ->where(function ($q) {
                    $q->where('tipe', 'pemasukan')->orWhere('kategori', 'Iuran Bulanan');
                })
                ->sum('nominal');

            $this->allocateResidentPayment($validated['penghuni_id'], $tahun, $totalPaidYear);
        }

        // AUTO-SYNC FOR WIFI AND SAMPAH EXPENSES
        $lowerKet = strtolower($validated['keterangan'] ?? '');
        if ($validated['kategori'] === 'Pembayaran WiFi' || str_contains($lowerKet, 'wifi') || ($validated['kategori'] === 'Listrik & Air' && str_contains($lowerKet, 'wifi'))) {
            AsramaIuran::updateOrCreate(
                ['tahun' => $tahun, 'bulan' => $bulan, 'fasilitas_key' => 'wifi'],
                ['nominal' => $validated['nominal'], 'status_lunas' => true]
            );
        }
        if ($validated['kategori'] === 'Pembayaran Sampah' || str_contains($lowerKet, 'sampah') || ($validated['kategori'] === 'Kebersihan & Keamanan' && str_contains($lowerKet, 'sampah'))) {
            AsramaIuran::updateOrCreate(
                ['tahun' => $tahun, 'bulan' => $bulan, 'fasilitas_key' => 'sampah'],
                ['nominal' => $validated['nominal'], 'status_lunas' => true]
            );
        }



        return redirect()->route('asrama.keuangan')->with('success', 'Catatan keuangan berhasil ditambahkan & Matriks Iuran otomatis dialokasikan!');
    }

    public function destroyKeuangan($id)
    {
        $keuangan = AsramaKeuangan::findOrFail($id);

        $penghuniId = $keuangan->penghuni_id;
        $tanggal = $keuangan->tanggal;
        $dateCarbon = \Carbon\Carbon::parse($tanggal);
        $tahun = (int) $dateCarbon->format('Y');
        $bulan = (int) $dateCarbon->format('n');
        $kategori = $keuangan->kategori;
        $keterangan = strtolower($keuangan->keterangan ?? '');

        // Delete the transaction record
        $keuangan->delete();

        // 1. RE-SYNC RESIDENT IURAN MATRIX IF RESIDENT TRANSACTION DELETED
        if ($penghuniId) {
            AsramaIuran::where('penghuni_id', $penghuniId)->where('tahun', $tahun)->delete();

            $remainingTotalPaid = AsramaKeuangan::where('penghuni_id', $penghuniId)
                ->whereYear('tanggal', $tahun)
                ->where(function ($q) {
                    $q->where('tipe', 'pemasukan')->orWhere('kategori', 'Iuran Bulanan');
                })
                ->sum('nominal');

            if ($remainingTotalPaid > 0) {
                $this->allocateResidentPayment($penghuniId, $tahun, $remainingTotalPaid);
            }
        }

        // 2. RE-SYNC WIFI OR SAMPAH EXPENSES IF OPERATIONAL EXPENSE DELETED
        if ($kategori === 'Pembayaran WiFi' || str_contains($keterangan, 'wifi') || $kategori === 'Listrik & Air') {
            $otherWifi = AsramaKeuangan::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->where(function ($q) {
                    $q->where('kategori', 'Pembayaran WiFi')
                        ->orWhere('kategori', 'Listrik & Air')
                        ->orWhere('keterangan', 'like', '%wifi%');
                })
                ->exists();

            if (!$otherWifi) {
                AsramaIuran::where('tahun', $tahun)->where('bulan', $bulan)->where('fasilitas_key', 'wifi')->delete();
            }
        }

        if ($kategori === 'Pembayaran Sampah' || str_contains($keterangan, 'sampah') || $kategori === 'Kebersihan & Keamanan') {
            $otherSampah = AsramaKeuangan::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->where(function ($q) {
                    $q->where('kategori', 'Pembayaran Sampah')
                        ->orWhere('kategori', 'Kebersihan & Keamanan')
                        ->orWhere('keterangan', 'like', '%sampah%');
                })
                ->exists();

            if (!$otherSampah) {
                AsramaIuran::where('tahun', $tahun)->where('bulan', $bulan)->where('fasilitas_key', 'sampah')->delete();
            }
        }

        return redirect()->route('asrama.keuangan')->with('success', 'Catatan keuangan berhasil dihapus & Matriks Iuran otomatis disesuaikan!');
    }

    private function allocateResidentPayment($penghuniId, $tahun, $amountReceived)
    {
        $penghuni = AsramaPenghuni::find($penghuniId);
        if (!$penghuni || $amountReceived <= 0) return;

        $tarifDefault = session('asrama_tarif_default', 100000);

        $joinCarbon = $penghuni->tanggal_masuk ? \Carbon\Carbon::parse($penghuni->tanggal_masuk) : \Carbon\Carbon::create(2026, 1, 1);
        $joinYear = (int)$joinCarbon->format('Y');
        $joinMonth = (int)$joinCarbon->format('m');
        $joinDay = (int)$joinCarbon->format('d');

        $startMonth = 1;
        if ($tahun < $joinYear) {
            return;
        } elseif ($tahun == $joinYear) {
            $startMonth = $joinMonth;
        }

        $remaining = $amountReceived;

        for ($m = $startMonth; $m <= 12; $m++) {
            if ($remaining <= 0) break;

            $targetFee = $tarifDefault;
            if ($tahun == $joinYear && $m == $joinMonth) {
                $totalDaysInMonth = $joinCarbon ? $joinCarbon->daysInMonth : 30;
                if ($joinDay == 1 || !$joinCarbon) {
                    $targetFee = $tarifDefault;
                } else {
                    $sisaHari = max(1, $totalDaysInMonth - $joinDay);
                    $rawProrata = ($tarifDefault / $totalDaysInMonth) * $sisaHari;
                    $targetFee = (int) (round($rawProrata / 1000) * 1000);
                }
            }

            $iuran = AsramaIuran::where('tahun', $tahun)
                ->where('bulan', $m)
                ->where('penghuni_id', $penghuniId)
                ->first();

            $alreadyPaid = $iuran ? $iuran->nominal : 0;
            $needed = max(0, $targetFee - $alreadyPaid);

            if ($needed > 0) {
                $allocated = min($remaining, $needed);
                $newNominal = $alreadyPaid + $allocated;

                AsramaIuran::updateOrCreate(
                    ['tahun' => $tahun, 'bulan' => $m, 'penghuni_id' => $penghuniId],
                    ['nominal' => $newNominal, 'status_lunas' => $newNominal >= $targetFee]
                );

                $remaining -= $allocated;
            }
        }

        if ($remaining > 0) {
            $nextYear = $tahun + 1;
            for ($nm = 1; $nm <= 12; $nm++) {
                if ($remaining <= 0) break;
                $targetFee = $tarifDefault;
                $allocated = min($remaining, $targetFee);
                AsramaIuran::updateOrCreate(
                    ['tahun' => $nextYear, 'bulan' => $nm, 'penghuni_id' => $penghuniId],
                    ['nominal' => $allocated, 'status_lunas' => $allocated >= $targetFee]
                );
                $remaining -= $allocated;
            }
        }
    }

    public function exportKeuanganExcel()
    {
        $keuangans = AsramaKeuangan::with('penghuni')->orderBy('tanggal', 'desc')->get();
        $filename = 'Riwayat_Transaksi_Kas_Asrama_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($keuangans) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Row
            fputcsv($file, ['No', 'Tanggal', 'Tipe', 'Kategori', 'Nominal (Rp)', 'Penghuni', 'Keterangan']);

            $no = 1;
            foreach ($keuangans as $k) {
                fputcsv($file, [
                    $no++,
                    $k->tanggal ? \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') : '-',
                    strtoupper($k->tipe),
                    $k->kategori,
                    $k->nominal,
                    $k->penghuni ? $k->penghuni->nama : '-',
                    $k->keterangan ?: '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportKeuanganPdf()
    {
        $keuangans = AsramaKeuangan::with('penghuni')->orderBy('tanggal', 'desc')->get();

        $totalPemasukan = $keuangans->where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = $keuangans->where('tipe', 'pengeluaran')->sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        return view('asrama::export_pdf', compact('keuangans', 'totalPemasukan', 'totalPengeluaran', 'saldoKas'));
    }

    public function exportMatriksExcel(Request $request)
    {
        $startYear = 2026;
        $tahun = (int) $request->input('tahun', date('Y'));
        if ($tahun < $startYear) {
            $tahun = $startYear;
        }

        $penghuniAktif = AsramaPenghuni::where('status_penghuni', 'Aktif')->orderBy('nama')->get();
        $penghuniKeluar = AsramaPenghuni::where('status_penghuni', 'Keluar')->orderBy('nama')->get();
        $iurans = AsramaIuran::where('tahun', $tahun)->get();

        $iuranMap = [];
        foreach ($iurans as $iuran) {
            if ($iuran->penghuni_id) {
                $iuranMap['penghuni_' . $iuran->penghuni_id . '_' . $iuran->bulan] = $iuran;
            } elseif ($iuran->fasilitas_key) {
                $iuranMap['fasilitas_' . $iuran->fasilitas_key . '_' . $iuran->bulan] = $iuran;
            }
        }

        $bulanNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        $tarifDefault = (int) $request->input('tarif_default', 100000);
        $filename = 'Matriks_Iuran_Bulanan_Asrama_Tahun_' . $tahun . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($tahun, $tarifDefault, $penghuniAktif, $penghuniKeluar, $iuranMap, $bulanNames) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            $headerRow = ['Nama / Fasilitas'];
            foreach ($bulanNames as $bName) {
                $headerRow[] = $bName;
            }
            $headerRow[] = 'Kekurangan Iuran (Rp)';
            fputcsv($file, $headerRow);

            // WiFi Row
            $wifiRow = ['WIFI'];
            for ($m = 1; $m <= 12; $m++) {
                $cell = $iuranMap['fasilitas_wifi_' . $m] ?? null;
                $wifiRow[] = ($cell && $cell->status_lunas) ? 'LUNAS' : '-';
            }
            $wifiRow[] = 0; // Facility has no individual resident debt
            fputcsv($file, $wifiRow);

            // Sampah Row
            $sampahRow = ['Iuran Sampah'];
            for ($m = 1; $m <= 12; $m++) {
                $cell = $iuranMap['fasilitas_sampah_' . $m] ?? null;
                $sampahRow[] = ($cell && $cell->status_lunas) ? 'LUNAS' : '-';
            }
            $sampahRow[] = 0; // Facility has no individual resident debt
            fputcsv($file, $sampahRow);

            // Active Residents Rows
            foreach ($penghuniAktif as $p) {
                $pRow = [$p->nama];
                for ($m = 1; $m <= 12; $m++) {
                    $cell = $iuranMap['penghuni_' . $p->id . '_' . $m] ?? null;
                    $nom = $cell ? $cell->nominal : 0;
                    $pRow[] = $nom > 0 ? $nom : 0;
                }

                // Calculate Kekurangan Iuran (Shortfall)
                $effectiveJoinDate = $p->tanggal_masuk ?: '2026-01-01';
                $pJoinCarbon = \Carbon\Carbon::parse($effectiveJoinDate);
                $pJoinYear = (int)$pJoinCarbon->format('Y');
                $pJoinMonth = (int)$pJoinCarbon->format('m');
                $pJoinDay = (int)$pJoinCarbon->format('d');

                $nowCarbon = \Carbon\Carbon::now();
                $cYear = (int)$nowCarbon->format('Y');
                $cMonth = (int)$nowCarbon->format('n');

                $untilM = 12;
                if ($tahun == $cYear) {
                    $untilM = $cMonth;
                } elseif ($tahun > $cYear) {
                    $untilM = 0;
                }

                $pTotalObligation = 0;
                if ($tahun >= $pJoinYear) {
                    $startM = ($tahun == $pJoinYear) ? $pJoinMonth : 1;
                    for ($m = $startM; $m <= $untilM; $m++) {
                        if ($tahun == $pJoinYear && $m == $pJoinMonth) {
                            $tDays = $pJoinCarbon->daysInMonth;
                            if ($pJoinDay == 1) {
                                $pTotalObligation += $tarifDefault;
                            } else {
                                $sisaH = max(1, $tDays - $pJoinDay);
                                $rawP = ($tarifDefault / $tDays) * $sisaH;
                                $pTotalObligation += (int) (round($rawP / 1000) * 1000);
                            }
                        } else {
                            $pTotalObligation += $tarifDefault;
                        }
                    }
                }

                $pTotalPaid = AsramaKeuangan::where('penghuni_id', $p->id)
                    ->whereYear('tanggal', $tahun)
                    ->sum('nominal');

                $pTunggakan = max(0, $pTotalObligation - $pTotalPaid);
                $pRow[] = $pTunggakan;
                fputcsv($file, $pRow);
            }

            // Former Residents Divider & Rows
            if ($penghuniKeluar->count() > 0) {
                fputcsv($file, ['--- PENGHUNI KELUAR ---']);
                foreach ($penghuniKeluar as $p) {
                    $pRow = [$p->nama . ' (Keluar)'];
                    for ($m = 1; $m <= 12; $m++) {
                        $cell = $iuranMap['penghuni_' . $p->id . '_' . $m] ?? null;
                        $nom = $cell ? $cell->nominal : 0;
                        $pRow[] = $nom > 0 ? $nom : 0;
                    }
                    $pRow[] = 0; // Former resident has no active shortfall
                    fputcsv($file, $pRow);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportMatriksPdf(Request $request)
    {
        $startYear = 2026;
        $tahun = (int) $request->input('tahun', date('Y'));
        if ($tahun < $startYear) {
            $tahun = $startYear;
        }

        $tarifDefault = (int) $request->input('tarif_default', 100000);
        $penghuniAktif = AsramaPenghuni::where('status_penghuni', 'Aktif')->orderBy('nama')->get();
        $penghuniKeluar = AsramaPenghuni::where('status_penghuni', 'Keluar')->orderBy('nama')->get();
        $iurans = AsramaIuran::where('tahun', $tahun)->get();

        $iuranMap = [];
        foreach ($iurans as $iuran) {
            if ($iuran->penghuni_id) {
                $iuranMap['penghuni_' . $iuran->penghuni_id . '_' . $iuran->bulan] = $iuran;
            } elseif ($iuran->fasilitas_key) {
                $iuranMap['fasilitas_' . $iuran->fasilitas_key . '_' . $iuran->bulan] = $iuran;
            }
        }

        $bulanNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        return view('asrama::export_matriks_pdf', compact('tahun', 'tarifDefault', 'penghuniAktif', 'penghuniKeluar', 'iuranMap', 'bulanNames'));
    }

    public static function getWifiSettings()
    {
        return [
            'ssid' => \Illuminate\Support\Facades\Cache::get('asrama_wifi_ssid', 'MyHub_Asrama_WiFi'),
            'password' => \Illuminate\Support\Facades\Cache::get('asrama_wifi_password', 'Asrama2026!Pass'),
        ];
    }

    public function saveWifiSettings(Request $request)
    {
        $validated = $request->validate([
            'wifi_ssid' => 'required|string|max:100',
            'wifi_password' => 'required|string|max:100',
        ]);

        \Illuminate\Support\Facades\Cache::forever('asrama_wifi_ssid', $validated['wifi_ssid']);
        \Illuminate\Support\Facades\Cache::forever('asrama_wifi_password', $validated['wifi_password']);

        return redirect()->back()->with('success', 'Pengaturan SSID & Password WiFi Asrama berhasil diperbarui!');
    }

    public static function buildWaUrl($phone, $nama, $bulanName, $tahun, $nominal = 0)
    {
        if (empty($phone)) return null;

        $cleanPhone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }

        $wifi = self::getWifiSettings();
        $ssid = $wifi['ssid'];
        $pass = $wifi['password'];

        $msg = "Halo *{$nama}*,\n\n";
        $msg .= "Pembayaran iuran Asrama Anda untuk bulan *{$bulanName} {$tahun}* telah *LUNAS*";
        if ($nominal > 0) {
            $msg .= " (Rp " . number_format($nominal, 0, ',', '.') . ")";
        }
        $msg .= ".\n\n";
        $msg .= "Berikut informasi akses WiFi Asrama bulan ini:\n";
        $msg .= "📶 *SSID*: {$ssid}\n";
        $msg .= "🔑 *Password*: {$pass}\n\n";
        $msg .= "Terima kasih!";

        return 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($msg);
    }
}
