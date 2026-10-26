@extends('layouts.app')

@section('title', 'Lacak Pesanan - Guna Mica')

@push('styles')
<style>
    /* Hide layout level alerts on this page */
    main > .container > .alert {
        display: none !important;
    }

    /* Floating Toast Container */
    .toast-container-track {
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

    .toast-card-track {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.8);
        overflow: hidden;
        position: relative;
    }

    .toast-card-track.toast-success {
        border-left: 4px solid #10b981; /* Emerald-500 */
    }

    .toast-card-track.toast-error {
        border-left: 4px solid #ef4444; /* Red-500 */
    }

    .toast-card-track.toast-warning {
        border-left: 4px solid #f59e0b; /* Amber-500 */
    }

    .toast-body-track {
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

    .btn-close-track {
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

    .btn-close-track:hover {
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
        transition: width 3s linear; /* 3 seconds! */
    }

    .toast-card-track.toast-error .toast-progress-fill {
        background: #ef4444;
    }

    .toast-card-track.toast-warning .toast-progress-fill {
        background: #f59e0b;
    }
</style>
@endpush

@section('content')
<div class="container">
    @php
        $toastType = null;
        $toastMessage = null;

        if (session('success')) {
            $toastType = 'success';
            $toastMessage = session('success');
        } elseif (session('error')) {
            $toastType = 'error';
            $toastMessage = session('error');
        } elseif (session('notice')) {
            $toastType = 'warning';
            $toastMessage = session('notice');
        } elseif ($resi !== '' && !$order) {
            $toastType = 'warning';
            $toastMessage = 'Resi tidak ditemukan pada akun Anda.';
        }
    @endphp

    @if ($toastType && $toastMessage)
        <div class="toast-container-track" id="trackOrderToast">
            <div class="toast-card-track toast-{{ $toastType }}">
                <div class="toast-body-track">
                    @if ($toastType === 'success')
                        <i class="bi bi-check-circle-fill icon-success me-2"></i>
                    @elseif ($toastType === 'error')
                        <i class="bi bi-exclamation-triangle-fill icon-error me-2"></i>
                    @else
                        <i class="bi bi-exclamation-circle-fill icon-warning me-2"></i>
                    @endif
                    <span>{{ $toastMessage }}</span>
                    <button type="button" class="btn-close-track" onclick="dismissTrackToast()" aria-label="Close">&times;</button>
                </div>
                <div class="toast-progress-bar">
                    <div class="toast-progress-fill" id="toastProgress"></div>
                </div>
            </div>
        </div>
    @endif

    <h4 class="mb-3 text-center">Lacak Pesanan</h4>

    <form method="get" class="row g-2 mb-4">
        <div class="col-md-9">
            <input type="text" class="form-control" name="resi" value="{{ $resi }}" placeholder="Masukkan nomor resi...">
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Lacak</button>
        </div>
    </form>

    @if ($order)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Detail Pengiriman</h6>
                <div class="row g-2">
                    <div class="col-md-6"><strong>Resi:</strong> {{ $order->resi }}</div>
                    <div class="col-md-6"><strong>Total Item:</strong> {{ $orders->count() }}</div>
                    <div class="col-md-6"><strong>Status Bayar:</strong> {{ strtoupper($order->status) }}</div>
                    <div class="col-md-6"><strong>Status Kirim:</strong> {{ strtoupper((string) $order->delivery_status) }}</div>
                    <div class="col-md-6"><strong>Tanggal Kirim:</strong> <span class="local-time" data-utc="{{ $order->shipped_at }}">{{ $order->shipped_at ?: '-' }}</span></div>
                    <div class="col-md-6"><strong>Tanggal Diterima:</strong> <span class="local-time" data-utc="{{ $order->delivered_at }}">{{ $order->delivered_at ?: '-' }}</span></div>
                    <div class="col-md-12"><strong>Estimasi:</strong> {{ $order->shipping_etd ?: '-' }}</div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if (in_array(strtolower((string) $order->delivery_status), ['dikirim', 'selesai'], true))
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('user.orders.invoice.pdf', ['ids' => $orders->pluck('id')->implode(',')]) }}" target="_blank">Cetak Invoice</a>
                    @endif

                    @if ((string) $order->delivery_status === 'dikirim')
                        <form action="{{ route('user.orders.track.complete') }}" method="post" class="m-0">
                            @csrf
                            <input type="hidden" name="resi" value="{{ $order->resi }}">
                            <button type="submit" class="btn btn-sm btn-success" onclick="return gmConfirmDialog(this, event, 'Konfirmasi pesanan telah diterima dengan aman?')">Selesai</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="mb-3">Item Pesanan</h6>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Ketebalan</th>
                                <th>Ukuran</th>
                                <th>Jumlah</th>
                                <th>Warna</th>
                                <th>Berat</th>
                                <th>Aksi Ulasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $item)
                                <tr>
                                    <td>{{ $item->nama_produk }}</td>
                                    <td>{{ $item->ketebalan ?: '-' }}</td>
                                    <td>{{ $item->ukuran ?: '-' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->color ?: '-' }}</td>
                                    <td>{{ (int) ($item->weight_gram ?? 0) }} gram</td>
                                    <td>
                                        @if (in_array((int) $item->id, $reviewedSaleIds, true))
                                            <span class="badge text-bg-success">Sudah dinilai</span>
                                        @elseif ((string) $item->delivery_status === 'selesai')
                                            <form action="{{ route('user.orders.track.review') }}" method="post" enctype="multipart/form-data" class="row g-2">
                                                @csrf
                                                <input type="hidden" name="resi" value="{{ $item->resi }}">
                                                <input type="hidden" name="sale_id" value="{{ $item->id }}">
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm" name="rating" required>
                                                        <option value="">Rating</option>
                                                        <option value="5">5</option>
                                                        <option value="4">4</option>
                                                        <option value="3">3</option>
                                                        <option value="2">2</option>
                                                        <option value="1">1</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control form-control-sm" name="review" placeholder="Ulasan wajib diisi" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="file" class="form-control form-control-sm" name="review_media" accept=".jpg,.jpeg,.png,.webp,.mp4,.mov,.avi,.webm,image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/webm">
                                                </div>
                                                <div class="col-md-1 d-grid">
                                                    <button class="btn btn-sm btn-primary" type="submit">Kirim</button>
                                                </div>
                                            </form>
                                        @else
                                            <span class="badge text-bg-secondary">Menunggu selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    const toast = document.getElementById('trackOrderToast');
    const fill = document.getElementById('toastProgress');
    if (!toast) return;

    // Start progress transition (3s)
    setTimeout(() => {
        if (fill) {
            fill.style.width = '0%';
        }
    }, 50);

    // Auto dismiss after 3s
    setTimeout(() => {
        dismissTrackToast();
    }, 3000);
})();

function dismissTrackToast() {
    const toast = document.getElementById('trackOrderToast');
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
