@extends('layouts.app')

@section('title', 'Keranjang - Guna Mica')

@push('styles')
<style>
    .cart-item {
        background: #fff;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 14px;
    }
    .cart-image {
        width: 110px;
        height: 110px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #ececec;
    }
    .cart-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .cart-summary {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        position: sticky;
        top: 92px;
    }
    .qty-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .qty-wrap input {
        max-width: 86px;
    }
    .item-check {
        transform: scale(1.2);
    }
    
    /* Hide layout level alerts on this page */
    main > .container > .alert {
        display: none !important;
    }

    /* Floating Toast Container */
    .toast-container-cart {
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

    .toast-card-cart {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.8);
        overflow: hidden;
        position: relative;
    }

    .toast-card-cart.toast-success {
        border-left: 4px solid #10b981; /* Emerald-500 */
    }

    .toast-card-cart.toast-error {
        border-left: 4px solid #ef4444; /* Red-500 */
    }

    .toast-card-cart.toast-warning {
        border-left: 4px solid #f59e0b; /* Amber-500 */
    }

    .toast-body-cart {
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

    .btn-close-cart {
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

    .btn-close-cart:hover {
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

    .toast-card-cart.toast-error .toast-progress-fill {
        background: #ef4444;
    }

    .toast-card-cart.toast-warning .toast-progress-fill {
        background: #f59e0b;
    }

    /* Empty Cart State Styling */
    .empty-cart-container {
        padding: 56px 16px;
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.015);
    }
    .empty-cart-icon-wrap {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #f8fafc;
        border: 1.5px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: #94a3b8;
        font-size: 2.2rem;
    }
    .empty-cart-title {
        font-weight: 800;
        color: #0f172a;
        font-size: 1.35rem;
    }
    .empty-cart-subtitle {
        font-size: 0.92rem;
        line-height: 1.6;
        color: #64748b;
    }
    .btn-primary-custom {
        background: #ffd700;
        color: #0f172a;
        font-weight: 750;
        border: none;
        border-radius: 99px;
        padding: 12px 30px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 14px rgba(255, 215, 0, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none !important;
    }
    .btn-primary-custom:hover {
        background: #e6c300;
        transform: translateY(-1px);
        color: #0f172a;
    }
</style>
@endpush

@section('content')
    @if (session('success') || session('error') || session('notice'))
        @php
            $toastType = session('success') ? 'success' : (session('error') ? 'error' : 'warning');
            $toastMessage = session('success') ?: (session('error') ?: session('notice'));
        @endphp
        <div class="toast-container-cart" id="cartToast">
            <div class="toast-card-cart toast-{{ $toastType }}">
                <div class="toast-body-cart">
                    @if ($toastType === 'success')
                        <i class="bi bi-check-circle-fill icon-success me-2"></i>
                    @elseif ($toastType === 'error')
                        <i class="bi bi-exclamation-triangle-fill icon-error me-2"></i>
                    @else
                        <i class="bi bi-exclamation-circle-fill icon-warning me-2"></i>
                    @endif
                    <span>{{ $toastMessage }}</span>
                    <button type="button" class="btn-close-cart" onclick="dismissCartToast()" aria-label="Close">&times;</button>
                </div>
                <div class="toast-progress-bar">
                    <div class="toast-progress-fill" id="toastProgress"></div>
                </div>
            </div>
        </div>
    @endif

<div class="container">
    <h4 class="mb-3 text-center">Keranjang Belanja</h4>

    @if ($items->isEmpty())
        <div class="empty-cart-container text-center py-5">
            <div class="empty-cart-icon-wrap mb-4">
                <i class="bi bi-bag-x"></i>
            </div>
            <h3 class="empty-cart-title mb-2">Keranjang Belanja Anda Kosong</h3>
            <p class="empty-cart-subtitle text-muted mx-auto mb-4" style="max-width: 480px;">Belum ada produk akrilik custom pilihan Anda di sini. Jelajahi katalog produk kami sekarang dan temukan kreasi akrilik terbaik untuk kebutuhan Anda.</p>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach ($items as $item)
                    <div class="cart-item">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-1 col-2 text-center">
                                <input class="form-check-input item-check" type="checkbox" checked data-item-id="{{ $item->id }}" data-item-qty="{{ $item->quantity }}" data-item-price="{{ (float) $item->harga }}">
                            </div>
                            <div class="col-md-2 col-4">
                                <img src="{{ asset('images/' . $item->image_path) }}" alt="{{ $item->nama }}" class="cart-image">
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="cart-title">{{ $item->nama }}</div>
                                <div class="small text-muted mb-1">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</div>
                                <div class="small text-muted">Stok tersedia: {{ $item->stock }}</div>
                            </div>
                            <div class="col-md-5 col-12">
                                <form id="update-cart-{{ $item->id }}" action="{{ route('cart.update', $item->id) }}" method="post" class="row g-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Jumlah</label>
                                        <input type="number" class="form-control form-control-sm quantity-input" min="1" max="{{ $item->stock }}" name="quantity" value="{{ $item->quantity }}" data-item-id="{{ $item->id }}" data-price="{{ (float) $item->harga }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1">Warna</label>
                                        <input type="text" class="form-control form-control-sm color-input" name="color" value="{{ $item->color }}" placeholder="Contoh: Bening" data-item-id="{{ $item->id }}">
                                    </div>
                                </form>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="fw-bold text-primary item-subtotal" data-item-id="{{ $item->id }}">Subtotal: Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</div>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('cart.destroy', $item->id) }}" method="post" onsubmit="return gmConfirmDialog(this, event, 'Hapus item ini?')" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="text-center mt-2">
                                    <a class="btn btn-outline-secondary btn-sm px-3" href="{{ auth()->check() ? route('user.products.index') : route('products.guest') }}">Tambah Produk</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4">
                <div class="cart-summary">
                    <h5 class="mb-3">Ringkasan Belanja</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Item</span>
                        <strong id="selectedItemCount">0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Grand Total</span>
                        <strong class="text-primary" id="selectedGrandTotal">Rp 0</strong>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('checkout.guard') }}" class="btn btn-success" id="paySelectedBtn">Lanjut Bayar</a>
                    </div>
                    @guest
                        <div class="small text-muted mt-2">Anda bisa menambahkan produk ke keranjang, tetapi checkout membutuhkan login.</div>
                    @endguest
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    const checks = Array.from(document.querySelectorAll('.item-check'));
    const itemCountEl = document.getElementById('selectedItemCount');
    const totalEl = document.getElementById('selectedGrandTotal');
    const payBtn = document.getElementById('paySelectedBtn');
    const baseCheckoutUrl = payBtn ? payBtn.getAttribute('href') : '';

    if (!checks.length || !itemCountEl || !totalEl || !payBtn) {
        return;
    }

    const formatRupiah = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;

    const getSelected = () => checks.filter((check) => check.checked);

    const refreshSummary = () => {
        let totalQty = 0;
        let totalPrice = 0;

        const selected = getSelected();
        selected.forEach((check) => {
            const itemId = check.dataset.itemId;
            const qtyInput = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
            const qty = Number(qtyInput ? qtyInput.value : check.dataset.itemQty || 0);
            const price = Number(check.dataset.itemPrice || 0);
            totalQty += qty;
            totalPrice += (qty * price);
        });

        itemCountEl.textContent = String(totalQty);
        totalEl.textContent = formatRupiah(totalPrice);

        const selectedIds = selected.map((check) => check.dataset.itemId).join(',');
        payBtn.setAttribute('href', selectedIds ? `${baseCheckoutUrl}?items=${encodeURIComponent(selectedIds)}` : baseCheckoutUrl);
        payBtn.classList.toggle('disabled', selectedIds.length === 0);
    };

    const submitUpdateForm = (itemId) => {
        const form = document.getElementById(`update-cart-${itemId}`);
        if (form) {
            form.submit();
        }
    };

    checks.forEach((check) => {
        check.addEventListener('change', refreshSummary);
    });

    document.querySelectorAll('.quantity-input').forEach((input) => {
        input.addEventListener('change', () => {
            const itemId = input.dataset.itemId;
            const qty = Number(input.value || 1);
            const price = Number(input.dataset.price || 0);
            const subtotalEl = document.querySelector(`.item-subtotal[data-item-id="${itemId}"]`);
            if (subtotalEl) {
                subtotalEl.textContent = `Subtotal: ${formatRupiah(qty * price)}`;
            }
            submitUpdateForm(itemId);
        });
    });

    document.querySelectorAll('.color-input').forEach((input) => {
        input.addEventListener('change', () => {
            submitUpdateForm(input.dataset.itemId);
        });
    });

    payBtn.addEventListener('click', (event) => {
        if (getSelected().length === 0) {
            event.preventDefault();
            alert('Pilih minimal satu barang untuk dibayar.');
        }
    });

    refreshSummary();
})();

(() => {
    const toast = document.getElementById('cartToast');
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
        dismissCartToast();
    }, 5000);
})();

function dismissCartToast() {
    const toast = document.getElementById('cartToast');
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
