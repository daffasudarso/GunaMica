@extends('layouts.app')

@section('title', 'Galeri & Layanan - Guna Mica')

@push('styles')
<style>
    .page-header {
        text-align: center;
        padding: 40px 0 20px 0;
        margin-bottom: 32px;
        color: #23242a;
    }
    @media (max-width: 1200px) {
        .page-header {
            padding: 24px 16px;
        }
    }
    .page-header h1 {
        font-size: 2.8rem;
        font-weight: 900;
        color: #23242a;
        margin-bottom: 12px;
        letter-spacing: -1.5px;
        line-height: 1.15;
    }
    .page-header h1 span {
        background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .page-header p {
        font-size: 1.05rem;
        color: #4e5963;
        margin-bottom: 0;
        font-weight: 500;
        line-height: 1.6;
    }
    /* duplikasi dihapus, style p tetap sama */
    .section-heading {
        text-align: center;
        margin-bottom: 34px;
    }
    .section-heading h2 {
        font-size: 1.8em;
        font-weight: 700;
        color: #212529;
        margin-bottom: 8px;
    }
    .section-heading .underline {
        width: 54px;
        height: 4px;
        background: #057ff9;
        border-radius: 4px;
        margin: 0 auto;
    }
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 44px 40px; /* gap baris 44px, kolom 40px agar lebih renggang */
        margin-bottom: 32px;
        padding: 10px 8px; /* beri padding agar card tidak nempel ke tepi grid */
    }
    .gallery-card {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 6px 24px rgba(44,62,80,0.10);
        transition: transform .22s, box-shadow .22s;
        height: 100%;
        border: 1.5px solid #f1f3f7;
        display: flex;
        flex-direction: column;
        padding: 8px 0 0 0; /* beri padding atas agar isi card tidak terlalu nempel ke border */
    }
    .gallery-card:hover {
        transform: translateY(-7px) scale(1.01);
        box-shadow: 0 16px 36px rgba(44,62,80,0.16);
        border-color: #ffd700;
    }
    .gallery-media {
        width: 100%;
        height: 200px;
        object-fit: cover;
        cursor: pointer;
        background: #f8f9fa;
        border-bottom: 1.5px solid #f1f3f7;
        transition: filter 0.2s;
    }
    .gallery-cover-wrap {
        position: relative;
    }
    .gallery-carousel .carousel-control-prev,
    .gallery-carousel .carousel-control-next {
        width: 32px;
        height: 32px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(33, 37, 41, 0.68);
        border-radius: 50%;
        opacity: 0;
        transition: opacity .18s ease;
    }
    .gallery-carousel .carousel-control-prev {
        left: 10px;
    }
    .gallery-carousel .carousel-control-next {
        right: 10px;
    }
    .gallery-card:hover .carousel-control-prev,
    .gallery-card:hover .carousel-control-next {
        opacity: 1;
    }
    .gallery-media-count {
        position: absolute;
        right: 12px;
        bottom: 12px;
        background: rgba(33,37,41,.82);
        color: #fff;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 700;
        z-index: 3;
    }
    .preview-nav-btn {
        position: absolute;
        top: 45%;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 50%;
        background: rgba(33, 37, 41, 0.78);
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 5;
    }
    .preview-nav-btn:hover {
        background: rgba(33, 37, 41, 0.92);
    }
    .preview-nav-prev {
        left: 14px;
    }
    .preview-nav-next {
        right: 14px;
    }
    .preview-counter {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 5;
        background: rgba(33,37,41,.82);
        color: #fff;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 700;
        display: none;
    }
    .wa-overlay {
        position: absolute;
        left: 12px;
        bottom: 12px;
        background: rgba(37, 211, 102, .92);
        color: #fff;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(37,211,102,0.12);
    }
    .wa-overlay:hover {
        color: #fff;
        background: #1eaf56;
    }
    .gallery-card .card-body {
        padding: 18px 18px 18px 18px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        flex-grow: 1;
    }
    .gallery-badge {
        display: inline-block;
        background: #e8f1ff;
        color: #057ff9;
        font-size: .82em;
        font-weight: 700;
        padding: 4px 14px;
        border-radius: 20px;
        margin-bottom: 10px;
    }
    .gallery-card h5 {
        font-size: 1.08em;
        font-weight: 700;
        margin-bottom: 8px;
        color: #23242a;
        transition: color 0.22s ease;
    }
    .gallery-card:hover h5 {
        color: #d97706;
    }
    .gallery-card p {
        font-size: .97em;
        color: #6c757d;
        margin-bottom: 0;
        line-height: 1.6;
    }
    .services-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 28px;
        margin-bottom: 32px;
    }
    .service-card {
        background: #fff;
        border-radius: 18px;
        padding: 32px 24px 28px 24px;
        text-align: center;
        border: 1.5px solid #f1f3f7;
        box-shadow: 0 6px 24px rgba(44,62,80,0.08);
        transition: transform .22s, border-color .22s, box-shadow .22s;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .service-card:hover {
        transform: translateY(-7px) scale(1.01);
        border-color: #ffd700;
        box-shadow: 0 16px 36px rgba(44,62,80,0.13);
    }
    .service-icon {
        width: 62px;
        height: 62px;
        border-radius: 50%;
        background: #e8f1ff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 1.5em;
        color: #057ff9;
        box-shadow: 0 2px 8px rgba(5,127,249,0.08);
    }
    .service-card h5 {
        font-size: 1.08em;
        font-weight: 700;
        margin-bottom: 10px;
        color: #23242a;
        transition: color 0.22s ease;
    }
    .service-card:hover h5 {
        color: #d97706;
    }
    .service-card p {
        font-size: .97em;
        color: #6c757d;
        line-height: 1.6;
        margin: 0;
    }
    .steps-wrapper {
        background: #fff;
        border-radius: 18px;
        padding: 38px 28px;
        box-shadow: 0 6px 24px rgba(44,62,80,0.08);
        margin-bottom: 32px;
    }
    .step-item {
        text-align: center;
        padding: 12px 4px;
    }
    .step-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #057ff9;
        color: #fff;
        font-size: 1.22em;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        box-shadow: 0 2px 8px rgba(5,127,249,0.08);
    }
    .step-item h6 {
        font-size: 1em;
        font-weight: 700;
        color: #23242a;
        margin-bottom: 6px;
        transition: color 0.22s ease;
    }
    .step-item:hover h6 {
        color: #d97706;
    }
    .step-item p {
        font-size: .97em;
        color: #6c757d;
        margin-bottom: 0;
        line-height: 1.6;
    }
    .cta-banner {
        background: #212529;
        color: #fff;
        border-radius: 16px;
        padding: 44px 28px;
        text-align: center;
        margin-top: 42px;
    }
    .cta-banner p {
        color: rgba(255,255,255,.75);
        margin-bottom: 20px;
    }
    .btn-wa {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        background: #25d366;
        color: #fff;
        border-radius: 999px;
        font-weight: 700;
        text-decoration: none;
    }
    .btn-wa:hover {
        background: #1eaf56;
        color: #fff;
    }
    @media (max-width: 992px) {
        .gallery-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .services-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 2rem;
        }
        .services-grid {
            grid-template-columns: 1fr;
        }
        .gallery-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="container">
        <h1>Galeri & <span>Layanan</span></h1>
        <p>Temukan portofolio hasil pengerjaan Guna Mica, koleksi produk custom akrilik, serta layanan desain dan produksi terbaik untuk kebutuhan bisnis Anda. Klik gambar untuk memperbesar dan lihat kualitas karya kami.</p>
    </div>
