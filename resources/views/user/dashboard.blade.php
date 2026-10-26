@extends('layouts.app')

@section('title', 'User Dashboard - Guna Mica')

@section('content')
<div class="container">
    <div class="bg-white shadow-sm rounded-3 p-4 mb-4">
        <h4 class="mb-1">Selamat datang, {{ auth()->user()->username }}!</h4>
        <p class="text-muted mb-0">Berikut ringkasan produk dari database lama Anda.</p>
    </div>

    <h5 class="mb-3">Produk Terbaru</h5>
    <div class="row g-3 mb-4">
        @forelse ($products as $product)
            <div class="col-6 col-md-3">
                <div class="product-card h-100">
                    <img src="{{ asset('images/' . $product->image_path) }}" alt="{{ $product->nama }}">
                    <div class="p-3">
                        <div class="fw-semibold">{{ $product->nama }}</div>
                        <div class="text-primary fw-bold">Rp {{ number_format((float) $product->harga, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning mb-0">Data produk belum tersedia.</div>
            </div>
        @endforelse
    </div>

    <h5 class="mb-3">Produk Terlaris</h5>
    <div class="row g-3">
        @forelse ($topProducts as $item)
            <div class="col-6 col-md-3">
                <div class="product-card h-100">
                    <img src="{{ asset('images/' . $item->image_path) }}" alt="{{ $item->nama }}">
                    <div class="p-3">
                        <div class="fw-semibold">{{ $item->nama }}</div>
                        <div class="small text-muted">Terjual: {{ $item->total_terjual }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-secondary mb-0">Belum ada transaksi terbayar.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
