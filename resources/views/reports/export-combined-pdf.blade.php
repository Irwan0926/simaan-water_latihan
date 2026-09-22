<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Gabungan — Harian, Mingguan, Bulanan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid #1a1a1a;
        }
        .header h1 { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .header .subtitle { font-size: 11px; color: #666; margin-top: 4px; }
        .header .periode { font-size: 13px; font-weight: bold; margin-top: 8px; }
        .section { margin-bottom: 28px; page-break-inside: avoid; }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 6px 10px;
            background: #1a1a1a;
            color: #fff;
            letter-spacing: 0.5px;
        }
        .section-subtitle {
            font-size: 11px;
            color: #555;
            margin: -4px 0 12px 0;
            padding-left: 2px;
        }
        .summary {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary td {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            width: 33.33%;
        }
        .summary .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 4px;
        }
        .summary .value {
            font-size: 15px;
            font-weight: bold;
            color: #1a1a1a;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table.data thead th {
            background: #333;
            color: #fff;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.data thead th.center { text-align: center; }
        table.data thead th.right  { text-align: right; }
        table.data tbody td {
            padding: 6px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        table.data tbody td.center { text-align: center; }
        table.data tbody td.right  { text-align: right; }
        table.data tbody tr:nth-child(even) { background: #f9f9f9; }
        .total-row td {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #1a1a1a;
            border-bottom: 2px solid #1a1a1a;
        }
        .empty {
            text-align: center;
            padding: 20px 8px;
            color: #888;
            border: 1px dashed #ddd;
        }
        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #888;
        }
        .footer .left  { float: left; }
        .footer .right { float: right; text-align: right; }
        .clear { clear: both; }
        @page { margin: 28px 22px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>SIMAAN WATER</h1>
        <div class="subtitle">POS &amp; Analisis Rule Based &middot; Laporan Penjualan Gabungan</div>
        <div class="periode">Harian &middot; Mingguan &middot; Bulanan</div>
    </div>

    @foreach($sections as $section)
        <div class="section">
            <div class="section-title">{{ $section['judul'] }}</div>
            <div class="section-subtitle">{{ $section['label'] }}</div>

            <table class="summary">
                <tr>
                    <td>
                        <div class="label">Total Transaksi</div>
                        <div class="value">{{ $section['totalTransaksi'] }}</div>
                    </td>
                    <td>
                        <div class="label">Total Pendapatan</div>
                        <div class="value">Rp {{ number_format($section['totalPendapatan'], 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <div class="label">Produk Terjual</div>
                        <div class="value">{{ $section['totalProdukTerjual'] }} unit</div>
                    </td>
                </tr>
            </table>

            @if($section['kosong'])
                <div class="empty">Tidak ada transaksi pada periode {{ $section['label'] }}.</div>
            @else
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 14%;">Tanggal</th>
                            <th style="width: 30%;">Jenis Produk</th>
                            <th class="center" style="width: 12%;">Jumlah</th>
                            <th style="width: 16%;">Kasir</th>
                            <th class="right" style="width: 15%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($section['sales'] as $sale)
                            <tr>
                                <td class="center">{{ str_pad($no, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ \App\Support\Waktu::tanggal($sale->tanggal) }}</td>
                                <td>
                                    @foreach($sale->details as $detail)
                                        <div>{{ $detail->product->nama_produk ?? '-' }}</div>
                                    @endforeach
                                </td>
                                <td class="center">
                                    @foreach($sale->details as $detail)
                                        <div>{{ $detail->qty }}</div>
                                    @endforeach
                                </td>
                                <td>{{ $sale->user->name ?? '-' }}</td>
                                <td class="right">Rp {{ number_format($sale->total_harga, 0, ',', '.') }}</td>
                            </tr>
                            @php $no++; @endphp
                        @endforeach
                        <tr class="total-row">
                            <td colspan="5" class="right">TOTAL</td>
                            <td class="right">Rp {{ number_format($section['totalPendapatan'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    <div class="footer">
        <div class="left">Dicetak pada {{ \App\Support\Waktu::sekarang() }} {{ \App\Support\Waktu::labelZona() }}</div>
        <div class="right">&copy; {{ date('Y') }} Simaan Water &middot; POS &amp; Analisis Rule Based</div>
        <div class="clear"></div>
    </div>

</body>
</html>
