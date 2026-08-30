<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Kas Asrama - {{ date('d/m/Y') }}</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #ffffff;
            color: #1e293b;
            margin: 0;
            padding: 2rem;
            font-size: 14px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .report-title h1 {
            margin: 0;
            font-size: 1.6rem;
            color: #0f172a;
        }

        .report-title p {
            margin: 0.25rem 0 0 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .meta-box {
            text-align: right;
            font-size: 0.85rem;
            color: #475569;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.85rem;
            background: #f8fafc;
        }

        .summary-card p {
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }

        .summary-card h3 {
            margin: 0.25rem 0 0 0;
            font-size: 1.25rem;
            font-weight: 800;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 0.6rem 0.75rem;
            text-align: left;
            font-size: 0.85rem;
        }

        th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .no-print {
            margin-bottom: 1.5rem;
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
            <h1>Laporan Riwayat Transaksi Kas Asrama</h1>
            <p>Sistem Informasi Manajemen Asrama MyHub</p>
        </div>
        <div class="meta-box">
            <strong>Tanggal Cetak:</strong> {{ date('d M Y, H:i') }} WIB<br>
            <strong>Total Transaksi:</strong> {{ count($keuangans) }} Catatan
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <p>Total Pemasukan</p>
            <h3 style="color: #16a34a;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h3>
        </div>
        <div class="summary-card">
            <p>Total Pengeluaran</p>
            <h3 style="color: #dc2626;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
        </div>
        <div class="summary-card">
            <p>Saldo Kas Saat Ini</p>
            <h3 style="color: #0284c7;">Rp {{ number_format($saldoKas, 0, ',', '.') }}</h3>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 12%;">Tipe</th>
                <th style="width: 20%;">Kategori</th>
                <th style="width: 18%;">Nominal (Rp)</th>
                <th style="width: 18%;">Penghuni</th>
                <th style="width: 15%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($keuangans as $index => $k)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $k->tanggal ? \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') : '-' }}</td>
                <td>
                    @if($k->tipe === 'pemasukan')
                    <span class="badge badge-success">PEMASUKAN</span>
                    @else
                    <span class="badge badge-danger">PENGELUARAN</span>
                    @endif
                </td>
                <td style="font-weight: 600;">{{ $k->kategori }}</td>
                <td style="font-weight: 700; color: {{ $k->tipe === 'pemasukan' ? '#16a34a' : '#dc2626' }};">
                    {{ $k->tipe === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($k->nominal, 0, ',', '.') }}
                </td>
                <td>{{ $k->penghuni ? $k->penghuni->nama : '-' }}</td>
                <td style="color: #64748b;">{{ $k->keterangan ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #64748b;">Belum ada riwayat transaksi kas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        // Automatic print prompt on page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>

</html>
