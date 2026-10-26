<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $setting = Setting::query()->first();
        $monthlyTarget = (int) ($setting->monthly_target ?? 7000000);
        $dailyRevenue = (int) Sale::query()
            ->where('status', 'terbayar')
            ->whereDate('created_at', $today->toDateString())
            ->sum('amount');

        $monthlyCustomers = (int) Sale::query()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->distinct('user_id')
            ->count('user_id');

        $from = Carbon::today()->subDays(6)->startOfDay();
        $dailyRows = Sale::query()
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('SUM(CASE WHEN status = "terbayar" THEN amount ELSE 0 END) as omzet')
            ->whereDate('created_at', '>=', $from->toDateString())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $key = $date->toDateString();
            $chartLabels[] = $key;
            $chartData[] = (int) ($dailyRows[$key]->omzet ?? 0);
        }

        return view('admin.dashboard', [
            'monthlyTarget' => $monthlyTarget,
            'dailyRevenue' => $dailyRevenue,
            'monthlyCustomers' => $monthlyCustomers,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'monthStart' => $monthStart,
        ]);
    }

    public function updateTarget(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'monthly_target' => ['required', 'integer', 'min:1'],
        ]);

        $setting = Setting::query()->first();
        if (!$setting) {
            $setting = new Setting();
        }

        $setting->monthly_target = (int) $data['monthly_target'];
        $setting->save();

        return redirect()->route('admin.dashboard')->with('success', 'Target bulanan berhasil diperbarui.');
    }

    public function downloadSalesExcel(): Response
    {
        $rows = Sale::query()
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select('sales.resi', 'users.username', 'products.nama as produk', 'sales.quantity', 'sales.amount', 'sales.status', 'sales.delivery_status', 'sales.created_at')
            ->orderByDesc('sales.created_at')
            ->limit(5000)
            ->get();

        $generatedAt = now()->format('d-m-Y H:i:s');
        $rowNumber = 1;
        $content = '<!DOCTYPE html><html><head><meta charset="UTF-8">'
            . '<style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #1f2937; }
                .sheet-title { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
                .sheet-subtitle { color: #6b7280; margin-bottom: 14px; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #cbd5e1; padding: 8px 10px; vertical-align: top; }
                th { background: #1f2937; color: #ffffff; text-align: left; }
                tbody tr:nth-child(even) td { background: #f8fafc; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .currency { mso-number-format:"\0022Rp\0022\ #,##0"; text-align: right; }
                .date { mso-number-format:"dd-mm-yyyy hh:mm"; }
            </style></head><body>'
            . '<div class="sheet-title">Laporan Sales Guna Mica</div>'
            . '<div class="sheet-subtitle">Dibuat pada: ' . e($generatedAt) . '</div>'
            . '<table>'
            . '<thead><tr>'
            . '<th style="width:48px;">No</th>'
            . '<th>Resi</th>'
            . '<th>Username</th>'
            . '<th>Produk</th>'
            . '<th style="width:70px;">Qty</th>'
            . '<th style="width:130px;">Amount</th>'
            . '<th>Status Bayar</th>'
            . '<th>Status Kirim</th>'
            . '<th style="width:150px;">Tanggal</th>'
            . '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $content .= '<tr>'
                . '<td class="text-center">' . $rowNumber++ . '</td>'
                . '<td>' . e((string) $row->resi) . '</td>'
                . '<td>' . e((string) $row->username) . '</td>'
                . '<td>' . e((string) $row->produk) . '</td>'
                . '<td class="text-center">' . e((string) $row->quantity) . '</td>'
                . '<td class="currency">' . e(number_format((float) $row->amount, 0, ',', '.')) . '</td>'
                . '<td>' . e((string) $row->status) . '</td>'
                . '<td>' . e((string) ($row->delivery_status ?: '-')) . '</td>'
                . '<td class="date">' . e(optional(Carbon::parse($row->created_at))->format('d-m-Y H:i')) . '</td>'
                . '</tr>';
        }

        if ($rows->isEmpty()) {
            $content .= '<tr><td colspan="9" class="text-center">Tidak ada data sales.</td></tr>';
        }

        $content .= '</tbody></table></body></html>';

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-sales.xls"',
        ]);
    }
}
