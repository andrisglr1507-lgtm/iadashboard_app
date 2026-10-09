<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Preview - Rekap Bad Stock</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        .header p {
            margin: 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #eaeaea;
            font-weight: bold;
            font-size: 14px;
        }
        td {
            font-size: 14px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .product-name {
            font-weight: bold;
            font-size: 16px;
        }
        .qty-text {
            font-size: 18px;
            font-weight: bold;
        }
        .koli-text {
            font-size: 24px;
            font-weight: 900;
            color: #000;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>Rekapitulasi Bad Stock & Expired</h2>
        <p>
            Filter: 
            Kategori: {{ request('category') ?: 'Semua' }} | 
            Status: {{ request('item_status') ?: 'Semua' }} | 
            Koli: {{ request('koli') ?: 'Semua' }}
        </p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Product</th>
                <th>Nama Product</th>
                <th>UOM</th>
                <th>Packname</th>
                <th class="text-right">Qty Carton</th>
                <th class="text-right">Qty Pcs</th>
                <th class="text-right">Total Qty</th>
                <th class="text-center">Koli</th>
                <th class="text-center">Kategori</th>
                <th>Batch No</th>
                <th>Tgl Expired</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-size: 15px;">{{ $row->sku_code }}</td>
                <td class="product-name">{{ $row->product->sku_name ?? 'Unknown' }}</td>
                <td style="font-size: 15px;">{{ $row->product->uom ?? '-' }}</td>
                <td style="font-size: 15px;">{{ $row->product->packname ?? '-' }}</td>
                <td class="text-right qty-text">{{ $row->qty_carton }}</td>
                <td class="text-right qty-text">{{ $row->qty_pcs }}</td>
                <td class="text-right qty-text" style="font-size: 20px;">{{ $row->total_qty }}</td>
                <td class="text-center koli-text">{{ $row->koli ?? '-' }}</td>
                <td class="text-center" style="font-size: 15px;">
                    @if($row->category == 'EXPIRED_STOCK')
                        EXPIRED
                    @elseif($row->category == 'GOOD_STOCK')
                        GOOD STOCK
                    @else
                        BAD STOCK
                    @endif
                </td>
                <td style="font-size: 15px;">{{ $row->batch_no ?? '-' }}</td>
                <td style="font-size: 15px;">{{ $row->expired_date ? \Carbon\Carbon::parse($row->expired_date)->format('d M Y') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 20px;">
                    Belum ada data barang rusak atau expired yang sesuai kriteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>

</body>
</html>
