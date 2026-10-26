<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Pesanan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }
        .header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2563eb;
        }
        .header-table {
            width: 100%;
            border: none;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .header h2 {
            margin: 0 0 5px;
            font-size: 24px;
            color: #2563eb;
        }
        .logo {
            width: 65px;
            height: 65px;
            margin-right: 15px;
        }
        .meta {
            color: #4b5563;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th,
        td {
            border: 1px solid #d1d5db;
            padding: 7px;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
            text-align: left;
        }
        .right {
            text-align: right;
        }
        .summary {
            margin-top: 14px;
            width: 320px;
            margin-left: auto;
        }
        .summary td {
            border: none;
            padding: 5px 0;
        }
        .total-row {
            font-weight: bold;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 70px;">
                    @if (!empty($logoDataUri))
                        <img src="{{ $logoDataUri }}" alt="Logo Guna Mica" class="logo">
                    @endif
                </td>
                <td>
                    <h2>GUNA MICA</h2>
                    <div class="meta">Toko Akrilik Terpercaya<br>Jakarta, Indonesia<br>Email: gunamicaadvertising98@gmail.com</div>
                </td>
                <td style="text-align: right;">
                    <div style="font-size: 22px; font-weight: bold; color: #2563eb; margin-bottom: 5px;">INVOICE</div>
                    <div class="meta">
                        No. Invoice: <b>{{ $orders->first()->resi ?? '-' }}</b><br>
                        Tanggal: <b>{{ $orders->first()->created_at ? \Carbon\Carbon::parse($orders->first()->created_at)->format('d-m-Y') : '-' }}</b><br>
                        Status: <span style="color:#16a34a;font-weight:bold;">{{ strtoupper($orders->first()->status) }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <h4 style="margin:18px 0 6px 0; color:#2563eb; font-size:15px;">Data Pelanggan</h4>
    <table style="width:100%;border:0;margin-bottom:10px;">
        <tr><td style="width:160px;">Nama Pelanggan</td><td>: {{ $orders->first()->nama }}</td></tr>
        <tr><td>Nama Penerima</td><td>: {{ $orders->first()->nama }}</td></tr>
        <tr><td>Alamat Pengiriman</td><td>: {{ $orders->first()->alamat }}</td></tr>
        <tr><td>No. Telepon</td><td>: {{ $orders->first()->no_telp }}</td></tr>
    </table>

    <h4 style="margin:18px 0 6px 0; color:#2563eb; font-size:15px;">Detail Pesanan</h4>
    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Produk</th>
                <th style="width:90px;">Harga</th>
                <th style="width:50px;">Jumlah</th>
                <th style="width:90px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $order->nama_produk }}</td>
                    <td>Rp{{ number_format((float) $order->amount / max(1, (int)$order->quantity), 0, ',', '.') }}</td>
                    <td>{{ (int) $order->quantity }}</td>
                    <td>Rp{{ number_format((float) $order->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table style="width:100%;border:0;margin-top:8px;">
        <tr><td style="width:80px;">Subtotal</td><td style="text-align:right;">Rp{{ number_format((float) $summary['subtotal'], 0, ',', '.') }}</td></tr>
        <tr><td>Ongkos Kirim</td><td style="text-align:right;">Rp{{ number_format((float) $summary['ongkir'], 0, ',', '.') }}</td></tr>
        <tr style="font-weight:bold;"><td>Total</td><td style="text-align:right;">Rp{{ number_format((float) $summary['total'], 0, ',', '.') }}</td></tr>
    </table>

    <h4 style="margin:18px 0 6px 0; color:#2563eb; font-size:15px;">Informasi Pengiriman</h4>
    <table style="width:100%;border:0;margin-bottom:10px;">
        <tr><td style="width:160px;">Metode Pembayaran</td><td>: {{ $orders->first()->payment_method ?? '-' }}</td></tr>
        <tr><td>Status Pengiriman</td><td>: {{ ucfirst($orders->first()->delivery_status) }}</td></tr>
        <tr><td>Nomor Resi</td><td>: {{ $orders->first()->resi }}</td></tr>
    </table>

    <div style="margin-top:30px;font-size:11px;color:#64748b;text-align:center;">
        Terima kasih atas kepercayaan Anda berbelanja di GUNA MICA.<br>
        Jika ada pertanyaan, silakan hubungi customer service kami.<br>
        <b>© {{ date('Y') }} GUNA MICA.</b>
    </div>
</body>
</html>
