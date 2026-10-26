<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Guna Mica')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logogunamica.png') }}?v=3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(90deg, #23242a 0%, #383c44 100%) !important;
            box-shadow: 0 4px 18px rgba(44, 62, 80, 0.08);
        }
        .navbar .nav-link,
        .navbar-brand,
        .navbar .dropdown-toggle {
            color: #fff !important;
            transition: color 0.2s;
        }
        .navbar .nav-link {
            font-weight: 500;
            margin: 0 10px;
            border-radius: 8px;
            padding: 7px 16px;
            position: relative;
        }
        .navbar .nav-link:hover,
        .navbar .nav-link:focus {
            color: #ffd700 !important;
            background: rgba(255,255,255,0.07);
        }
        .navbar-nav {
            align-items: center;
        }
        .navbar-logo {
            width: 48px;
            height: 36px;
            margin-right: 12px;
            display: inline-block;
            vertical-align: middle;
            object-fit: contain;
            border-radius: 12px;
            background: #23242a;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
        .custom-active {
            color: #ffd700 !important;
            background: rgba(255,255,255,0.09) !important;
            border-radius: 8px;
        }
        .nav-link:not(.custom-active) {
            color: #fff !important;
        }
        .cart-badge {
            position: absolute;
            top: 0;
            right: -10px;
            background: #dc3545;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
            border-radius: 20px;
            padding: 2px 6px;
            line-height: 1;
            box-shadow: 0 2px 8px rgba(220,53,69,0.18);
        }
        .profile-thumb {
            width: 34px;
            height: 34px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid #ffd700;
        }
        .footer {
            background: linear-gradient(90deg, #23242a 0%, #383c44 100%);
            color: #e9ecef;
            padding: 48px 0 28px;
            font-size: 1em;
            margin-top: 56px;
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -2px 16px rgba(44, 62, 80, 0.08);
        }
        .footer h6 {
            color: #fff;
            margin-bottom: 22px;
            font-weight: 700;
            font-size: 1.15em;
            letter-spacing: 1px;
        }
        .footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer ul li {
            margin-bottom: 12px;
        }
        .footer a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.25s, text-shadow 0.25s;
        }
        .footer a:hover {
            color: #ffd700;
            text-shadow: 0 2px 8px rgba(255,215,0,0.18);
        }
        .footer-info {
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            color: #adb5bd;
        }
        .footer-info i {
            font-size: 22px;
            margin-right: 12px;
            width: 26px;
            text-align: center;
        }
        .bi-whatsapp.footer-icon {
            color: #25D366;
        }
        .bi-envelope-fill.footer-icon {
            color: #EA4335;
        }
        .bi-instagram.footer-icon {
            color: #E1306C;
        }
        .footer hr {
            border-top: 1px solid #495057;
            margin-top: 24px;
            margin-bottom: 18px;
        }
        .gm-notify-modal .modal-content {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
        }
        .gm-notify-modal .modal-header {
            border-bottom: 1px solid #eef2f7;
            padding: 14px 18px;
        }
        .gm-notify-modal .modal-title {
            font-weight: 700;
            color: #23242a;
            font-size: 1.05rem;
        }
        .gm-notify-modal .modal-body {
            padding: 16px 18px;
            color: #3d4752;
            line-height: 1.55;
        }
        .gm-notify-modal .modal-footer {
            border-top: 1px solid #eef2f7;
            padding: 12px 18px 16px;
            gap: 8px;
        }
        @media (max-width: 991px) {
            .navbar .nav-link {
                margin: 4px 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $isUser = auth()->check() && auth()->user()->role === 'user';
    $isGuest = !auth()->check();
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    $cartCount = 0;
    $hideFooter = request()->routeIs('cart.index')
        || request()->routeIs('user.orders.history')
        || request()->routeIs('user.orders.checkout')
        || request()->routeIs('user.orders.track')
        || request()->routeIs('user.profile.edit');
    $showChatbot = request()->routeIs('home');

    $isActive = function (array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };

    if ($isUser) {
        $cartCount = (int) \App\Models\CartItem::query()->where('user_id', auth()->id())->sum('quantity');
    } elseif ($isGuest) {
        $cartCount = (int) collect(session('guest_cart', []))->sum(fn ($item) => (int) ($item['quantity'] ?? 0));
    }
@endphp
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.dashboard') : route('home') }}" style="gap: 8px;">
            <img src="{{ asset('images/logogunamica.png') }}" alt="Logo Guna Mica" class="navbar-logo" style="width:68px;height:38px;border-radius:14px;background:#23242a;box-shadow:0 2px 8px rgba(0,0,0,0.18);object-fit:contain;">
            <span style="font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;font-weight:700;font-size:1.45rem;letter-spacing:1px;color:#e6e6e6;text-shadow:0 2px 8px rgba(0,0,0,0.18);">GUNA MICA</span>
        </a>
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    @if ($isAdmin)
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.products.index') }}">Produk</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Pengguna</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.shipping.index') }}">Pengiriman</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.portfolio.index') }}">Portfolio</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.faq.index') }}">FAQ Chatbot</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link {{ $isActive(['home']) ? 'custom-active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
                        <li class="nav-item"><a class="nav-link {{ $isActive(['user.products.*', 'products.guest*']) ? 'custom-active' : '' }}" href="{{ route('user.products.index') }}">Produk</a></li>
                        <li class="nav-item"><a class="nav-link {{ $isActive(['galeri']) ? 'custom-active' : '' }}" href="{{ route('galeri') }}">Galeri & Layanan</a></li>
                        <li class="nav-item position-relative">
                            <a class="nav-link {{ $isActive(['cart.index']) ? 'custom-active' : '' }}" href="{{ route('cart.index') }}" aria-label="Keranjang">
                                <i class="bi bi-cart-fill"></i>
                                @if ($cartCount > 0)
                                    <span class="cart-badge">{{ $cartCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                @else
                    <li class="nav-item"><a class="nav-link {{ $isActive(['home']) ? 'custom-active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link {{ $isActive(['products.guest*']) ? 'custom-active' : '' }}" href="{{ route('products.guest') }}">Produk</a></li>
                    <li class="nav-item"><a class="nav-link {{ $isActive(['galeri']) ? 'custom-active' : '' }}" href="{{ route('galeri') }}">Galeri & Layanan</a></li>
                    <li class="nav-item"><a class="nav-link {{ $isActive(['about']) ? 'custom-active' : '' }}" href="{{ route('about') }}">Tentang Kami</a></li>
                    <li class="nav-item position-relative">
                        <a class="nav-link {{ $isActive(['cart.index']) ? 'custom-active' : '' }}" href="{{ route('cart.index') }}" aria-label="Keranjang">
                            <i class="bi bi-cart-fill"></i>
                            @if ($cartCount > 0)
                                <span class="cart-badge">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link {{ $isActive(['login']) ? 'custom-active' : '' }}" href="{{ route('login') }}">Login</a></li>
                @endauth
            </ul>
            @auth
                @if (!$isAdmin)
                    @php
                        $profileImage = (string) (auth()->user()->profile_image ?? '');
                        if (filter_var($profileImage, FILTER_VALIDATE_URL)) {
                            $profileSrc = $profileImage;
                        } elseif ($profileImage !== '' && file_exists(public_path('uploads/profiles/' . $profileImage))) {
                            $profileSrc = asset('uploads/profiles/' . $profileImage);
                        } elseif ($profileImage !== '' && file_exists(public_path('uploads/' . $profileImage))) {
                            $profileSrc = asset('uploads/' . $profileImage);
                        } else {
                            $profileSrc = asset('images/logoB.png');
                        }
                    @endphp
                    <a href="{{ route('user.orders.history') }}" class="nav-link d-flex align-items-center me-2 {{ $isActive(['user.orders.history']) ? 'custom-active' : '' }}">
                        <i class="bi bi-clock-history me-1"></i> Riwayat
                    </a>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ $profileSrc }}" class="profile-thumb" alt="Profile">{{ auth()->user()->username }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('user.profile.edit') }}">Profil</a></li>
                            <li><a class="dropdown-item" href="{{ route('user.orders.track') }}">Lacak Barang</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="post" class="px-2">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger w-100" type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <span class="text-white me-3">{{ auth()->user()->username }}</span>
                    <form action="{{ route('logout') }}" method="post" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-light" type="submit">Logout</button>
                    </form>
                @endif
            @endauth
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container mb-3">
        @if (session('success') && !request()->routeIs('cart.index'))
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
        @endif
        @if (session('error') && !request()->routeIs('cart.index'))
            <div class="alert alert-danger mb-0">{{ session('error') }}</div>
        @endif
        @if (session('notice') && !request()->routeIs('cart.index'))
            <div class="alert alert-warning mb-0">{{ session('notice') }}</div>
        @endif
    </div>
    @yield('content')
