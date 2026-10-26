@extends('layouts.app')

@section('title', $product->nama . ' - Guna Mica')

@push('styles')
<style>
    /* Premium Detail Card Container */
    .detail-wrap {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 4px 25px rgba(15, 23, 42, 0.02);
        padding: 40px;
        margin-bottom: 48px !important;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    /* Media Showcase Wrapper */
    .main-image-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        margin-bottom: 18px;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
        border: 1px solid #f1f5f9;
        aspect-ratio: 4/3;
        background: #f8fafc;
    }
    .main-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease;
        transform-origin: center center;
    }
    .main-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        background: #0f172a;
    }
    .image-rating-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 3;
        background: rgba(15, 23, 42, 0.85);
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
    .zoom-controls {
        position: absolute;
        right: 14px;
        bottom: 14px;
        z-index: 3;
        display: flex;
        gap: 6px;
    }
    .zoom-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(4px);
        color: #ffffff;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .zoom-btn:hover {
        background: rgba(15, 23, 42, 0.95);
        transform: scale(1.05);
    }

    /* Thumbnail Gallery List */
    .thumb-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 18px;
    }
    .thumb-button {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 0;
        overflow: hidden;
        background: #f8fafc;
        width: 76px;
        height: 76px;
        transition: all 0.25s ease;
        cursor: pointer;
        position: relative;
    }
    .thumb-button:hover, .thumb-button.active {
        border-color: #ffd700;
        box-shadow: 0 4px 12px rgba(255, 215, 0, 0.25);
        transform: translateY(-2px);
    }
    .thumb-button img, .thumb-button video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .thumb-video-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #ffffff;
        font-size: 1.5rem;
        z-index: 2;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    /* Product Title & Info Column */
    .product-title {
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
        line-height: 1.25;
    }
    .product-category-badge {
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 16px;
    }
    .product-price-detail {
        font-size: 1.85rem;
        font-weight: 800;
        color: #d97706; /* Amber-600 */
        margin-bottom: 24px;
        letter-spacing: -0.5px;
    }

    /* Specs Horizontal Cards Grid */
    .spec-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 28px;
    }
    .spec-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px 10px;
        background: #f8fafc;
        text-align: center;
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .spec-card:hover {
        background: #ffffff;
        border-color: rgba(255, 215, 0, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.02);
    }
    .spec-icon {
        font-size: 1.3rem;
        color: #ffd700; /* Gold */
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .spec-label {
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 4px;
    }
    .spec-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        display: block;
    }

    /* Colors and Description styling */
    .info-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .color-tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    .color-tag {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #334155;
        font-weight: 600;
    }
    .product-description {
        font-size: 0.95rem;
        color: #475569;
        line-height: 1.6;
        margin-bottom: 28px;
    }

    /* Checkout / Action Form box */
    .cart-form-box {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        background: #f8fafc;
        margin-bottom: 12px;
    }
    .form-control-custom, .form-select-custom {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 10px 14px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background-color: #ffffff;
        height: 46px;
    }
    .form-control-custom:focus, .form-select-custom:focus {
        border-color: #eab308;
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.12);
        outline: none;
    }
    .btn-add-cart {
        background: #10b981; /* Premium Emerald */
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 12px 28px;
        font-weight: 700;
        font-size: 0.98rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        height: 46px;
    }
    .btn-add-cart:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
    }
    .btn-back-custom {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-weight: 600;
        padding: 12px 24px;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none;
        height: 46px;
        justify-content: center;
    }
    .btn-back-custom:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #0f172a;
    }

    /* Minimalist Reviews Carousel & Card styling */
    .reviews-header {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 20px;
        letter-spacing: -0.3px;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 6px;
        display: inline-block;
    }
    .review-item {
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 12px;
        padding: 14px;
        background: #ffffff;
        height: 100%;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.01);
        transition: border-color 0.2s ease;
    }
    .review-item:hover {
        border-color: rgba(255, 215, 0, 0.4);
    }
    .review-user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .review-user-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
    }
    .review-date {
        color: #94a3b8;
        font-size: 0.75rem;
    }
    .review-stars {
        color: #fbbf24;
        font-size: 0.75rem;
        display: flex;
        gap: 2px;
    }
    .review-text {
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.5;
    }
    .review-media-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        margin-top: 8px;
        background: #0f172a;
    }
    .carousel-control-prev, .carousel-control-next {
        width: 36px;
        height: 36px;
        background: rgba(15, 23, 42, 0.85);
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    #reviewCarousel:hover .carousel-control-prev, #reviewCarousel:hover .carousel-control-next {
        opacity: 1;
    }
    .carousel-control-prev { left: -14px; }
    .carousel-control-next { right: -14px; }
    .carousel-control-prev-icon, .carousel-control-next-icon {
        width: 16px;
        height: 16px;
    }
    .border-dashed {
        border-style: dashed !important;
    }

    /* Related Products Section */
    .related-section-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: #0f172a;
        margin-top: 56px;
        margin-bottom: 24px;
        letter-spacing: -0.3px;
    }
    .related-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.01);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .related-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
        border-color: rgba(255, 215, 0, 0.4);
    }
    .related-card img {
        width: 100%;
        height: 155px;
        object-fit: cover;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }
    .related-details {
        padding: 14px 16px;
    }
    .related-name {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        transition: color 0.2s ease;
    }
    .related-card:hover .related-name {
        color: #d97706;
    }

    /* Responsive adjusts */
    @media (max-width: 991px) {
        .detail-wrap {
            padding: 24px;
        }
    }
    @media (max-width: 768px) {
        .detail-wrap {
            padding: 20px 16px;
            border-radius: 18px;
        }
        .main-image-wrap {
            max-height: 300px;
        }
        .product-title {
            font-size: 1.55rem;
        }
        .spec-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .carousel-control-prev, .carousel-control-next {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    @php
        $firstMedia = $product->gallery_images[0] ?? 'logoB.png';
        $firstMediaExt = strtolower((string) pathinfo((string) $firstMedia, PATHINFO_EXTENSION));
        $firstIsVideo = in_array($firstMediaExt, ['mp4', 'mov', 'avi', 'webm'], true);
    @endphp

    <div class="detail-wrap">
        <div class="row g-4">
            <!-- Left Column: Gallery Media Showcase -->
            <div class="col-md-5">
                <div class="main-image-wrap">
                    <img src="{{ asset('images/' . $firstMedia) }}" alt="{{ $product->nama }}" class="main-image {{ $firstIsVideo ? 'd-none' : '' }}" id="mainProductImage">
                    <video controls class="main-video {{ $firstIsVideo ? '' : 'd-none' }}" id="mainProductVideo">
                        <source src="{{ asset('images/' . $firstMedia) }}">
                    </video>
                    @if ($product->avg_rating > 0)
                        <span class="image-rating-badge">
                            <i class="bi bi-star-fill star-icon"></i>
                            {{ number_format((float) $product->avg_rating, 1) }}
                        </span>
                    @endif
                    <div class="zoom-controls">
                        <button type="button" class="zoom-btn" id="zoomOutBtn" aria-label="Perkecil">-</button>
                        <button type="button" class="zoom-btn" id="zoomInBtn" aria-label="Perbesar">+</button>
                    </div>
                </div>
                <div class="thumb-grid">
                    @foreach ($product->gallery_images as $index => $img)
                        @php
                            $mediaExt = strtolower((string) pathinfo((string) $img, PATHINFO_EXTENSION));
                            $isVideoMedia = in_array($mediaExt, ['mp4', 'mov', 'avi', 'webm'], true);
                        @endphp
                        <button type="button" class="thumb-button {{ $index === 0 ? 'active' : '' }}" data-media="{{ asset('images/' . $img) }}" data-type="{{ $isVideoMedia ? 'video' : 'image' }}" aria-label="Media Thumbnail {{ $index + 1 }}">
                            @if ($isVideoMedia)
                                <div class="thumb-video-icon"><i class="bi bi-play-fill"></i></div>
                                <video muted preload="metadata">
                                    <source src="{{ asset('images/' . $img) }}">
                                </video>
                            @else
                                <img src="{{ asset('images/' . $img) }}" alt="{{ $product->nama }}">
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Right Column: Details & Add to Cart form -->
            <div class="col-md-7">
                <span class="product-category-badge">{{ $product->kategori ?: 'Akrilik' }}</span>
                <h1 class="product-title">{{ $product->nama }}</h1>
                <div class="product-price-detail">Rp {{ number_format((float) $product->harga, 0, ',', '.') }}</div>

                <!-- Product Specifications Grid with Icons -->
                <div class="spec-grid">
                    <div class="spec-card">
                        <div class="spec-icon"><i class="bi bi-box-seam"></i></div>
                        <span class="spec-label">Stok</span>
                        <span class="spec-value text-primary">{{ $product->stock }} pcs</span>
                    </div>
                    <div class="spec-card">
                        <div class="spec-icon"><i class="bi bi-layers"></i></div>
                        <span class="spec-label">Tebal</span>
                        <span class="spec-value">{{ $product->ketebalan ?: '-' }}</span>
                    </div>
                    <div class="spec-card">
                        <div class="spec-icon"><i class="bi bi-arrows-angle-expand"></i></div>
                        <span class="spec-label">Ukuran</span>
                        <span class="spec-value text-truncate">{{ $product->ukuran ?: '-' }}</span>
                    </div>
                    <div class="spec-card">
                        <div class="spec-icon"><i class="bi bi-box"></i></div>
                        <span class="spec-label">Berat</span>
                        <span class="spec-value">{{ (int) ($product->weight_gram ?? 0) }} g</span>
                    </div>
                </div>

                <!-- Colors List -->
                @php
                    $warnaList = collect(explode(',', (string) $product->warna))->map(fn ($w) => trim($w))->filter()->values();
                @endphp
                <div class="mb-4">
                    <h3 class="info-section-title">Warna Tersedia:</h3>
                    @if ($warnaList->isNotEmpty())
                        <div class="color-tag-list">
                            @foreach ($warnaList as $warna)
                                <span class="color-tag">{{ $warna }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h3 class="info-section-title">Deskripsi Produk:</h3>
                    <p class="product-description">{{ $product->deskripsi ?: 'Tidak ada deskripsi untuk produk ini.' }}</p>
                </div>

                <!-- Checkout Purchase Form -->
                <div class="cart-form-box">
                    @if (auth()->check())
                        <form action="{{ route('cart.store') }}" method="post" class="row g-3 align-items-end">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div class="col-6 col-sm-3">
                                <label class="form-label small fw-bold">Jumlah</label>
                                <input type="number" class="form-control form-control-custom w-100" name="quantity" value="1" min="1" max="{{ $product->stock }}">
                            </div>
                            <div class="col-6 col-sm-5">
                                <label class="form-label small fw-bold">Pilih Warna</label>
                                @if ($warnaList->isNotEmpty())
                                    <select class="form-select form-select-custom" name="color" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach ($warnaList as $warna)
                                            <option value="{{ $warna }}">{{ $warna }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control form-control-custom" name="color" placeholder="Contoh: Bening">
                                @endif
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2 mt-3 pt-2">
                                <button class="btn btn-add-cart" type="submit">
                                    <i class="bi bi-cart-plus-fill"></i> Tambah ke Keranjang
                                </button>
                                <a href="{{ route('user.products.index') }}" class="btn-back-custom">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    @else
                        <div class="row g-3">
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <a href="{{ route('login') }}" class="btn btn-add-cart w-100 py-2">
                                    <i class="bi bi-lock-fill"></i> Login untuk Membeli
                                </a>
                                <a href="{{ route('products.guest') }}" class="btn-back-custom w-100 text-center justify-content-center">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Produk
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Review Section Slider Carousel / Grid (Conditional on Count) -->
    <div class="mb-5">
        <h2 class="reviews-header">Ulasan Pelanggan</h2>
        @if ($reviews->isEmpty())
            <div class="text-muted text-center py-4 border-dashed border rounded-3">Belum ada ulasan untuk produk ini.</div>
        @else
            @if ($reviews->count() > 2)
                @php
                    $reviewSlides = $reviews->chunk(2)->values();
                @endphp
                <div id="reviewCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000">
                    <div class="carousel-inner">
                        @foreach ($reviewSlides as $index => $slide)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <div class="row g-3 px-1">
                                    @foreach ($slide as $review)
                                        @php
                                            $profileImg = (string) ($review->profile_image ?? '');
                                            if (filter_var($profileImg, FILTER_VALIDATE_URL)) {
                                                $profileSrc = $profileImg;
                                            } elseif ($profileImg !== '' && file_exists(public_path('uploads/profiles/' . $profileImg))) {
                                                $profileSrc = asset('uploads/profiles/' . $profileImg);
                                            } elseif ($profileImg !== '' && file_exists(public_path('uploads/' . $profileImg))) {
                                                $profileSrc = asset('uploads/' . $profileImg);
                                            } else {
                                                $profileSrc = asset('images/logoB.png');
                                            }
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="review-item">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <img src="{{ $profileSrc }}" alt="{{ $review->username }}" class="review-user-avatar">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h4 class="review-user-name mb-0">{{ $review->username }}</h4>
                                                            <span class="review-date">{{ \Carbon\Carbon::parse($review->created_at)->translatedFormat('d M Y') }}</span>
                                                        </div>
                                                        <div class="review-stars">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="bi {{ $i <= (int) $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="review-text mb-0">{{ $review->clean_review ?: 'Pelanggan memberikan rating bintang tanpa komentar.' }}</p>
                                                @if (!empty($review->media_file))
                                                    <div class="mt-2">
                                                        @if ($review->media_is_video)
                                                            <video controls preload="metadata" class="review-media-thumb">
                                                                <source src="{{ asset('uploads/reviews/' . $review->media_file) }}">
                                                            </video>
                                                        @else
                                                            <img src="{{ asset('uploads/reviews/' . $review->media_file) }}" alt="Media Ulasan" class="review-media-thumb">
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($reviewSlides->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#reviewCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#reviewCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif
                </div>
            @else
                <div class="row g-3 px-1">
                    @foreach ($reviews as $review)
                        @php
                            $profileImg = (string) ($review->profile_image ?? '');
                            if (filter_var($profileImg, FILTER_VALIDATE_URL)) {
                                $profileSrc = $profileImg;
                            } elseif ($profileImg !== '' && file_exists(public_path('uploads/profiles/' . $profileImg))) {
                                $profileSrc = asset('uploads/profiles/' . $profileImg);
                            } elseif ($profileImg !== '' && file_exists(public_path('uploads/' . $profileImg))) {
                                $profileSrc = asset('uploads/' . $profileImg);
                            } else {
                                $profileSrc = asset('images/logoB.png');
                            }
                        @endphp
                        <div class="col-md-6">
                            <div class="review-item">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img src="{{ $profileSrc }}" alt="{{ $review->username }}" class="review-user-avatar">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="review-user-name mb-0">{{ $review->username }}</h4>
                                            <span class="review-date">{{ \Carbon\Carbon::parse($review->created_at)->translatedFormat('d M Y') }}</span>
                                        </div>
                                        <div class="review-stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi {{ $i <= (int) $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <p class="review-text mb-0">{{ $review->clean_review ?: 'Pelanggan memberikan rating bintang tanpa komentar.' }}</p>
                                @if (!empty($review->media_file))
                                    <div class="mt-2">
                                        @if ($review->media_is_video)
                                            <video controls preload="metadata" class="review-media-thumb">
                                                <source src="{{ asset('uploads/reviews/' . $review->media_file) }}">
                                            </video>
                                        @else
                                            <img src="{{ asset('uploads/reviews/' . $review->media_file) }}" alt="Media Ulasan" class="review-media-thumb">
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    <!-- Related Products -->
    <div>
        <h2 class="related-section-title">Produk Terkait</h2>
        <div class="row g-4">
            @forelse ($related as $item)
                @php
                    $itemUrl = auth()->check() ? route('user.products.show', $item->id) : route('products.guest.show', $item->id);
                @endphp
                <div class="col-6 col-md-3">
                    <a class="related-card" href="{{ $itemUrl }}">
                        <img src="{{ asset('images/' . $item->image_path) }}" alt="{{ $item->nama }}" loading="lazy">
                        <div class="related-details">
                            <div class="related-name text-truncate">{{ $item->nama }}</div>
                            <div class="small fw-bold text-muted mt-1">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light text-center border py-3">Belum ada produk terkait.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const mainImage = document.getElementById('mainProductImage');
    const mainVideo = document.getElementById('mainProductVideo');
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    if (!mainImage || !mainVideo) return;

    let scale = 1;

    const applyScale = () => {
        scale = Math.min(3, Math.max(1, scale));
        mainImage.style.transform = `scale(${scale})`;
    };

    const wrap = mainImage.closest('.main-image-wrap');
    if (wrap) {
        wrap.addEventListener('mousemove', (event) => {
            if (mainImage.classList.contains('d-none')) {
                return;
            }
            const rect = wrap.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width) * 100;
            const y = ((event.clientY - rect.top) / rect.height) * 100;
            mainImage.style.transformOrigin = `${x}% ${y}%`;
            if (scale < 1.6) {
                scale = 1.6;
            }
            applyScale();
        });

        wrap.addEventListener('mouseleave', () => {
            scale = 1;
            mainImage.style.transformOrigin = 'center center';
            applyScale();
        });
    }

    document.querySelectorAll('.thumb-button').forEach((button) => {
        button.addEventListener('click', () => {
            // Remove active class from all thumb buttons
            document.querySelectorAll('.thumb-button').forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');

            const nextMedia = button.getAttribute('data-media');
            const mediaType = button.getAttribute('data-type') || 'image';
            if (nextMedia) {
                if (mediaType === 'video') {
                    mainImage.classList.add('d-none');
                    mainVideo.classList.remove('d-none');
                    mainVideo.src = nextMedia;
                    mainVideo.load();
                } else {
                    mainVideo.pause();
                    mainVideo.classList.add('d-none');
                    mainVideo.src = '';
                    mainImage.classList.remove('d-none');
                    mainImage.src = nextMedia;
                }

                scale = 1;
                applyScale();
            }
        });
    });

    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', () => {
            scale += 0.2;
            applyScale();
        });
    }

    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', () => {
            scale -= 0.2;
            applyScale();
        });
    }

    mainImage.addEventListener('wheel', (event) => {
        if (mainImage.classList.contains('d-none')) {
            return;
        }
        event.preventDefault();
        scale += event.deltaY < 0 ? 0.15 : -0.15;
        applyScale();
    }, { passive: false });
})();
</script>
@endpush
