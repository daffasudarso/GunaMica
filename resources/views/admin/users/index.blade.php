@extends('layouts.admin')

@section('title', 'Admin Pengguna - Guna Mica')

@push('styles')
<style>
    /* Hide layout alerts on this page */
    .admin-main > .alert {
        display: none !important;
    }

    /* Floating Toast Container */
    .toast-container-admin {
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

    .toast-card-admin {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.8);
        overflow: hidden;
        position: relative;
    }

    .toast-card-admin.toast-success {
        border-left: 4px solid #10b981; /* Emerald-500 */
    }

    .toast-card-admin.toast-error {
        border-left: 4px solid #ef4444; /* Red-500 */
    }

    .toast-body-admin {
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

    .btn-close-admin {
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

    .btn-close-admin:hover {
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

    .toast-card-admin.toast-error .toast-progress-fill {
        background: #ef4444;
    }
</style>
@endpush

@section('content')
<div class="container">
    @if (session('success') || session('error'))
        <div class="toast-container-admin" id="adminUsersToast">
            <div class="toast-card-admin {{ session('success') ? 'toast-success' : 'toast-error' }}">
                <div class="toast-body-admin">
                    @if (session('success'))
                        <i class="bi bi-check-circle-fill me-2 icon-success"></i>
                        <span>{{ session('success') }}</span>
                    @else
                        <i class="bi bi-exclamation-triangle-fill me-2 icon-error"></i>
                        <span>{{ session('error') }}</span>
                    @endif
                    <button type="button" class="btn-close-admin" onclick="dismissAdminToast()" aria-label="Close">×</button>
                </div>
                <div class="toast-progress-bar">
                    <div class="toast-progress-fill" id="toastProgress"></div>
                </div>
            </div>
        </div>
    @endif

    <h4 class="mb-3">Kelola Pengguna Admin</h4>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-9"><input class="form-control" name="search" value="{{ $search }}" placeholder="Cari nama/email..."></div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary">Cari</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Foto</th>
                    <th>Role</th>
                    <th>Alamat</th>
                    <th width="260">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
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
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td><img src="{{ $profileSrc }}" alt="{{ $user->username }}" style="width:42px;height:42px;object-fit:cover;border-radius:50%;"></td>
                        <td>{{ $user->role }}</td>
                        <td>{{ $user->alamat }}</td>
                        <td>
                            <details>
                                <summary class="btn btn-sm btn-outline-primary py-0 px-2">Edit</summary>
                                <form action="{{ route('admin.users.update', $user->id) }}" method="post" class="mt-2 d-grid gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input class="form-control form-control-sm" name="username" value="{{ $user->username }}" required>
                                    <input class="form-control form-control-sm" type="email" name="email" value="{{ $user->email }}" required>
                                    <input class="form-control form-control-sm" type="password" name="password" placeholder="Password baru (opsional)">
                                    <select class="form-select form-select-sm" name="role">
                                        <option value="user" @selected($user->role === 'user')>user</option>
                                        <option value="admin" @selected($user->role === 'admin')>admin</option>
                                    </select>
                                    <textarea class="form-control form-control-sm" name="alamat" rows="2">{{ $user->alamat }}</textarea>
                                    <button class="btn btn-sm btn-primary py-0 px-2">Simpan</button>
                                </form>
                            </details>
                            <form class="mt-2" action="{{ route('admin.users.destroy', $user->id) }}" method="post" onsubmit="return gmConfirmDialog(this, event, 'Hapus pengguna ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger py-0 px-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
@endsection

@push('scripts')
<script>
(() => {
    const toast = document.getElementById('adminUsersToast');
    const fill = document.getElementById('toastProgress');
    if (!toast) return;

    // Start progress transition
    setTimeout(() => {
        if (fill) {
            fill.style.width = '0%';
        }
    }, 50);

    // Auto dismiss after 5s
    setTimeout(() => {
        dismissAdminToast();
    }, 5000);
})();

function dismissAdminToast() {
    const toast = document.getElementById('adminUsersToast');
    if (!toast) return;
    toast.style.transition = 'all 0.4s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translate(-50%, -20px)';
    setTimeout(() => {
        toast.remove();
    }, 400);
}
</script>
@endpush
