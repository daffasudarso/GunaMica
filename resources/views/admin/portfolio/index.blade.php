@extends('layouts.admin')

@section('title', 'Admin Portfolio - Guna Mica')

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
        <div class="toast-container-admin" id="adminPortfolioToast">
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

    <h4 class="mb-3">Kelola Portfolio Admin</h4>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-9"><input class="form-control" name="search" value="{{ $search }}" placeholder="Cari judul portfolio..."></div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary">Cari</button></div>
    </form>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6>Tambah Portfolio</h6>
            <form action="{{ route('admin.portfolio.store') }}" method="post" enctype="multipart/form-data" class="row g-2">
                @csrf
                <div class="col-md-4"><input class="form-control" name="judul" placeholder="Judul" required></div>
                <div class="col-md-3"><input class="form-control" name="kategori" placeholder="Kategori"></div>
                <div class="col-md-5"><input class="form-control" type="file" name="gambar_files[]" accept="image/*,video/*" multiple></div>
                <div class="col-md-12"><textarea class="form-control" name="deskripsi" rows="2" placeholder="Deskripsi"></textarea></div>
                <div class="col-md-12 d-grid"><button class="btn btn-success">Tambah</button></div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Deskripsi</th>
                    <th width="250">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @php
                        $isVideo = in_array(strtolower((string) pathinfo((string) $item->image_path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi', 'webm'], true);
                    @endphp
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>
                            @if ($isVideo)
                                <video controls preload="metadata" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                                    <source src="{{ asset('images/' . $item->image_path) }}">
                                </video>
                            @else
                                <img src="{{ asset('images/' . $item->image_path) }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                            @endif
                        </td>
                        <td>{{ $item->judul }}</td>
                        <td>{{ $item->kategori }}</td>
                        <td>{{ $item->deskripsi }}</td>
                        <td>
                            <details>
                                <summary class="btn btn-sm btn-outline-primary py-0 px-2">Edit</summary>
                                <form action="{{ route('admin.portfolio.update', $item->id) }}" method="post" enctype="multipart/form-data" class="mt-2 d-grid gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input class="form-control form-control-sm" name="judul" value="{{ $item->judul }}" required>
                                    <input class="form-control form-control-sm" name="kategori" value="{{ $item->kategori }}">
                                    <textarea class="form-control form-control-sm" rows="2" name="deskripsi">{{ $item->deskripsi }}</textarea>

                                    @if (!empty($item->media_items))
                                        <div>
                                            <div class="small fw-semibold mb-1">Hapus Media Terpilih</div>
                                            <div class="d-flex flex-wrap gap-2 small">
                                                @foreach ($item->media_items as $m)
                                                    <label>
                                                        <input class="form-check-input me-1" type="checkbox" name="remove_media[]" value="{{ $m }}">
                                                        {{ \Illuminate\Support\Str::limit($m, 18) }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <input class="form-control form-control-sm" type="file" name="gambar_files[]" accept="image/*,video/*" multiple>
                                    <button class="btn btn-sm btn-primary py-0 px-2">Simpan</button>
                                </form>
                            </details>
                            <form class="mt-2" action="{{ route('admin.portfolio.destroy', $item->id) }}" method="post" onsubmit="return gmConfirmDialog(this, event, 'Hapus portfolio ini?')">
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

    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
(() => {
    const toast = document.getElementById('adminPortfolioToast');
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
    const toast = document.getElementById('adminPortfolioToast');
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
