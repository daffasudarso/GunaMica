@extends('layouts.app')

@section('title', 'Profil - Guna Mica')

@push('styles')
<style>
    .profile-shell {
        max-width: 700px;
        margin: 0 auto;
        background: #fff;
        padding: 34px 26px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }
    .profile-pic {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid #0d6efd;
        margin-bottom: 16px;
        cursor: pointer;
        transition: transform .25s ease;
    }
    .profile-pic:hover {
        transform: scale(1.04);
    }
    .profile-title {
        font-weight: 700;
    }
    .fullscreen-img {
        display: none;
        position: fixed;
        z-index: 9999;
        inset: 0;
        background: rgba(0, 0, 0, 0.9);
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding: 16px;
    }
    .fullscreen-img img {
        max-width: 90%;
        max-height: 85%;
        border-radius: 10px;
        margin-bottom: 16px;
    }

    /* Hide layout level alerts on this page */
    main > .container > .alert {
        display: none !important;
    }

    /* Floating Toast Container */
    .toast-container-profile {
        position: fixed;
        top: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        width: 100%;
        max-width: 420px;
        padding: 0 16px;
        animation: toast-slide-down 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes toast-slide-down {
        from {
            transform: translate(-50%, -20px);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    .toast-card-profile {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.8);
        overflow: hidden;
        position: relative;
    }

    .toast-card-profile.toast-success {
        border-left: 4px solid #10b981; /* Emerald-500 */
    }

    .toast-card-profile.toast-error {
        border-left: 4px solid #ef4444; /* Red-500 */
    }

    .toast-card-profile.toast-warning {
        border-left: 4px solid #f59e0b; /* Amber-500 */
    }

    .toast-body-profile {
        padding: 16px 48px 16px 16px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
    }

    .icon-success {
        color: #10b981;
        font-size: 1.15rem;
    }

    .icon-error {
        color: #ef4444;
        font-size: 1.15rem;
    }

    .icon-warning {
        color: #f59e0b;
        font-size: 1.15rem;
    }

    .btn-close-profile {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #94a3b8;
        font-size: 1.25rem;
        cursor: pointer;
        transition: color 0.15s ease;
        line-height: 1;
        padding: 0;
    }

    .btn-close-profile:hover {
        color: #475569;
    }

    /* Countdown bar */
    .toast-progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: #f1f5f9;
    }

    .toast-progress-fill {
        height: 100%;
        width: 100%;
        background: #10b981;
        transition: width 5s linear;
    }

    .toast-card-profile.toast-error .toast-progress-fill {
        background: #ef4444;
    }

    .toast-card-profile.toast-warning .toast-progress-fill {
        background: #f59e0b;
    }
</style>
@endpush

@section('content')
<div class="container">
    @if (session('success') || session('error') || session('notice') || session('status'))
        @php
            $toastType = session('success') || session('status') ? 'success' : (session('error') ? 'error' : 'warning');
            $toastMessage = session('success') ?: (session('status') ?: (session('error') ?: session('notice')));
        @endphp
        <div class="toast-container-profile" id="profileToast">
            <div class="toast-card-profile toast-{{ $toastType }}">
                <div class="toast-body-profile">
                    @if ($toastType === 'success')
                        <i class="bi bi-check-circle-fill icon-success me-2"></i>
                    @elseif ($toastType === 'error')
                        <i class="bi bi-exclamation-triangle-fill icon-error me-2"></i>
                    @else
                        <i class="bi bi-exclamation-circle-fill icon-warning me-2"></i>
                    @endif
                    <span>{{ $toastMessage }}</span>
                    <button type="button" class="btn-close-profile" onclick="dismissProfileToast()" aria-label="Close">&times;</button>
                </div>
                <div class="toast-progress-bar">
                    <div class="toast-progress-fill" id="toastProgress"></div>
                </div>
            </div>
        </div>
    @endif
    @php
        $profileImage = (string) ($user->profile_image ?? '');
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

    <div class="profile-shell text-center">
        <h4 class="profile-title mb-3">Profil Pengguna</h4>

        <img src="{{ $profileSrc }}" alt="Foto Profil" class="profile-pic" id="profileImage">

        <form action="{{ route('user.profile.update') }}" method="post" enctype="multipart/form-data" class="text-start mt-3">
            @csrf
            @method('PUT')

            <div class="mb-2">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="username" value="{{ old('username', $user->username) }}" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" name="alamat" rows="3">{{ old('alamat', $user->alamat) }}</textarea>
            </div>
            <div class="mb-2">
                <label class="form-label">Foto Profil</label>
                <input type="file" class="form-control" name="profile_image" accept="image/*">
            </div>
            <div class="mb-2">
                <label class="form-label">Password Baru (opsional)</label>
                <input type="password" class="form-control" name="password">
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" name="password_confirmation">
            </div>

            <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-primary btn-sm px-3" type="submit">Simpan Perubahan</button>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm px-3">&larr; Kembali ke Beranda</a>
            </div>
        </form>
    </div>

    <div class="fullscreen-img" id="fullscreenContainer">
        <img src="{{ $profileSrc }}" alt="Fullscreen Profile">
        <button class="btn btn-light" id="closeFullscreen" type="button">Tutup</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const profileImage = document.getElementById('profileImage');
    const fullscreen = document.getElementById('fullscreenContainer');
    const closeButton = document.getElementById('closeFullscreen');

    if (!profileImage || !fullscreen || !closeButton) return;

    profileImage.addEventListener('click', () => {
        fullscreen.style.display = 'flex';
    });

    closeButton.addEventListener('click', () => {
        fullscreen.style.display = 'none';
    });

    fullscreen.addEventListener('click', (event) => {
        if (event.target === fullscreen) {
            fullscreen.style.display = 'none';
        }
    });
})();

(() => {
    const toast = document.getElementById('profileToast');
    const fill = document.getElementById('toastProgress');
    if (!toast) return;

    // Start progress transition (5s)
    setTimeout(() => {
        if (fill) {
            fill.style.width = '0%';
        }
    }, 50);

    // Auto dismiss after 5s
    setTimeout(() => {
        dismissProfileToast();
    }, 5000);
})();

window.dismissProfileToast = function() {
    const toast = document.getElementById('profileToast');
    if (!toast) return;
    toast.style.transition = 'all 0.4s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translate(-50%, -20px)';
    setTimeout(() => {
        toast.remove();
    }, 400);
};
</script>
@endpush
