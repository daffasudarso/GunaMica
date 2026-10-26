@extends('layouts.app')

@section('title', 'Produk - Guna Mica')

@push('styles')
<style>
    /* Page Header */
    /* Page Header */
    .page-title {
        text-align: center;
        font-weight: 900;
        font-size: 2.8rem;
        letter-spacing: -1.5px;
        line-height: 1.15;
        margin-bottom: 12px;
        color: #0f172a;
        padding-top: 40px;
    }
    .page-title span {
        background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .page-subtitle {
        color: #4e5963;
        font-size: 1.05rem;
        max-width: 600px;
        margin: 0 auto 36px;
        text-align: center;
        line-height: 1.6;
        font-weight: 500;
    }

    /* Filter & Search Panel */
    .filter-panel {
        background: #ffffff;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 25px rgba(15, 23, 42, 0.02);
        border: 1px solid rgba(226, 232, 240, 0.8);
        margin-bottom: 40px;
    }
    .form-select-custom {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background-color: #f8fafc;
        height: 50px;
    }
    .form-input-custom {
        border-radius: 10px 0 0 10px;
        border: 1px solid #cbd5e1;
        border-right: none;
        padding: 12px 18px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background-color: #f8fafc;
        height: 50px;
    }
    .form-select-custom:focus, .form-input-custom:focus {
        border-color: #eab308;
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.12);
        background-color: #ffffff;
        outline: none;
    }
    .btn-search-custom {
        border-radius: 0 10px 10px 0;
        background: #ffd700;
        border: 1px solid #cbd5e1;
        border-left: none;
        color: #0f172a;
        font-weight: 700;
        padding: 0 24px;
        height: 50px;
        transition: all 0.2s ease;
    }
    .btn-search-custom:hover {
        background: #e6c300;
        color: #0f172a;
    }

    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 48px;
    }

    /* Product Card */
    .product-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.02);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        position: relative;
    }
    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        border-color: rgba(255, 215, 0, 0.45);
    }
    
    /* Uniform Image Showcase Container */
    .product-image-wrap {
        position: relative;
        overflow: hidden;
        width: 100%;
        aspect-ratio: 4/3; /* Forces equal dimension sizing on all product cards */
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }
    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Crops image cleanly to fit the container perfectly */
        transition: transform 0.5s ease;
    }
    .product-card:hover .product-image {
        transform: scale(1.06);
    }

    /* Rating Badge on Top-Left of the image container */
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

    /* Product Info Details */
    .product-info {
        padding: 18px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .product-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 8px;
        line-height: 1.4;
        min-height: 44px; /* Align text blocks */
        word-break: break-word;
        transition: color 0.2s ease;
        text-decoration: none !important;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-card:hover .product-name {
        color: #d97706; /* Amber-600 */
    }
    .product-price {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .product-meta {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 4px;
    }
    .product-stock {
        font-size: 0.85rem;
        font-weight: 600;
        color: #0284c7;
        margin-bottom: 12px;
    }
    .card-footer-row {
        width: 100%;
        margin-top: auto;
        padding-top: 10px;
    }
    .btn-detail {
        background: #0f172a;
        color: #ffffff;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    .product-card:hover .btn-detail {
        background: #ffd700;
        color: #0f172a;
        box-shadow: 0 4px 12px rgba(255, 215, 0, 0.25);
    }

    /* Pagination CSS Styling */
    .pagination-wrap {
        display: flex;
        justify-content: center;
        margin-top: 24px;
    }
    .pagination .page-item .page-link {
        color: #0f172a;
        border-radius: 8px;
        margin: 0 3px;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .pagination .page-item.active .page-link {
        background-color: #ffd700;
        border-color: #ffd700;
        color: #0f172a;
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.25);
    }
    .pagination .page-item .page-link:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
    }

    /* Responsive Grid layout */
    @media (max-width: 1200px) {
        .product-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .page-title {
            font-size: 2rem;
        }
        .filter-panel {
            padding: 16px;
        }
    }
    @media (max-width: 576px) {
        .product-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    <h1 class="page-title">Daftar <span>Produk</span></h1>
    <p class="page-subtitle">Temukan berbagai kreasi produk display akrilik premium berkualitas dengan ukuran dan ketebalan seragam.</p>

    <!-- Search & Filter Panel Form -->
    <div class="filter-panel">
        <form class="row g-3 align-items-center" method="get" id="productSearchForm">
            <div class="col-12 col-md-4">
                <select class="form-select form-select-custom" name="kategori" id="categorySelect" aria-label="Kategori Produk">
                    <option value="">-- Semua Kategori --</option>
                    @foreach ($categories as $cat)
                        @if (!empty($cat))
                            <option value="{{ $cat }}" @selected($kategori === $cat)>{{ $cat }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-8">
                <div class="input-group">
                    <input type="text" class="form-control form-input-custom" id="productSearchInput" name="search" value="{{ $search }}" placeholder="Cari produk akrilik...">
                    <button class="btn btn-search-custom" type="submit" aria-label="Cari Produk">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Product Grid Showcase -->
    <div class="product-grid">
        @forelse ($products as $product)
            @php
                $detailUrl = auth()->check() ? route('user.products.show', $product->id) : route('products.guest.show', $product->id);
            @endphp
            <div>
                <div class="product-card">
                    <a href="{{ $detailUrl }}" class="text-decoration-none">
                        <div class="product-image-wrap">
                            <img src="{{ asset('images/' . $product->image_path) }}" alt="{{ $product->nama }}" class="product-image" loading="lazy">
                            @if ($product->avg_rating > 0)
                                <span class="image-rating-badge">
                                    <i class="bi bi-star-fill star-icon"></i>
                                    {{ number_format((float) $product->avg_rating, 1) }}
                                </span>
                            @endif
                        </div>
                    </a>
                    <div class="product-info">
                        <a href="{{ $detailUrl }}" class="text-decoration-none">
                            <h3 class="product-name">{{ $product->nama }}</h3>
                        </a>
                        <div class="product-price">Rp {{ number_format((float) $product->harga, 0, ',', '.') }}</div>
                        <div class="product-meta">Kategori: {{ $product->kategori ?: '-' }}</div>
                        <div class="product-stock">Stok: {{ $product->stock }}</div>
                        
                        <div class="card-footer-row">
                            <a href="{{ $detailUrl }}" class="btn btn-detail">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-4">
                <div class="alert alert-light border py-4 text-center">Produk tidak ditemukan.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination links -->
    <div class="pagination-wrap">
        {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('productSearchInput');
    const form = document.getElementById('productSearchForm');
    if (!input || !form) return;

    let timer = null;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => form.submit(), 450);
    });

    const categorySelect = document.getElementById('categorySelect');
    if (categorySelect && form) {
        categorySelect.addEventListener('change', () => form.submit());
    }
})();
</script>
@endpush
