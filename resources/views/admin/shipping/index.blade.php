@extends('layouts.admin')

@section('title', 'Admin Pengiriman - Guna Mica')

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
        <div class="toast-container-admin" id="adminShippingToast">
            <div class="toast-card-admin {{ session('success') ? 'toast-success' : 'toast-error' }}">
                <div class="toast-body-admin">
                    <i class="bi {{ session('success') ? 'bi-check-circle-fill icon-success' : 'bi-exclamation-triangle-fill icon-error' }} me-2"></i>
                    <span>{{ session('success') ?: session('error') }}</span>
                    <button type="button" class="btn-close-admin" onclick="dismissAdminToast()" aria-label="Close">&times;</button>
                </div>
                <div class="toast-progress-bar">
                    <div class="toast-progress-fill" id="toastProgress"></div>
                </div>
            </div>
        </div>
    @endif

    <h4 class="mb-3">Kelola Pengiriman Admin</h4>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-5"><input class="form-control" name="search" value="{{ $search }}" placeholder="Cari user/produk/resi..."></div>
        <div class="col-md-4">
            <select class="form-select" name="delivery_status">
                <option value="">Semua Status</option>
                <option value="dikemas" @selected($deliveryStatus==='dikemas')>dikemas</option>
                <option value="dikirim" @selected($deliveryStatus==='dikirim')>dikirim</option>
                <option value="selesai" @selected($deliveryStatus==='selesai')>selesai</option>
                <option value="batal" @selected($deliveryStatus==='batal')>batal</option>
            </select>
        </div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary">Filter</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead>
                <tr>
                    <th>Resi</th>
                    <th>Pembeli</th>
                    <th>Alamat</th>
                    <th>Produk</th>
                    <th>Status Bayar</th>
                    <th>Status Kirim</th>
                    <th width="220">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->resi }}</td>
                        <td>{{ $order->username }}</td>
                        <td>
                            <small class="text-muted">{{ $order->alamat ?: 'Alamat tidak tersedia' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('images/' . $order->image_path) }}" alt="{{ $order->nama_produk }}" style="width:52px;height:52px;object-fit:cover;border-radius:8px;">
                                <span>{{ $order->nama_produk }}</span>
                            </div>
                        </td>
                        <td>{{ strtoupper($order->status) }}</td>
                        <td>{{ strtoupper((string) $order->delivery_status) }}</td>
                        <td>
                            <form action="{{ route('admin.shipping.status', $order->id) }}" method="post" class="mb-2">
                                @csrf
                                @method('PUT')
                                <div class="input-group input-group-sm mb-1">
                                    <span class="input-group-text">Status</span>
                                    <select class="form-select" name="delivery_status" required>
                                        <option value="dikemas" @selected($order->delivery_status==='dikemas')>dikemas</option>
                                        <option value="dikirim" @selected($order->delivery_status==='dikirim')>dikirim</option>
                                        <option value="selesai" @selected($order->delivery_status==='selesai')>selesai</option>
                                        <option value="batal" @selected($order->delivery_status==='batal')>batal</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-sm mb-1">
                                    <span class="input-group-text">No. Resi</span>
                                    <input type="text" class="form-control" name="resi" value="{{ $order->resi }}" placeholder="Input Resi Kurir...">
                                </div>
                                <button class="btn btn-sm btn-primary w-100">Update Data</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">Belum ada data pengiriman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</div>
@endsection

@push('scripts')
<script>
(() => {
    const toast = document.getElementById('adminShippingToast');
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
    const toast = document.getElementById('adminShippingToast');
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
