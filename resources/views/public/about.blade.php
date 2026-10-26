@extends('layouts.app')

@section('title', 'Tentang Kami - Guna Mica')

@push('styles')
<style>
    /* Premium About Hero Section */
    .about-hero {
        text-align: center;
        padding: 40px 0 20px 0;
        margin-bottom: 32px;
        color: #0f172a;
    }
    .about-hero-content {
        position: relative;
        z-index: 2;
        max-width: 700px;
        margin: 0 auto;
    }
    .about-hero h1 {
        font-weight: 900;
        font-size: 2.8rem;
        letter-spacing: -1.5px;
        line-height: 1.15;
        margin-bottom: 12px;
        color: #0f172a;
    }
    .about-hero h1 span {
        background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .about-hero p {
        font-size: 1.05rem;
        color: #475569;
        margin-bottom: 0;
        font-weight: 500;
        line-height: 1.6;
    }

    /* Premium Section Cards */
    .about-section-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        padding: 48px 36px;
        margin-bottom: 48px !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.015);
    }
    .section-title {
        text-align: center;
        font-weight: 900;
        margin-bottom: 10px;
        color: #0f172a;
        font-size: 1.8rem;
        letter-spacing: -0.5px;
    }
    .underline-line {
        width: 54px;
        height: 4px;
        background: #ffd700;
        border-radius: 4px;
        margin: 0 auto 32px auto;
    }

    /* Vision & Mission Bento Grid */
    .vision-box {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 32px 28px;
        height: 100%;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.01);
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .vision-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        border-color: rgba(255, 215, 0, 0.4);
    }
    .vision-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255, 215, 0, 0.1);
        color: #d97706;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        font-size: 1.35rem;
    }
    .vision-box:hover .vision-icon {
        background: #ffd700;
        color: #0f172a;
    }
    .vision-box h5 {
        font-size: 1.12rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
        transition: color 0.22s ease;
    }
    .vision-box:hover h5 {
        color: #d97706;
    }
    .vision-box p, .vision-box ul {
        font-size: 0.92rem;
        color: #475569;
        line-height: 1.6;
    }
    .mission-list {
        padding-left: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .mission-list li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .mission-list li i {
        color: #10b981;
        margin-top: 2px;
    }

    /* Why Us Premium 3-Color Cards */
    .why-card {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 36px 24px;
        text-align: center;
        height: 100%;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.01);
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .why-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
        border-color: rgba(255, 215, 0, 0.4);
    }
    .why-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        font-size: 1.45rem;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.03);
    }
    /* Gold themed for Quality */
    .why-card.quality .why-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
    }
    .why-card.quality:hover .why-icon {
        background: #fbbf24;
        color: #0f172a;
    }
    /* Blue themed for Service */
    .why-card.service .why-icon {
        background: rgba(5, 127, 249, 0.1);
        color: #057ff9;
    }
    .why-card.service:hover .why-icon {
        background: #057ff9;
        color: #ffffff;
    }
    /* Green themed for Safety */
    .why-card.safety .why-icon {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }
    .why-card.safety:hover .why-icon {
        background: #10b981;
        color: #ffffff;
    }

    .why-card h5 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 10px;
        transition: color 0.22s ease;
    }
    .why-card:hover h5 {
        color: #d97706;
    }
    .why-card p {
        font-size: 0.88rem;
        color: #475569;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* Layanan Kami Premium Bento Cards */
    .service-card {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 36px 24px;
        text-align: center;
        height: 100%;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.01);
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        margin-bottom: 8px;
    }
    .service-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
        border-color: rgba(255, 215, 0, 0.4);
    }
    .service-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(255, 215, 0, 0.1);
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px auto;
        font-size: 1.45rem;
    }
    .service-card:hover .service-icon {
        background: #ffd700;
        color: #0f172a;
    }
    .service-card h5 {
        font-size: 1.1rem;
        font-weight: 800;
        margin-bottom: 10px;
        color: #0f172a;
        transition: color 0.22s ease;
    }
    .service-card:hover h5 {
        color: #d97706;
    }
    .service-card p {
        font-size: 0.88rem;
        color: #475569;
        line-height: 1.6;
        margin: 0;
    }

    /* Alur Pemesanan Interaktif (Timeline) */
    .step-item {
        text-align: center;
        padding: 24px 16px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.01);
        height: 100%;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .step-item:hover {
        transform: translateY(-5px);
        border-color: rgba(255, 215, 0, 0.4);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.04);
    }
    .step-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255, 215, 0, 0.1);
        color: #d97706;
        font-size: 1.2rem;
        font-weight: 850;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px auto;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.06);
    }
    .step-item:hover .step-circle {
        background: #ffd700;
        color: #0f172a;
    }
    .step-item h6 {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
        transition: color 0.22s ease;
    }
    .step-item:hover h6 {
        color: #d97706;
    }
    .step-item p {
        font-size: 0.85rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 24px 16px;
        }
        .about-hero h1 {
            font-size: 2rem;
        }
        .about-section-card {
            padding: 28px 16px;
        }
        .section-title {
            font-size: 1.45rem;
        }
    }