</main>

@if ((!auth()->check() || auth()->user()->role !== 'admin') && !$hideFooter)
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <h6>Guna Mica</h6>
                    <p class="mb-0">Spesialis produk akrilik custom, display usaha, dan solusi branding visual untuk bisnis Anda.</p>
                </div>
                <div class="col-md-3">
                    <h6>Navigasi</h6>
                    <ul>
                        <li><a href="{{ route('home') }}">Beranda</a></li>
                        <li><a href="{{ auth()->check() ? route('user.products.index') : route('products.guest') }}">Produk</a></li>
                        <li><a href="{{ route('galeri') }}">Galeri & Layanan</a></li>
                        @guest
                            <li><a href="{{ route('about') }}">Tentang Kami</a></li>
                        @endguest
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Bantuan</h6>
                    <ul>
                        <li><a href="{{ route('about') }}">Cara Pemesanan</a></li>
                        <li><a href="{{ route('about') }}">Kebijakan Privasi</a></li>
                        <li><a href="{{ route('about') }}">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Kontak</h6>
                    <div class="footer-info">
                        <a href="https://wa.me/6282161807523" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp footer-icon"></i>+62 821-6180-7523
                        </a>
                    </div>
                    <div class="footer-info">
                        <a href="mailto:gunamicaadvertising98@gmail.com" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-envelope-fill footer-icon"></i>gunamicaadvertising98@gmail.com
                        </a>
                    </div>
                    <div class="footer-info">
                        <a href="https://www.instagram.com/gunamicaadvertising" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-instagram footer-icon"></i>@gunamicaadvertising
                        </a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">&copy; {{ now()->year }} Guna Mica. All rights reserved.</div>
        </div>
    </footer>
