@extends('layouts.admin')

@section('title', 'Kelola Produk Admin - Guna Mica')

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
<div class="container-fluid">
    @if (session('success') || session('error'))
        <div class="toast-container-admin" id="adminProductsToast">
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

    <h4 class="mb-3">Kelola Produk Admin</h4>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6>Tambah Produk</h6>
            <form action="{{ route('admin.products.store') }}" method="post" enctype="multipart/form-data" class="row g-2">
                @csrf
                <div class="col-md-4"><input class="form-control" name="nama" placeholder="Nama" required></div>
                <div class="col-md-2"><input class="form-control" name="kategori" placeholder="Kategori" required></div>
                <div class="col-md-2"><input class="form-control" name="harga" type="number" min="0" placeholder="Harga" required></div>
                <div class="col-md-2"><input class="form-control" name="stock" type="number" min="0" placeholder="Stok" required></div>
                <div class="col-md-2"><input class="form-control" name="weight_gram" type="number" min="1" placeholder="Berat (gram)"></div>
                <div class="col-md-12"><textarea class="form-control" name="deskripsi" rows="2" placeholder="Deskripsi"></textarea></div>
                <div class="col-md-3"><input class="form-control" name="ketebalan" placeholder="Ketebalan"></div>
                <div class="col-md-3"><input class="form-control" name="ukuran" placeholder="Ukuran"></div>
                <div class="col-md-12">
                    <label class="form-label small mb-1">Pilihan Warna</label>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach (['Putih', 'Bening', 'Hitam', 'Kuning', 'Biru'] as $w)
                            <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="warna_options[]" value="{{ $w }}">{{ $w }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="form-label small mb-1">Upload Foto/Video</label>
                    <input class="form-control" name="media_files[]" type="file" accept="image/*,video/*" multiple>
                </div>
                <div class="col-md-12 d-grid"><button class="btn btn-success btn-sm">Tambah Produk</button></div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Media</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Ketebalan</th>
                    <th>Ukuran</th>
                    <th>Berat</th>
                    <th width="290">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    @php
                        $media = [];
                        if (!empty($product->gambar) && str_starts_with(trim((string) $product->gambar), '[')) {
                            $decoded = json_decode((string) $product->gambar, true);
                            if (is_array($decoded)) {
                                $media = array_values(array_filter($decoded, fn ($m) => is_string($m) && trim($m) !== ''));
                            }
                        }
                        if (empty($media)) {
                            $media = [$product->image_path];
                        }
                        $firstMedia = (string) $product->image_path;
                        $firstIsVideo = in_array(strtolower((string) pathinfo($firstMedia, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi', 'webm'], true);
                    @endphp
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            @if ($firstIsVideo)
                                <video controls preload="metadata" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                                    <source src="{{ asset('images/' . $product->image_path) }}">
                                </video>
                            @else
                                <img src="{{ asset('images/' . $product->image_path) }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                            @endif
                        </td>
                        <td>{{ $product->nama }}</td>
                        <td>{{ $product->kategori }}</td>
                        <td>Rp {{ number_format((float) $product->harga, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->ketebalan ?: '-' }}</td>
                        <td>{{ $product->ukuran ?: '-' }}</td>
                        <td>{{ (int) ($product->weight_gram ?? 0) }} gr</td>
                        <td>
                            <details>
                                <summary class="btn btn-sm btn-outline-primary">Edit</summary>
                                <form action="{{ route('admin.products.update', $product->id) }}" method="post" enctype="multipart/form-data" class="mt-2 d-grid gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input class="form-control form-control-sm" name="nama" value="{{ $product->nama }}" required>
                                    <input class="form-control form-control-sm" name="kategori" value="{{ $product->kategori }}" required>
                                    <input class="form-control form-control-sm" type="number" name="harga" value="{{ (float) $product->harga }}" required>
                                    <input class="form-control form-control-sm" type="number" name="stock" value="{{ $product->stock }}" required>
                                    <textarea class="form-control form-control-sm" name="deskripsi" rows="2">{{ $product->deskripsi }}</textarea>
                                    <div class="d-flex flex-wrap gap-2 small">
                                        @foreach (['Putih', 'Bening', 'Hitam', 'Kuning', 'Biru'] as $w)
                                            <label><input class="form-check-input me-1" type="checkbox" name="warna_options[]" value="{{ $w }}" @checked(str_contains((string) $product->warna, $w))>{{ $w }}</label>
                                        @endforeach
                                    </div>
                                    <input class="form-control form-control-sm" name="ketebalan" value="{{ $product->ketebalan }}" placeholder="Ketebalan">
                                    <input class="form-control form-control-sm" name="ukuran" value="{{ $product->ukuran }}" placeholder="Ukuran">
                                    <input class="form-control form-control-sm" type="number" min="1" name="weight_gram" value="{{ $product->weight_gram }}" placeholder="Berat">

                                    <div>
                                        <div class="small fw-semibold mb-1">Hapus Media Terpilih</div>
                                        <div class="d-flex flex-wrap gap-2 small">
                                            @foreach ($media as $m)
                                                <label>
                                                    <input class="form-check-input me-1" type="checkbox" name="remove_media[]" value="{{ $m }}">
                                                    {{ \Illuminate\Support\Str::limit($m, 18) }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <input class="form-control form-control-sm" type="file" name="media_files[]" accept="image/*,video/*" multiple>
                                    <button class="btn btn-sm btn-primary">Simpan</button>
                                </form>
                            </details>
                            <form class="mt-2" action="{{ route('admin.products.destroy', $product->id) }}" method="post" onsubmit="return gmConfirmDialog(this, event, 'Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>
@endsection

@push('scripts')
<script>
(() => {
    const toast = document.getElementById('adminProductsToast');
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
    const toast = document.getElementById('adminProductsToast');
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
