@extends('layouts.app')

@section('title', 'Beranda - Guna Mica')

@push('styles')
<style>
    /* Hero Banner Showcase Frame */
    .home-banner-wrap {
        border-radius: 24px;
        overflow: hidden;
        margin-bottom: 56px;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.6);
        transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .home-banner-wrap:hover {
        transform: translateY(-4px);
    }
    .home-banner-wrap img {
        width: 100%;
        height: 380px;
        object-fit: cover;
        display: block;
        background: #f8fafc;
    }

    /* Section Titles styling */
    .section-header {
        text-align: center;
        margin-bottom: 40px;
    }
    .section-title {
        font-weight: 900;
        color: #0f172a;
        font-size: 2.4rem;
        margin-bottom: 12px;
        letter-spacing: -1.2px;
        line-height: 1.2;
    }
    .section-title span {
        background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .section-subtitle {
        color: #4e5963;
        font-size: 1.05rem;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
        font-weight: 500;
    }

    /* Top Product Cards */
    .product-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        position: relative;
    }
    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
        border-color: rgba(255, 215, 0, 0.45);
    }
    .product-image-wrap {
        position: relative;
        overflow: hidden;
        aspect-ratio: 4/3;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }
    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .product-card:hover .product-image {
        transform: scale(1.08);
    }

    /* Rating Badge on top left */
    .image-rating-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        color: #ffffff;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }
    .image-rating-badge .star-icon {
        color: #fbbf24;
    }

    /* Card Details */
    .product-info {
        padding: 18px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .product-category-tag {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #d97706; /* Amber-600 */
        font-weight: 750;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .product-name {
        font-size: 0.98rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 8px;
        line-height: 1.4;
        min-height: 40px;
        word-break: break-word;
        transition: color 0.2s ease;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-card:hover .product-name {
        color: #d97706;
    }


    /* Highlight Cards ("Kami Pilihan Hebat Anda!") */
    .feature-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 18px;
        padding: 28px 20px;
        text-align: center;
        height: 100%;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.015);
        transition: all 0.3s ease;
    }
    .feature-card:hover {
        transform: translateY(-5px);
        border-color: #ffd700;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .feature-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(255, 215, 0, 0.1);
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 1.4rem;
        transition: all 0.3s ease;
    }
    .feature-card:hover .feature-icon-wrapper {
        background: #ffd700;
        color: #0f172a;
        transform: scale(1.05);
    }
    .feature-desc {
        font-size: 0.92rem;
        color: #475569;
        line-height: 1.5;
        margin-bottom: 0;
    }



    /* Media query adjustments */
    @media (max-width: 768px) {
        .home-banner-wrap img {
            height: 220px;
            object-fit: contain;
            background: #ffffff;
        }
        .section-title {
            font-size: 1.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container pt-2">
    <!-- Hero Banner Showcase -->
    <div class="home-banner-wrap">
        <img src="{{ asset('images/Bannerr.png') }}" alt="Banner Guna Mica">
    </div>

    <!-- Section: Top Products -->
    <div class="section-header">
        <h2 class="section-title">Produk Terlaris <span>Bulan Ini</span></h2>
        <p class="section-subtitle">Temukan produk display dan kreasi akrilik kustom pilihan paling diminati oleh pelanggan kami.</p>
    </div>
    
    <div class="row g-4 mb-5 justify-content-center">
        @forelse ($topProducts as $item)
            @php
                $detailUrl = auth()->check() ? route('user.products.show', $item->id) : route('products.guest.show', $item->id);
            @endphp
            <div class="col-6 col-md-4">
                <a href="{{ $detailUrl }}" class="product-card">
                    <div class="product-image-wrap">
                        <img src="{{ asset('images/' . $item->image_path) }}" alt="{{ $item->nama }}" class="product-image" loading="lazy">
                        @if ($item->avg_rating > 0)
                            <span class="image-rating-badge">
                                <i class="bi bi-star-fill star-icon"></i>
                                {{ number_format((float) $item->avg_rating, 1) }}
                            </span>
                        @endif
                    </div>
                    <div class="product-info">
                        <span class="product-category-tag">Best Seller</span>
                        <h3 class="product-name">{{ $item->nama }}</h3>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border py-4 text-center mb-0">Belum ada produk terlaris saat ini.</div>
            </div>
        @endforelse
    </div>

    <!-- Section: Highlights -->
    <div class="section-header">
        <h2 class="section-title">Mengapa Harus <span>Guna Mica?</span></h2>
        <p class="section-subtitle">Kami berkomitmen memberikan standar kualitas pengerjaan dan pelayanan terbaik untuk kepuasan Anda.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="feature-card">
                <div class="feature-icon-wrapper"><i class="bi bi-cash-stack"></i></div>
                <p class="feature-desc">Kualitas terbaik dengan harga bersahabat langsung dari pabrik produksi.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="feature-card">
                <div class="feature-icon-wrapper"><i class="bi bi-truck"></i></div>
                <p class="feature-desc">Pengiriman aman ke seluruh Nusantara dengan packing kayu & bubble wrap berlapis.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="feature-card">
                <div class="feature-icon-wrapper"><i class="bi bi-lightning-charge-fill"></i></div>
                <p class="feature-desc">Proses pengerjaan cepat dengan pemotongan laser presisi tinggi yang konsisten.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="feature-card">
                <div class="feature-icon-wrapper"><i class="bi bi-award-fill"></i></div>
                <p class="feature-desc">Gratis konsultasi sketsa desain dan penyesuaian ukuran akrilik custom impian Anda.</p>
            </div>
        </div>
    </div>


</div>
@endsection