</style>
@endpush

@section('content')
<div class="about-hero" id="aboutHero">
    <div class="about-hero-content">
        <h1>Tentang <span>Guna Mica</span></h1>
        <p>Solusi terpercaya penyedia produk dan jasa fabrikasi akrilik kustom terbaik untuk bisnis Anda.</p>
    </div>
</div>

<div class="container">
    <!-- Section: Solusi Akrilik Terpercaya -->
    <div class="about-section-card mb-4">
        <h4 class="section-title">Solusi Akrilik Terpercaya</h4>
        <div class="underline-line"></div>
        <p class="text-center text-muted mb-0" style="max-width: 800px; margin: 0 auto; font-size: 1.02rem; line-height: 1.7;">
            Tingkatkan daya tarik visual bisnis Anda dengan sentuhan akrilik berkualitas dari Guna Mica. 
            Kami melayani kebutuhan retail maupun perusahaan skala besar dengan jaminan presisi tinggi, material kelas satu, dan layanan ekspres yang responsif. 
            Solusi tepat untuk kebutuhan branding yang lebih berkelas dan premium!
        </p>
    </div>

    <!-- Section: Visi & Misi Bento Grid -->
    <div class="about-section-card mb-4">
        <h4 class="section-title">Visi & Misi Kami</h4>
        <div class="underline-line"></div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="vision-box">
                    <span class="vision-icon"><i class="bi bi-eye-fill"></i></span>
                    <h5>Visi Guna Mica</h5>
                    <p class="mb-0">Menjadi penyedia produk akrilik kustom terpercaya di Indonesia dengan kualitas unggul, kreativitas tanpa batas, serta pelayanan profesional yang memuaskan.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="vision-box">
                    <span class="vision-icon"><i class="bi bi-clipboard-check-fill"></i></span>
                    <h5>Misi Guna Mica</h5>
                    <ul class="mission-list mb-0">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Menyediakan produk akrilik presisi dan berkualitas tinggi menggunakan teknologi mutakhir.</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Mengutamakan kecepatan produksi dan ketepatan waktu pengiriman barang ke seluruh Indonesia.</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Memberikan konsultasi desain secara cuma-cuma demi mengakomodasi kebutuhan spesifik pelanggan.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Mengapa Memilih Kami -->
    <div class="about-section-card mb-4">
        <h4 class="section-title">Mengapa Memilih Kami?</h4>
        <div class="underline-line"></div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="why-card quality">
                    <div class="why-icon"><i class="bi bi-award-fill"></i></div>
                    <h5>Kualitas Terbaik</h5>
                    <p>Kami menyeleksi bahan akrilik impor premium dengan pengerjaan tepi akrilik yang licin berkilau dan presisi tinggi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="why-card service">
                    <div class="why-icon"><i class="bi bi-headset"></i></div>
                    <h5>Layanan Responsif</h5>
                    <p>Tim dukungan sales kami selalu siaga mendampingi Anda berdiskusi mengenai sketsa desain dan ketebalan material.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="why-card safety">
                    <div class="why-icon"><i class="bi bi-shield-fill-check"></i></div>
                    <h5>Pengiriman Aman</h5>
                    <p>Menggunakan sistem kemasan kayu kokoh dan pelindung gelembung tebal demi keamanan barang Anda hingga di tujuan.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Layanan Kami -->
    <div class="about-section-card mb-4">
        <h4 class="section-title">Layanan Kami</h4>
        <div class="underline-line"></div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="bi bi-brush"></i></div>
                    <h5>Desain Custom</h5>
                    <p>Desain menyesuaikan identitas brand, ukuran, dan kebutuhan ruang pemasangan Anda.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="bi bi-cpu"></i></div>
                    <h5>Produksi Presisi</h5>
                    <p>Pemotongan dan finishing rapi dengan standar kualitas material akrilik terbaik.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card">
                    <div class="service-icon"><i class="bi bi-globe-asia-australia"></i></div>
                    <h5>Pengiriman Nasional</h5>
                    <p>Dukungan pengemasan aman dan pengiriman ke seluruh Indonesia dengan tracking resi.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Alur Pemesanan -->
    <div class="about-section-card mb-4">
        <h4 class="section-title">Alur Pemesanan</h4>
        <div class="underline-line"></div>
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="step-item">
                    <div class="step-circle">1</div>
                    <h6>Konsultasi</h6>
                    <p>Diskusikan kebutuhan produk, ukuran, dan konsep visual bersama tim kami.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="step-item">
                    <div class="step-circle">2</div>
                    <h6>Penawaran Harga</h6>
                    <p>Kami berikan rincian biaya sesuai spesifikasi dan timeline pengerjaan.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="step-item">
                    <div class="step-circle">3</div>
                    <h6>Produksi</h6>
                    <p>Proses pembuatan dikerjakan oleh tim berpengalaman dengan kontrol kualitas.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="step-item">
                    <div class="step-circle">4</div>
                    <h6>Pengiriman</h6>
                    <p>Pesanan dikirim aman dan bisa dilacak lewat fitur lacak barang di akun Anda.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