</div>

<div class="container">

    @php
        $flatItems = collect($byCategory)->flatten(1);
        $groupedItems = $flatItems->groupBy('id')->values();
    @endphp
    @if ($groupedItems->isEmpty())
        <div class="alert alert-light">Belum ada data portfolio.</div>
    @else
        <div class="gallery-grid mb-4">
            @foreach ($groupedItems as $items)
                @php
                    $firstItem = $items->first();
                    $carouselId = 'galleryCarousel' . $firstItem->id;
                @endphp
                <div class="gallery-card">
                    <div class="gallery-cover-wrap">
                        <div id="{{ $carouselId }}" class="carousel slide gallery-carousel" data-bs-interval="false">
                            <div class="carousel-inner">
                                @foreach ($items as $index => $item)
                                    @php
                                        $mediaPath = 'images/' . $item->media_path;
                                        $resolvedMedia = file_exists(public_path($mediaPath)) ? asset($mediaPath) : asset('images/logoB.png');
                                        $mediaType = $item->is_video ? 'video' : 'image';
                                    @endphp
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        @if ($item->is_video)
                                            <video preload="metadata" class="gallery-media" playsinline muted data-bs-toggle="modal" data-bs-target="#galleryPreview" data-title="{{ $firstItem->judul }}" data-desc="{{ $firstItem->deskripsi }}" data-media="{{ $resolvedMedia }}" data-type="{{ $mediaType }}" data-group-id="{{ $firstItem->id }}" data-media-index="{{ $index }}">
                                                <source src="{{ $resolvedMedia }}">
                                            </video>
                                        @else
                                            <img src="{{ $resolvedMedia }}" alt="{{ $firstItem->judul }}" class="gallery-media" data-bs-toggle="modal" data-bs-target="#galleryPreview" data-title="{{ $firstItem->judul }}" data-desc="{{ $firstItem->deskripsi }}" data-media="{{ $resolvedMedia }}" data-type="{{ $mediaType }}" data-group-id="{{ $firstItem->id }}" data-media-index="{{ $index }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($items->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>

                        @if ($items->count() > 1)
                            <span class="gallery-media-count">{{ $items->count() }} Media</span>
                        @endif

                        <a href="https://wa.me/6282161807523" target="_blank" rel="noopener noreferrer" class="wa-overlay">
                            <i class="bi bi-whatsapp"></i> +62 821-6180-7523
                        </a>
                    </div>
                    <div class="card-body">
                        <span class="gallery-badge">{{ $firstItem->kategori ?: 'Umum' }}</span>
                        <h5>{{ $firstItem->judul }}</h5>
                    </div>
                </div>
            @endforeach
        </div>
    @endif



    <div class="modal fade" id="galleryPreview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-0">
                    <button type="button" class="preview-nav-btn preview-nav-prev" id="previewPrevBtn" aria-label="Media sebelumnya">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="preview-nav-btn preview-nav-next" id="previewNextBtn" aria-label="Media berikutnya">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <span class="preview-counter" id="previewCounter"></span>
                    <img src="" id="previewImage" alt="Preview" class="w-100" style="max-height:70vh;object-fit:contain;background:#111;display:none;">
                    <video id="previewVideo" class="w-100" controls style="max-height:70vh;object-fit:contain;background:#111;display:none;"></video>
                    <div class="p-3">
                        <h5 id="previewTitle" class="mb-1"></h5>
                        <p id="previewDesc" class="text-muted mb-0 small"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const preview = document.getElementById('galleryPreview');
    if (!preview) return;

    const previewImage = document.getElementById('previewImage');
    const previewVideo = document.getElementById('previewVideo');
    const previewTitle = document.getElementById('previewTitle');
    const previewDesc = document.getElementById('previewDesc');
    const previewPrevBtn = document.getElementById('previewPrevBtn');
    const previewNextBtn = document.getElementById('previewNextBtn');
    const previewCounter = document.getElementById('previewCounter');

    let currentGroupItems = [];
    let currentIndex = 0;

    const renderMedia = () => {
        if (!currentGroupItems.length) return;

        const item = currentGroupItems[currentIndex];
        const mediaType = item.getAttribute('data-type') || 'image';
        const mediaUrl = item.getAttribute('data-media') || '';

        if (mediaType === 'video') {
            previewImage.style.display = 'none';
            previewImage.src = '';
            previewVideo.style.display = 'block';
            previewVideo.src = mediaUrl;
        } else {
            previewVideo.pause();
            previewVideo.style.display = 'none';
            previewVideo.src = '';
            previewImage.style.display = 'block';
            previewImage.src = mediaUrl;
        }

        previewTitle.textContent = item.getAttribute('data-title') || 'Portfolio';
        previewDesc.textContent = item.getAttribute('data-desc') || '-';

        const hasMultiple = currentGroupItems.length > 1;
        previewPrevBtn.style.display = hasMultiple ? 'inline-flex' : 'none';
        previewNextBtn.style.display = hasMultiple ? 'inline-flex' : 'none';
        previewCounter.style.display = hasMultiple ? 'inline-block' : 'none';
        previewCounter.textContent = hasMultiple ? `${currentIndex + 1} / ${currentGroupItems.length}` : '';
    };

    previewPrevBtn.addEventListener('click', () => {
        if (!currentGroupItems.length) return;
        currentIndex = (currentIndex - 1 + currentGroupItems.length) % currentGroupItems.length;
        renderMedia();
    });

    previewNextBtn.addEventListener('click', () => {
        if (!currentGroupItems.length) return;
        currentIndex = (currentIndex + 1) % currentGroupItems.length;
        renderMedia();
    });

    preview.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const groupId = trigger.getAttribute('data-group-id');
        currentGroupItems = groupId
            ? Array.from(document.querySelectorAll(`.gallery-media[data-group-id="${groupId}"]`))
            : [trigger];

        const triggerIndex = Number.parseInt(trigger.getAttribute('data-media-index') || '0', 10);
        currentIndex = Number.isNaN(triggerIndex) ? 0 : Math.max(0, triggerIndex);
        if (currentIndex >= currentGroupItems.length) currentIndex = 0;

        renderMedia();
    });

    preview.addEventListener('hidden.bs.modal', () => {
        previewVideo.pause();
        previewVideo.src = '';
        previewImage.src = '';
        currentGroupItems = [];
        currentIndex = 0;
    });

    preview.addEventListener('keydown', (event) => {
        if (!currentGroupItems.length || currentGroupItems.length < 2) return;
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            previewPrevBtn.click();
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            previewNextBtn.click();
        }
    });
})();
</script>
@endpush
