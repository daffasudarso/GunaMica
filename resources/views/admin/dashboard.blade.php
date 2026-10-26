@extends('layouts.admin')

@section('title', 'Dashboard Admin - Guna Mica')

@push('styles')
<style>
    .stat-card {
        border-radius: 10px;
        color: #fff;
        padding: 16px;
        min-height: 130px;
    }
    .stat-dark { background: #232833; }
    .stat-blue { background: #1e67f3; }
    .stat-green { background: #16874f; }
    .stat-card .label { opacity: .9; font-size: .92rem; }
    .stat-card .value { font-size: 2rem; font-weight: 700; }
    .chart-wrap {
        position: relative;
        height: 280px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">Dashboard Admin</h4>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card stat-dark h-100">
                <div class="label mb-1"><i class="bi bi-bullseye"></i> Target Bulanan</div>
                <div class="value">Rp {{ number_format((int) $monthlyTarget, 0, ',', '.') }}</div>
                <form action="{{ route('admin.dashboard.target') }}" method="post" class="mt-2 d-flex gap-2">
                    @csrf
                    <input class="form-control form-control-sm" type="number" name="monthly_target" min="1" placeholder="Contoh: 7000000" required>
                    <button class="btn btn-warning btn-sm" type="submit">Simpan</button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-blue h-100">
                <div class="label mb-1"><i class="bi bi-cash-coin"></i> Omset Hari Ini</div>
                <div class="value">Rp {{ number_format((int) $dailyRevenue, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-green h-100">
                <div class="label mb-1"><i class="bi bi-person"></i> Customer Bulan Ini</div>
                <div class="value">{{ (int) $monthlyCustomers }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-graph-up-arrow"></i> Grafik Penjualan (7 Hari Terakhir)</h6>
            <div class="chart-wrap">
                <canvas id="salesChart" data-labels='@json($chartLabels)' data-values='@json($chartData)'></canvas>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <a class="btn btn-success btn-sm" href="{{ route('admin.dashboard.export-sales') }}">
                    <i class="bi bi-file-earmark-excel"></i> Download Laporan Sales (Excel)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const data = JSON.parse(canvas.dataset.values || '[]');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Omset Harian',
                data,
                borderColor: '#1e67f3',
                backgroundColor: 'rgba(30, 103, 243, 0.15)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
})();
</script>
@endpush
