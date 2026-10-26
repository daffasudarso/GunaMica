@extends('layouts.app')

@section('title', 'Checkout - Guna Mica')

@push('styles')
<style>
    .service-list {
        display: grid;
        gap: 10px;
    }
    .service-option {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 12px;
        background: #fff;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
    }
    .service-option:hover {
        border-color: #0d6efd;
    }
    .service-option input {
        margin-top: 2px;
    }
    .service-option.active {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, .13);
        background: #f8fbff;
    }
    .checkout-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .checkout-item-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .checkout-item img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<div class="container" id="checkoutPage"
    data-verify-url="{{ route('user.payments.verify') }}"
    data-token-url="{{ route('user.payments.snap-token') }}"
    data-track-url="{{ route('user.orders.track') }}"
    data-csrf="{{ csrf_token() }}">
    <h4 class="mb-3 text-center">Checkout</h4>

    @if (!empty($paymentSummary) && count($paymentSummary) > 0)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-3">Pembayaran Midtrans</h6>
                <div class="d-flex flex-column gap-2">
                    @foreach ($paymentSummary as $pay)
                        <div class="d-flex justify-content-between align-items-center border rounded p-2">
                            <div>
                                <div class="fw-semibold">Order {{ $pay['resi'] }}</div>
                                <div class="small text-muted">Total: Rp {{ number_format((float) $pay['gross_amount'], 0, ',', '.') }} | Status: {{ strtoupper((string) $pay['status']) }}</div>
                            </div>
                            @if (strtolower((string) $pay['status']) === 'pending')
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary midtrans-pay-btn"
                                    data-order-id="{{ $pay['resi'] }}"
                                    data-gross-amount="{{ (int) $pay['gross_amount'] }}"
                                >Bayar Sekarang</button>
                            @else
                                <div class="d-flex gap-1">
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('user.orders.track', ['resi' => $pay['resi']]) }}">Lacak Pesanan</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('user.orders.invoice.pdf', ['ids' => $items->pluck('id')->implode(',')]) }}" target="_blank">Cetak Invoice</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($items->isEmpty() && empty($paymentSummary))
        <div class="alert alert-warning">Keranjang kosong, silakan pilih produk terlebih dahulu.</div>
    @elseif (!$items->isEmpty())
        <div class="row g-4">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Data Pengiriman</h6>
                        <form id="checkoutForm" action="{{ route('user.orders.place') }}" method="post"
                            data-destination-url="{{ route('user.shipping.destinations') }}"
                            data-estimate-url="{{ route('user.shipping.estimate') }}"
                            data-csrf="{{ csrf_token() }}"
                            data-total-weight="{{ (int) ($totalWeight ?? 1000) }}"
                            data-subtotal="{{ (int) $subtotal }}">
                            @csrf
                            <input type="hidden" name="selected_cart_ids" value="{{ implode(',', $selectedCartIds ?? []) }}">
                            <div class="mb-2">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" class="form-control" name="nama" value="{{ old('nama', auth()->user()->username) }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat" rows="3" required>{{ old('alamat', auth()->user()->alamat) }}</textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">No Telepon</label>
                                <input type="text" class="form-control" name="no_telp" value="{{ old('no_telp') }}" required>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Kurir</label>
                                    <select class="form-select" name="shipping_courier" id="courierSelect">
                                        <option value="jne" @selected(old('shipping_courier', 'jne') === 'jne')>JNE</option>
                                        <option value="pos" @selected(old('shipping_courier') === 'pos')>POS</option>
                                        <option value="tiki" @selected(old('shipping_courier') === 'tiki')>TIKI</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Service Dipilih</label>
                                    <input type="text" class="form-control" id="shippingServiceText" value="{{ old('shipping_service', '-') }}" readonly>
                                </div>
                            </div>
                            <input type="hidden" name="shipping_service" id="shippingService" value="{{ old('shipping_service', '') }}">
                            <div class="mb-2">
                                <label class="form-label">Tujuan Pengiriman (ketik kota/kecamatan)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="destinationSearch" placeholder="Contoh: Makassar" autocomplete="off">
                                    <button type="button" class="btn btn-outline-primary" id="searchDestinationBtn">Cari</button>
                                </div>
                                <select class="form-select mt-2" id="destinationSelect">
                                    <option value="">Pilih tujuan dari hasil pencarian</option>
                                </select>
                            </div>
                            <input type="hidden" name="shipping_etd" id="shippingEtd" value="{{ old('shipping_etd', '') }}">
                            <input type="hidden" name="ongkir" id="ongkirInput" value="{{ old('ongkir', $ongkir) }}">
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="catatan" rows="2">{{ old('catatan') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pilih Layanan Pengiriman</label>
                                <div id="serviceList" class="service-list">
                                    <div class="small text-muted">Pilih tujuan terlebih dahulu untuk menampilkan layanan.</div>
                                </div>
                            </div>
                            <div class="alert alert-light border" id="shippingInfo">
                                Ongkir saat ini: <strong id="shippingCostLabel">Rp {{ number_format($ongkir, 0, ',', '.') }}</strong>
                                <br>
                                Estimasi: <span id="shippingEtdLabel">{{ old('shipping_etd', '-') }}</span>
                            </div>
                            <button class="btn btn-success w-100" type="submit" id="payNowSubmitBtn">Bayar Sekarang</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Ringkasan Pesanan</h6>
                        @foreach ($items as $item)
                            <div class="checkout-item small">
                                <div class="checkout-item-left">
                                    <img src="{{ asset('images/' . $item->image_path) }}" alt="{{ $item->nama }}">
                                    <span>{{ $item->nama }} x{{ $item->quantity }}</span>
                                </div>
                                <span>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <hr>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Subtotal</span>
                            <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Ongkir</span>
                            <strong id="summaryShippingCost">Rp {{ number_format($ongkir, 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total</span>
                            <strong id="summaryTotal" class="text-primary">Rp {{ number_format($total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">Pesanan berhasil dibuat. Silakan selesaikan pembayaran Midtrans di atas.</div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
(() => {
    const checkoutForm = document.getElementById('checkoutForm');
    if (!checkoutForm) {
        return;
    }

    const totalWeight = Number(checkoutForm.dataset.totalWeight || 1000);
    const baseSubtotal = Number(checkoutForm.dataset.subtotal || 0);
    const placeUrl = checkoutForm.getAttribute('action') || '';
    const destinationUrl = checkoutForm.dataset.destinationUrl || '';
    const estimateUrl = checkoutForm.dataset.estimateUrl || '';
    const csrfToken = checkoutForm.dataset.csrf || '';
    const destinationSearch = document.getElementById('destinationSearch');
    const destinationSelect = document.getElementById('destinationSelect');
    const courierSelect = document.getElementById('courierSelect');
    const shippingServiceText = document.getElementById('shippingServiceText');
    const shippingService = document.getElementById('shippingService');
    const shippingEtd = document.getElementById('shippingEtd');
    const ongkirInput = document.getElementById('ongkirInput');
    const shippingCostLabel = document.getElementById('shippingCostLabel');
    const shippingEtdLabel = document.getElementById('shippingEtdLabel');
    const summaryShippingCost = document.getElementById('summaryShippingCost');
    const summaryTotal = document.getElementById('summaryTotal');
    const payNowSubmitBtn = document.getElementById('payNowSubmitBtn');
    const searchDestinationBtn = document.getElementById('searchDestinationBtn');
    const serviceList = document.getElementById('serviceList');
    let hasSelectedService = Boolean((shippingService.value || '').trim());

    if (!destinationSearch || !destinationSelect || !searchDestinationBtn || !destinationUrl || !estimateUrl || !csrfToken || !placeUrl) {
        return;
    }

    if (hasSelectedService) {
        shippingServiceText.value = shippingService.value;
    }

    const formatRupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

    function updateCost(cost, etd, serviceCode) {
        ongkirInput.value = String(cost);
        shippingEtd.value = etd || '-';
        const nextService = serviceCode || shippingService.value;
        shippingService.value = nextService;
        shippingServiceText.value = nextService || '-';
        shippingCostLabel.textContent = formatRupiah(cost);
        shippingEtdLabel.textContent = etd || '-';
        summaryShippingCost.textContent = formatRupiah(cost);
        summaryTotal.textContent = formatRupiah(baseSubtotal + Number(cost || 0));
    }

    function resetSelectedService() {
        hasSelectedService = false;
        shippingService.value = '';
        shippingServiceText.value = '-';
        shippingEtd.value = '';
        ongkirInput.value = '0';
        shippingCostLabel.textContent = formatRupiah(0);
        shippingEtdLabel.textContent = '-';
        summaryShippingCost.textContent = formatRupiah(0);
        summaryTotal.textContent = formatRupiah(baseSubtotal);
    }

    function renderServiceOptions(services) {
        serviceList.innerHTML = '';

        if (!Array.isArray(services) || services.length === 0) {
            serviceList.innerHTML = '<div class="small text-muted">Layanan tidak tersedia untuk rute ini.</div>';
            return;
        }

        services.forEach((svc, index) => {
            const cost = Number(svc.cost || 0);
            const etd = svc.etd || '-';
            const serviceCode = svc.service_code || 'SPS';
            const label = svc.service_name || svc.service_description || serviceCode;

            const wrapper = document.createElement('div');
            wrapper.className = 'service-option d-flex justify-content-between gap-3';
            wrapper.innerHTML = `
                <div class="d-flex gap-2 align-items-start" style="cursor: pointer;">
                    <input class="form-check-input mt-1" type="radio" name="shipping_service_option" value="${serviceCode}">
                    <div>
                        <div class="fw-semibold">${label} (${serviceCode})</div>
                        <div class="small text-muted">Estimasi ${etd}</div>
                    </div>
                </div>
                <div class="fw-bold text-primary">${formatRupiah(cost)}</div>
            `;

            const radio = wrapper.querySelector('input[type="radio"]');
            const activate = () => {
                document.querySelectorAll('.service-option').forEach((el) => el.classList.remove('active'));
                wrapper.classList.add('active');
                radio.checked = true;
                hasSelectedService = true;
                updateCost(cost, etd, serviceCode);
            };

            wrapper.addEventListener('click', activate);
            radio.addEventListener('click', (e) => {
                e.stopPropagation();
                activate();
            });

            serviceList.appendChild(wrapper);

        });
    }

    async function loadDestinations() {
        const query = destinationSearch.value.trim();
        if (query.length < 3) {
            alert('Masukkan minimal 3 karakter tujuan.');
            return;
        }

        let payload = { ok: false, message: 'Gagal mengambil tujuan.' };
        try {
            const url = new URL(destinationUrl, window.location.origin);
            url.searchParams.set('query', query);

            const res = await fetch(url.toString());
            payload = await res.json();
        } catch (e) {
            alert('Tidak dapat menghubungi layanan tujuan pengiriman.');
            return;
        }

        destinationSelect.innerHTML = '<option value="">Pilih tujuan dari hasil pencarian</option>';
        if (!payload.ok || !Array.isArray(payload.data)) {
            alert(payload.message || 'Tujuan tidak ditemukan atau layanan ongkir belum aktif.');
            return;
        }

        if (payload.data.length === 0) {
            alert('Tujuan tidak ditemukan, coba kata kunci lain.');
            return;
        }

        payload.data.forEach((item) => {
            const opt = document.createElement('option');
            opt.value = String(item.id || '');
            opt.textContent = [item.subdistrict_name, item.city_name, item.province_name].filter(Boolean).join(', ');
            destinationSelect.appendChild(opt);
        });
    }

    async function estimateShipping() {
        const destination = destinationSelect.value;
        if (!destination) {
            return;
        }

        let payload = { ok: false, message: 'Gagal menghitung ongkir.' };
        try {
            const res = await fetch(estimateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    destination,
                    courier: courierSelect.value,
                    weight: totalWeight,
                }),
            });

            payload = await res.json();
        } catch (e) {
            alert('Tidak dapat menghubungi layanan ongkir.');
            return;
        }

        if (!payload.ok || !Array.isArray(payload.data) || payload.data.length === 0) {
            resetSelectedService();
            serviceList.innerHTML = '<div class="small text-danger">Ongkir tidak tersedia untuk kombinasi tujuan/kurir ini.</div>';
            alert(payload.message || 'Ongkir tidak tersedia untuk kombinasi tujuan/kurir ini.');
            return;
        }

        resetSelectedService();
        renderServiceOptions(payload.data);
    }

    searchDestinationBtn.addEventListener('click', loadDestinations);
    destinationSelect.addEventListener('change', () => {
        resetSelectedService();
        estimateShipping();
    });
    courierSelect.addEventListener('change', () => {
        resetSelectedService();
        estimateShipping();
    });

    checkoutForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!destinationSelect.value) {
            alert('Silakan pilih tujuan pengiriman terlebih dahulu.');
            return;
        }

        if (!hasSelectedService || !shippingService.value) {
            alert('Silakan pilih salah satu layanan pengiriman terlebih dahulu.');
            return;
        }

        if (typeof window.startMidtransPayment !== 'function') {
            alert('Modul pembayaran belum siap. Muat ulang halaman lalu coba lagi.');
            return;
        }

        if (payNowSubmitBtn) {
            payNowSubmitBtn.disabled = true;
        }

        try {
            const formData = new FormData(checkoutForm);
            const placeResp = await fetch(placeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const placeData = await placeResp.json();
            if (!placeResp.ok || !placeData.ok || !placeData.order_id || !placeData.gross_amount) {
                alert(placeData.message || 'Gagal membuat pesanan.');
                return;
            }

            await window.startMidtransPayment(placeData.order_id, Number(placeData.gross_amount || 0), payNowSubmitBtn, placeData.track_url || '');
        } catch (e) {
            alert('Terjadi kesalahan saat membuat pesanan.');
        } finally {
            if (payNowSubmitBtn) {
                payNowSubmitBtn.disabled = false;
            }
        }
    });
})();
</script>

<script>
(() => {
    const container = document.getElementById('checkoutPage');
    if (!container) {
        return;
    }

    const verifyUrl = container.dataset.verifyUrl || '';
    const tokenUrl = container.dataset.tokenUrl || '';
    const trackUrl = container.dataset.trackUrl || '';
    const csrfToken = container.dataset.csrf || '';

    const buttons = document.querySelectorAll('.midtrans-pay-btn');
    if (!verifyUrl || !tokenUrl || !csrfToken) {
        return;
    }

    async function verifyPayment(orderId, redirectUrl = '') {
        await fetch(verifyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ order_id: orderId }),
        });

        if (redirectUrl) {
            window.location.href = redirectUrl;
            return;
        }

        if (!trackUrl) {
            window.location.reload();
            return;
        }

        window.location.href = `${trackUrl}?resi=${encodeURIComponent(orderId)}`;
    }

    window.startMidtransPayment = async (orderId, grossAmount, triggerBtn = null, redirectUrl = '') => {
        if (!orderId || Number(grossAmount) < 1) {
            alert('Data pembayaran tidak valid.');
            return;
        }

        if (triggerBtn) {
            triggerBtn.disabled = true;
        }

        try {
            const tokenResp = await fetch(tokenUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    order_id: orderId,
                    gross_amount: Number(grossAmount),
                }),
            });

            const tokenData = await tokenResp.json();
            if (!tokenData.ok || !tokenData.token) {
                alert(tokenData.message || 'Gagal membuat token Midtrans.');
                return;
            }

            window.snap.pay(tokenData.token, {
                onSuccess: () => verifyPayment(orderId, redirectUrl),
                onPending: () => verifyPayment(orderId, redirectUrl),
                onError: () => verifyPayment(orderId, redirectUrl),
                onClose: () => {
                    if (triggerBtn) {
                        triggerBtn.disabled = false;
                    }
                },
            });
        } catch (e) {
            alert('Terjadi kesalahan saat memproses pembayaran.');
            if (triggerBtn) {
                triggerBtn.disabled = false;
            }
        }
    };

    buttons.forEach((btn) => {
        btn.addEventListener('click', async () => {
            await window.startMidtransPayment(btn.dataset.orderId, btn.dataset.grossAmount, btn);
        });
    });
})();
</script>
@endpush
