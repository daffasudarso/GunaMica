@extends('layouts.app')

@section('title', 'Invoice - Guna Mica')

@section('content')
<div class="container" id="invoiceContainer"
    data-verify-url="{{ route('user.payments.verify') }}"
    data-token-url="{{ route('user.payments.snap-token') }}"
    data-track-url="{{ route('user.orders.track') }}"
    data-csrf="{{ csrf_token() }}">
    <h4 class="mb-3">Invoice Pesanan</h4>

    @if ($orders->isEmpty())
        <div class="alert alert-warning">Data invoice tidak ditemukan.</div>
    @else
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
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered align-middle bg-white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Resi</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Status Bayar</th>
                        <th>Status Kirim</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->resi }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ asset('images/' . $order->image_path) }}" alt="{{ $order->nama_produk }}" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                    <span>{{ $order->nama_produk }}</span>
                                </div>
                            </td>
                            <td>{{ $order->quantity }}</td>
                            <td>Rp {{ number_format((float) $order->amount, 0, ',', '.') }}</td>
                            <td>{{ strtoupper($order->status) }}</td>
                            <td>{{ strtoupper((string) $order->delivery_status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2">
            @if ($orders->isNotEmpty() && strtolower($orders->first()->status) === 'terbayar')
                <a href="{{ route('user.orders.invoice.pdf', ['ids' => $orders->pluck('id')->implode(',')]) }}" class="btn btn-outline-success" target="_blank">Cetak Invoice</a>
            @endif
            <a href="{{ route('user.orders.history') }}" class="btn btn-outline-primary">Lihat Semua Pesanan</a>
            <a href="{{ route('user.products.index') }}" class="btn btn-primary">Belanja Lagi</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
(() => {
    const container = document.getElementById('invoiceContainer');
    if (!container) {
        return;
    }

    const verifyUrl = container.dataset.verifyUrl || '';
    const tokenUrl = container.dataset.tokenUrl || '';
    const trackUrl = container.dataset.trackUrl || '';
    const csrfToken = container.dataset.csrf || '';

    const buttons = document.querySelectorAll('.midtrans-pay-btn');
    if (!buttons.length || !verifyUrl || !tokenUrl || !csrfToken) {
        return;
    }

    async function verifyPayment(orderId) {
        await fetch(verifyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ order_id: orderId }),
        });
        if (!trackUrl) {
            window.location.reload();
            return;
        }

        window.location.href = `${trackUrl}?resi=${encodeURIComponent(orderId)}`;
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const orderId = btn.dataset.orderId;
            const grossAmount = Number(btn.dataset.grossAmount || 0);

            if (!orderId || grossAmount < 1) {
                return;
            }

            btn.disabled = true;
            try {
                const tokenResp = await fetch(tokenUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        gross_amount: grossAmount,
                    }),
                });

                const tokenData = await tokenResp.json();
                if (!tokenData.ok || !tokenData.token) {
                    alert(tokenData.message || 'Gagal membuat token Midtrans.');
                    return;
                }

                window.snap.pay(tokenData.token, {
                    onSuccess: () => verifyPayment(orderId),
                    onPending: () => verifyPayment(orderId),
                    onError: () => verifyPayment(orderId),
                    onClose: () => {
                        btn.disabled = false;
                    },
                });
            } catch (e) {
                alert('Terjadi kesalahan saat memproses pembayaran.');
            } finally {
                btn.disabled = false;
            }
        });
    });
})();
</script>
@endpush