@endif

<div class="modal fade gm-notify-modal" id="gmNotifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gmNotifyTitle">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="gmNotifyMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="gmNotifyCancel">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="gmNotifyOk">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const modalEl = document.getElementById('gmNotifyModal');
    if (!modalEl || !window.bootstrap) return;

    const titleEl = document.getElementById('gmNotifyTitle');
    const messageEl = document.getElementById('gmNotifyMessage');
    const cancelBtn = document.getElementById('gmNotifyCancel');
    const okBtn = document.getElementById('gmNotifyOk');
    const modal = new bootstrap.Modal(modalEl, {backdrop: 'static', keyboard: false});
    let resolver = null;

    function showDialog({ title = 'Informasi', message = '', isConfirm = false, okText = 'OK', cancelText = 'Batal' }) {
        titleEl.textContent = title;
        messageEl.textContent = String(message ?? '');
        okBtn.textContent = okText;
        cancelBtn.textContent = cancelText;
        cancelBtn.style.display = isConfirm ? 'inline-block' : 'none';

        return new Promise((resolve) => {
            resolver = resolve;
            modal.show();
        });
    }

    okBtn.addEventListener('click', () => {
        if (resolver) resolver(true);
        resolver = null;
        modal.hide();
    });

    cancelBtn.addEventListener('click', () => {
        if (resolver) resolver(false);
        resolver = null;
        modal.hide();
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        if (resolver) {
            resolver(false);
            resolver = null;
        }
    });

    window.gmAlert = function (message, title = 'Informasi') {
        return showDialog({ title, message, isConfirm: false, okText: 'OK' });
    };

    window.gmConfirm = function (message, options = {}) {
        return showDialog({
            title: options.title || 'Konfirmasi',
            message,
            isConfirm: true,
            okText: options.okText || 'Ya',
            cancelText: options.cancelText || 'Batal'
        });
    };

    window.gmConfirmDialog = function (trigger, event, message, title = 'Konfirmasi') {
        if (event) event.preventDefault();
        const form = trigger && trigger.tagName === 'FORM' ? trigger : trigger?.closest?.('form');
        if (!form) return false;

        window.gmConfirm(message, { title }).then((confirmed) => {
            if (confirmed) form.submit();
        });
        return false;
    };

    const nativeAlert = window.alert;
    window.alert = function (message) {
        window.gmAlert(message, 'Pemberitahuan');
    };
    window._gmNativeAlert = nativeAlert;
})();

function localizeTime(selector) {
    const elements = document.querySelectorAll(selector);
    const formatter = new Intl.DateTimeFormat('id-ID', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
        hour12: false
    });

    elements.forEach(el => {
        const timestr = el.getAttribute('data-utc');
        if (!timestr || timestr === '-') return;
        
        try {
            // Server sends time in Asia/Jakarta (UTC+7)
            const dt = new Date(timestr.replace(' ', 'T') + '+07:00');
            if (!isNaN(dt)) {
                el.textContent = formatter.format(dt);
            }
        } catch (e) {
            console.error('Error localizing time:', e);
        }
    });
}
window.addEventListener('DOMContentLoaded', () => localizeTime('.local-time'));
</script>
@if ((!auth()->check() || auth()->user()->role !== 'admin') && $showChatbot)
    <div id="gm-asisten-root" data-chat-api="https://gunamica.me/public/asisten/message" data-csrf="{{ csrf_token() }}" data-logo="{{ asset('images/logogunamica.png') }}"></div>
    <script src="{{ asset('js/asisten-widget.js') }}?v={{ file_exists(public_path('js/asisten-widget.js')) ? filemtime(public_path('js/asisten-widget.js')) : 1 }}"></script>
@endif
@stack('scripts')
</body>
</html>
