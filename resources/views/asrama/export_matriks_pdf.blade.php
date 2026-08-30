<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Matriks Pembayaran Iuran Asrama {{ $tahun }}</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #ffffff;
            color: #1e293b;
            margin: 0;
            padding: 1.5rem;
            font-size: 13px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
        }

        .report-title h1 {
            margin: 0;
            font-size: 1.5rem;
            color: #0f172a;
        }

        .report-title p {
            margin: 0.25rem 0 0 0;
            color: #64748b;
            font-size: 0.85rem;
        }

        .meta-box {
            text-align: right;
            font-size: 0.85rem;
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 0.5rem 0.4rem;
            text-align: center;
            font-size: 0.8rem;
        }

        th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.72rem;
        }

        .col-nama {
            text-align: left;
            padding-left: 0.6rem;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge-paid {
            color: #166534;
            font-weight: 700;
        }

        .badge-empty {
            color: #94a3b8;
        }

        .row-divider {
            background: #e2e8f0;
            font-weight: 700;
            text-align: left;
            padding-left: 0.6rem;
            color: #475569;
            font-size: 0.75rem;
        }

        .no-print {
            margin-bottom: 1.25rem;
            display: flex;
            gap: 0.5rem;
        }

        .btn-print {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <button class="btn-print" style="background: #64748b;" onclick="window.close()">Tutup</button>
    </div>

    <div class="report-header">
        <div class="report-title">
            <h1>Laporan Matriks Pembayaran Iuran Asrama</h1>
            <p>Tahun Anggaran {{ $tahun }} &bull; Tariff Default: Rp {{ number_format($tarifDefault, 0, ',', '.') }}/bln</p>
        </div>
        <div class="meta-box">
            <strong>Tanggal Cetak:</strong> {{ date('d M Y, H:i') }} WIB<br>
            <strong>Total Penghuni Aktif:</strong> {{ count($penghuniAktif) }} Orang
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-nama" style="width: 22%;">Nama / Fasilitas</th>
                @foreach($bulanNames as $bNum => $bName)
                <th>{{ $bName }}</th>
                @endforeach
                <th style="width: 16%;">Kekurangan Iuran</th>
            </tr>
        </thead>
        <tbody>
            {{-- FIXED EXPENSES ROWS (WIFI & Iuran Sampah) --}}
            @foreach(['wifi' => 'WIFI', 'sampah' => 'Iuran Sampah'] as $key => $label)
            <tr>
                <td class="col-nama" style="font-weight: 700;">{{ $label }}</td>
                @foreach($bulanNames as $bNum => $bName)
                @php
                $cell = $iuranMap['fasilitas_' . $key . '_' . $bNum] ?? null;
                $isLunas = $cell ? $cell->status_lunas : false;
                @endphp
                <td>
                    @if($isLunas)
                    <span class="badge-paid">☑ LUNAS</span>
                    @else
                    <span class="badge-empty">☐</span>
                    @endif
                </td>
                @endforeach
                <td style="font-weight: 700; text-align: center; color: #94a3b8;">
                    -
                </td>
            </tr>
            @endforeach

            {{-- ACTIVE RESIDENTS --}}
            @foreach($penghuniAktif as $p)
            @php
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
                if ($tahun==$pJoinYear && $m==$pJoinMonth) {
                $tDays=$pJoinCarbon->daysInMonth;
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

                $pTotalPaid = \App\Models\AsramaKeuangan::where('penghuni_id', $p->id)
                ->whereYear('tanggal', $tahun)
                ->sum('nominal');

                $pTunggakan = max(0, $pTotalObligation - $pTotalPaid);
                @endphp
                <tr>
                    <td class="col-nama">{{ $p->nama }}</td>
                    @foreach($bulanNames as $bNum => $bName)
                    @php
                    $cell = $iuranMap['penghuni_' . $p->id . '_' . $bNum] ?? null;
                    $nom = $cell ? $cell->nominal : 0;
                    @endphp
                    <td>
                        @if($nom > 0)
                        <span class="badge-paid">Rp {{ number_format($nom, 0, ',', '.') }}</span>
                        @else
                        <span class="badge-empty">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td style="font-weight: 700; text-align: center; color: {{ $pTunggakan > 0 ? '#dc2626' : '#16a34a' }};">
                        @if($pTunggakan > 0)
                        Rp {{ number_format($pTunggakan, 0, ',', '.') }}
                        @else
                        ✓ LUNAS
                        @endif
                    </td>
                </tr>
                @endforeach

                {{-- FORMER RESIDENTS --}}
                @if($penghuniKeluar->count() > 0)
                <tr>
                    <td colspan="14" class="row-divider">Penghuni Keluar (Non-Aktif)</td>
                </tr>
                @foreach($penghuniKeluar as $p)
                <tr>
                    <td class="col-nama" style="color: #64748b;">{{ $p->nama }} (Keluar)</td>
                    @foreach($bulanNames as $bNum => $bName)
                    @php
                    $cell = $iuranMap['penghuni_' . $p->id . '_' . $m ?? 1] ?? null;
                    $nom = $cell ? $cell->nominal : 0;
                    @endphp
                    <td>
                        @if($nom > 0)
                        <span class="badge-paid">Rp {{ number_format($nom, 0, ',', '.') }}</span>
                        @else
                        <span class="badge-empty">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td style="font-weight: 700; text-align: center; color: #94a3b8;">
                        -
                    </td>
                </tr>
                @endforeach
                @endif
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>

</html>