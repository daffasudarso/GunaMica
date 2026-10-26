<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin - Guna Mica')</title>
    <link rel="icon" href="{{ asset('images/logogunamica.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            margin: 0;
            background: #f1f3f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-shell {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }
        .admin-sidebar {
            background: #1f2630;
            color: #fff;
            padding: 14px 0;
        }
        .admin-brand {
            padding: 8px 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.05rem;
        }
        .admin-brand img {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            object-fit: cover;
        }
        .admin-menu {
            list-style: none;
            margin: 0;
            padding: 10px 0;
        }
        .admin-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            font-size: .93rem;
            padding: 10px 18px;
            transition: background .2s;
        }
        .admin-menu a:hover,
        .admin-menu a.active {
            background: rgba(255,255,255,.1);
        }
        .admin-content {
            min-width: 0;
        }
        .admin-topbar {
            background: #fff;
            border-bottom: 1px solid #e6e9ef;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-main {
            padding: 18px;
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
        @media (max-width: 991.98px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $isActive = function (array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };
@endphp
@php
    $adminProfileImage = (string) (auth()->user()->profile_image ?? '');
    $adminProfileSrc = $adminProfileImage !== '' && file_exists(public_path('uploads/profiles/' . $adminProfileImage))
        ? asset('uploads/profiles/' . $adminProfileImage)
        : asset('images/logoB.png');
@endphp
<div class="admin-shell">
    <aside class="admin-sidebar offcanvas-lg offcanvas-start text-bg-dark" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel" style="background-color: #1f2630 !important;">
        <div class="offcanvas-header d-lg-none" style="border-bottom: 1px solid rgba(255,255,255,.08);">
            <h5 class="offcanvas-title text-white d-flex align-items-center" id="adminSidebarLabel" style="font-weight:700; font-size:1.05rem;">
                <img src="{{ $adminProfileSrc }}" alt="Foto Profil Admin" style="width:34px;height:34px;border-radius:8px;object-fit:cover;margin-right:10px;">
                GUNA MICA
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-column p-0">
            <div class="admin-brand d-none d-lg-flex">
                <img src="{{ $adminProfileSrc }}" alt="Foto Profil Admin">
                <span>GUNA MICA</span>
            </div>
            <ul class="admin-menu w-100">
                <li><a class="{{ $isActive(['admin.dashboard']) ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a class="{{ $isActive(['admin.products.*']) ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="bi bi-plus-circle"></i> Tambah Produk</a></li>
                <li><a class="{{ $isActive(['admin.users.*']) ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> Pelanggan</a></li>
                <li><a class="{{ $isActive(['admin.shipping.*']) ? 'active' : '' }}" href="{{ route('admin.shipping.index') }}"><i class="bi bi-truck"></i> Pengiriman</a></li>
                <li><a class="{{ $isActive(['admin.portfolio.*']) ? 'active' : '' }}" href="{{ route('admin.portfolio.index') }}"><i class="bi bi-images"></i> Portfolio</a></li>
                <li><a class="{{ $isActive(['admin.reviews.*']) ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}"><i class="bi bi-chat-square-text"></i> Ulasan</a></li>
                <li><a class="{{ $isActive(['admin.faq.*']) ? 'active' : '' }}" href="{{ route('admin.faq.index') }}"><i class="bi bi-robot"></i> FAQ Asisten</a></li>
                <li><a class="{{ $isActive(['admin.asisten-logs.*']) ? 'active' : '' }}" href="{{ route('admin.asisten-logs.index') }}"><i class="bi bi-journal-text"></i> Log Asisten</a></li>
                <li><a class="{{ $isActive(['admin.profile.*']) ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person-gear"></i> Profil Admin</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="post" class="px-3 pt-2">
                        @csrf
                        <button class="btn btn-sm btn-outline-light w-100" type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </aside>

    <section class="admin-content">
        <div class="admin-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div class="small text-muted">Halo, {{ auth()->user()->username }}</div>
            </div>
        </div>

        <main class="admin-main">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </section>
</div>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
