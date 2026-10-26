<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Support\LegacyImage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');
        $status = (string) $request->string('delivery_status');

        $orders = Sale::query()
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select('sales.*', 'users.username', 'products.nama as nama_produk', 'products.gambar')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('users.username', 'like', '%' . $search . '%')
                        ->orWhere('products.nama', 'like', '%' . $search . '%')
                        ->orWhere('sales.resi', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn ($q) => $q->where('sales.delivery_status', $status))
            ->orderByDesc('sales.created_at')
            ->paginate(15)
            ->withQueryString();

        $orders->getCollection()->transform(function ($item) {
            $item->image_path = LegacyImage::first((string) $item->gambar);
            return $item;
        });

        return view('admin.shipping.index', [
            'orders' => $orders,
            'search' => $search,
            'deliveryStatus' => $status,
        ]);
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'delivery_status' => ['required', 'in:dikemas,dikirim,selesai,batal'],
            'resi' => ['nullable', 'string', 'max:255'],
        ]);

        $order = Sale::query()->findOrFail($id);
        $status = $data['delivery_status'];
        $now = Carbon::now()->toDateTimeString();

        $payload = [
            'delivery_status' => $status,
            'resi' => $data['resi'] ?? $order->resi,
        ];

        if ($status === 'dikirim') {
            $payload['shipped_at'] = $now;
            $payload['delivered_at'] = null;
        } elseif ($status === 'selesai') {
            $payload['shipped_at'] = $order->shipped_at ?: $now;
            $payload['delivered_at'] = $now;
        } else {
            $payload['shipped_at'] = null;
            $payload['delivered_at'] = null;
        }

        Sale::query()
            ->where('user_id', $order->user_id)
            ->where('resi', $order->resi)
            ->update($payload);

        return redirect()->route('admin.shipping.index')->with('success', 'Status pengiriman diperbarui.');
    }
}
