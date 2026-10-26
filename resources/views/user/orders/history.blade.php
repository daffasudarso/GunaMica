@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Guna Mica')

@section('content')
<div class="container">
    <h4 class="mb-3 text-center">Riwayat Pesanan</h4>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-8">
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari resi / status...">
        </div>
        <div class="col-md-4 d-grid">
            <button class="btn btn-primary" type="submit">Cari</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead>
                <tr>
                    <th>Resi</th>
                    <th>Produk</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Status Bayar</th>
                    <th>Status Kirim</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->resi }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('images/' . $order->image_path) }}" alt="{{ $order->nama_produk }}" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                <span>{{ $order->nama_produk }}</span>
                            </div>
                        </td>
                        <td>{{ $order->created_at }}</td>
                        <td>{{ $order->quantity }}</td>
                        <td>{{ strtoupper($order->status) }}</td>
                        <td>{{ strtoupper((string) $order->delivery_status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</div>
@endsection
