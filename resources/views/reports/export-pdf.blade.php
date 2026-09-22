<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - {{ $namaBulan }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #1a1a1a;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
        }

        .header .periode {
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
        }

        .header .product-filter {
            font-size: 11px;
            color: #555;
            margin-top: 3px;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 12px;
        }

        .summary-card {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }

        .summary-card .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 4px;
        }

        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
        }

        .product-summary {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
        }

        .product-summary h2 {
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-summary-item {
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }

        .product-summary-item:last-child {
            border-bottom: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        table thead th {
            background: #1a1a1a;
            color: #fff;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table thead th.center { text-align: center; }
        table thead th.right  { text-align: right; }

        table tbody td {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }

        table tbody td.center { text-align: center; }
        table tbody td.right  { text-align: right; }

        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .produk-list {
            line-height: 1.8;
        }

        .produk-list .row {
            display: flex;
            justify-content: space-between;
        }

        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #888;
        }

        .footer .left  { text-align: left; }
        .footer .right { text-align: right; }

        .total-row td {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #1a1a1a;
            border-bottom: 2px solid #1a1a1a;
        }

        @page {
            margin: 30px 25px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>SIMAAN WATER</h1>
        <div class="subtitle">POS & Analisis Rule Based &middot; Laporan Penjualan</div>
        <div class="periode">{{ $namaBulan }}</div>
        <div class="product-filter">Produk: {{ $productLabel }}</div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ $totalTransaksi }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($totalPendapatan,0,',','.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Produk Terjual</div>
            <div class="value">{{ $totalProdukTerjual }} unit</div>
        </div>
    </div>

    @if($isAllProducts && $ringkasanProduk->isNotEmpty())
        <div class="product-summary">
            <h2>Detail Produk Terjual</h2>
            @foreach($ringkasanProduk as $produk)
                <div class="product-summary-item">
                    {{ $produk->nama_produk }} terjual {{ $produk->total_terjual }} unit
                </div>
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 14%;">Tanggal</th>
                <th style="width: 30%;">Jenis Produk</th>
                <th class="center" style="width: 10%;">Jumlah Dibeli</th>
                <th style="width: 16%;">Kasir</th>
                <th class="right" style="width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($kosong) && $kosong)
                <tr>
                    <td colspan="6" style="text-align:center; padding:32px 8px; color:#888;">
                        Tidak ada transaksi pada periode {{ $namaBulan }}.
                    </td>
                </tr>
            @else
            @php $no = 1; @endphp
            @foreach($sales as $sale)
                <tr>
                    <td class="center">{{ str_pad($no, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ \App\Support\Waktu::tanggal($sale->tanggal) }}</td>
                    <td>
                        <div class="produk-list">
                            @foreach($sale->details as $detail)
                                <div>{{ $detail->product->nama_produk }}</div>
                            @endforeach
                        </div>
                    </td>
                    <td class="center">
                        <div class="produk-list">
                            @foreach($sale->details as $detail)
                                <div>{{ $detail->qty }}</div>
                            @endforeach
                        </div>
                    </td>
                    <td>{{ $sale->user->name }}</td>
                    <td class="right">
                        Rp {{ number_format($isAllProducts ? $sale->total_harga : $sale->details->sum('subtotal'),0,',','.') }}
                    </td>
                </tr>
                @php $no++; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="right">TOTAL</td>
                <td class="right">Rp {{ number_format($totalPendapatan,0,',','.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <div class="left">
            Dicetak pada {{ \App\Support\Waktu::sekarang() }} {{ \App\Support\Waktu::labelZona() }}
        </div>
        <div class="right">
            &copy; {{ date('Y') }} Simaan Water &middot; POS & Analisis Rule Based
        </div>
    </div>

</body>
</html>
