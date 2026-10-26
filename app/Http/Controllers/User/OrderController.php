<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Sale;
use App\Support\LegacyImage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function checkout(Request $request): View
    {
        $paymentSummary = session('paymentSummary', []);

        $selectedIds = $this->extractSelectedCartIds((string) $request->query('items'));

        $query = CartItem::query()
            ->where('user_id', Auth::id())
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->select('cart_items.*', 'products.nama', 'products.harga', 'products.gambar', 'products.stock', 'products.weight_gram');

        if (!empty($selectedIds)) {
            $query->whereIn('cart_items.id', $selectedIds);
        }

        $items = $query
            ->get()
            ->map(function ($item) {
                $item->image_path = LegacyImage::first((string) $item->gambar);
                $item->subtotal = (float) $item->harga * (int) $item->quantity;
                return $item;
            });

        if ($items->isEmpty()) {
            return view('user.orders.checkout', [
                'items' => collect(),
                'subtotal' => 0,
                'ongkir' => 0,
                'total' => 0,
                'totalWeight' => 0,
                'selectedCartIds' => [],
                'paymentSummary' => $paymentSummary,
            ]);
        }

        $subtotal = (float) $items->sum('subtotal');
        $ongkir = 0;
        $total = $subtotal + $ongkir;
        $totalWeight = (int) $items->sum(function ($item) {
            return max(1, (int) ($item->weight_gram ?? 1000)) * (int) $item->quantity;
        });

        return view('user.orders.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'total' => $total,
            'totalWeight' => $totalWeight,
            'selectedCartIds' => $items->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'paymentSummary' => $paymentSummary,
        ]);
    }

    public function place(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string'],
            'no_telp' => ['required', 'string', 'max:20'],
            'catatan' => ['nullable', 'string'],
            'shipping_courier' => ['required', 'string', 'max:30'],
            'shipping_service' => ['required', 'string', 'max:30'],
            'shipping_etd' => ['required', 'string', 'max:50'],
            'ongkir' => ['required', 'integer', 'min:0'],
            'selected_cart_ids' => ['required', 'string'],
        ]);

        $selectedIds = $this->extractSelectedCartIds($data['selected_cart_ids']);
        if (empty($selectedIds)) {
            return redirect()->route('cart.index')->with('error', 'Pilih item yang ingin dibayar terlebih dahulu.');
        }

        $items = CartItem::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $selectedIds)
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Item checkout tidak ditemukan.');
        }

        $createdSaleIds = [];
        $orderCode = $this->generateResi();
        $ongkir = max(0, (int) $data['ongkir']);

        DB::transaction(function () use ($items, $data, &$createdSaleIds, $orderCode, $ongkir, $selectedIds): void {
            $isFirst = true;
            foreach ($items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
                $qty = min((int) $item->quantity, max(1, (int) $product->stock));

                $product->stock = max(0, (int) $product->stock - $qty);
                $product->save();

                $sale = Sale::query()->create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'amount' => (float) $product->harga * $qty,
                    'payment_method' => 'midtrans',
                    'status' => 'pending',
                    'delivery_status' => 'dikemas',
                    'resi' => $orderCode,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'color' => $item->color,
                    'nama' => $data['nama'],
                    'alamat' => $data['alamat'],
                    'no_telp' => $data['no_telp'],
                    'catatan' => $data['catatan'] ?? null,
                    'ongkir' => $isFirst ? $ongkir : 0,
                    'shipping_courier' => $data['shipping_courier'],
                    'shipping_service' => $data['shipping_service'],
                    'shipping_etd' => $data['shipping_etd'],
                ]);

                $createdSaleIds[] = $sale->id;
                $isFirst = false;
            }

            CartItem::query()->where('user_id', Auth::id())->whereIn('id', $selectedIds)->delete();
        });

        $createdOrders = Sale::query()->whereIn('id', $createdSaleIds)->get();
        $grossAmount = (int) ((float) $createdOrders->sum('amount') + (float) $createdOrders->sum('ongkir'));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'order_id' => $orderCode,
                'gross_amount' => $grossAmount,
                'track_url' => route('user.orders.track', ['resi' => $orderCode]),
                'message' => 'Pesanan berhasil dibuat. Lanjutkan pembayaran Midtrans.',
            ]);
        }

        return redirect()->route('user.orders.checkout')
            ->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.')
            ->with('paymentSummary', [[
                'resi' => $orderCode,
                'status' => 'pending',
                'gross_amount' => $grossAmount,
            ]]);
    }

    public function invoice(Request $request): View
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $orders = Sale::query()
            ->where('user_id', Auth::id())
            ->when($ids->isNotEmpty(), fn ($q) => $q->whereIn('sales.id', $ids->all()))
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select('sales.*', 'products.nama as nama_produk', 'products.gambar')
            ->orderByDesc('sales.id')
            ->get()
            ->map(function ($item) {
                $item->image_path = LegacyImage::first((string) $item->gambar);
                return $item;
            });

        $paymentSummary = $orders
            ->groupBy('resi')
            ->map(function ($group, $resi) {
                $status = (string) $group->first()->status;
                $totalProduk = (float) $group->sum('amount');
                $totalOngkir = (float) $group->sum('ongkir');

                return [
                    'resi' => $resi,
                    'status' => $status,
                    'gross_amount' => (int) ($totalProduk + $totalOngkir),
                ];
            })
            ->values();

        return view('user.orders.invoice', [
            'orders' => $orders,
            'paymentSummary' => $paymentSummary,
        ]);
    }

    public function invoicePdf(Request $request): Response
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $orders = Sale::query()
            ->where('user_id', Auth::id())
            ->when($ids->isNotEmpty(), fn ($q) => $q->whereIn('sales.id', $ids->all()))
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select('sales.*', 'products.nama as nama_produk')
            ->orderByDesc('sales.id')
            ->get();

        abort_if($orders->isEmpty(), 404, 'Invoice tidak ditemukan.');

        $subtotal = (float) $orders->sum('amount');
        $ongkir = (float) $orders->sum('ongkir');
        $serviceCode = (string) ($orders->first()->shipping_service ?? 'SPS');
        $logoDataUri = null;
        $logoPath = public_path('images/logoB.png');
        if (is_file($logoPath)) {
            $logoDataUri = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('user.orders.invoice_pdf', [
            'orders' => $orders,
            'summary' => [
                'subtotal' => $subtotal,
                'ongkir' => $ongkir,
                'total' => $subtotal + $ongkir,
            ],
            'serviceLabel' => strtoupper($serviceCode),
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'portrait');

        $filename = 'invoice-' . (string) ($orders->first()->resi ?? 'order') . '.pdf';
        return $pdf->download($filename);
    }

    public function history(Request $request): View
    {
        $search = (string) $request->string('search');

        $orders = Sale::query()
            ->where('user_id', Auth::id())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('resi', 'like', '%' . $search . '%')
                      ->orWhere('status', 'like', '%' . $search . '%')
                      ->orWhere('delivery_status', 'like', '%' . $search . '%');
                });
            })
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select('sales.*', 'products.nama as nama_produk', 'products.gambar')
            ->orderByDesc('sales.created_at')
            ->paginate(12)
            ->withQueryString();

        $orders->getCollection()->transform(function ($item) {
            $item->image_path = LegacyImage::first((string) $item->gambar);
            return $item;
        });

        return view('user.orders.history', [
            'orders' => $orders,
            'search' => $search,
        ]);
    }

    public function track(Request $request): View
    {
        $resi = (string) $request->string('resi');

        $order = null;
        $orders = collect();
        $reviewedSaleIds = [];
        if ($resi !== '') {
            $orders = Sale::query()
                ->where('user_id', Auth::id())
                ->where('resi', $resi)
                ->join('products', 'products.id', '=', 'sales.product_id')
                ->select('sales.*', 'products.nama as nama_produk', 'products.weight_gram', 'products.ketebalan', 'products.ukuran')
                ->orderBy('sales.id')
                ->get();

            $order = $orders->first();
            if ($order) {
                $order->shipped_at = $orders->pluck('shipped_at')->filter()->sort()->first();
                $order->delivered_at = $orders->pluck('delivered_at')->filter()->sortDesc()->first();
            }

            $reviewedSaleIds = DB::table('product_reviews')
                ->whereIn('sale_id', $orders->pluck('id')->all())
                ->pluck('sale_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('user.orders.track', [
            'resi' => $resi,
            'order' => $order,
            'orders' => $orders,
            'reviewedSaleIds' => $reviewedSaleIds,
        ]);
    }

    public function markArrived(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'resi' => ['required', 'string'],
        ]);

        $orders = Sale::query()
            ->where('user_id', Auth::id())
            ->where('resi', $data['resi'])
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('user.orders.track', ['resi' => $data['resi']])->with('error', 'Data pesanan tidak ditemukan.');
        }

        if ((string) $orders->first()->delivery_status !== 'dikirim') {
            return redirect()->route('user.orders.track', ['resi' => $data['resi']])->with('error', 'Status pesanan belum bisa diselesaikan.');
        }

        Sale::query()
            ->where('user_id', Auth::id())
            ->where('resi', $data['resi'])
            ->update([
                'delivery_status' => 'selesai',
                'delivered_at' => Carbon::now()->toDateTimeString(),
            ]);

        return redirect()->route('user.orders.track', ['resi' => $data['resi']])->with('success', 'Pesanan ditandai selesai. Silakan beri rating dan ulasan.');
    }

    public function submitReview(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'resi' => ['required', 'string'],
            'sale_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['required', 'string'],
            'review_media' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm'],
        ]);

        $sale = Sale::query()
            ->where('id', $data['sale_id'])
            ->where('user_id', Auth::id())
            ->where('resi', $data['resi'])
            ->first();

        if (!$sale) {
            return redirect()->route('user.orders.track', ['resi' => $data['resi']])->with('error', 'Item pesanan tidak valid.');
        }

        if ((string) $sale->delivery_status !== 'selesai') {
            return redirect()->route('user.orders.track', ['resi' => $data['resi']])->with('error', 'Rating dan ulasan hanya bisa diberikan setelah pesanan selesai.');
        }

        $exists = DB::table('product_reviews')->where('sale_id', $sale->id)->exists();
        if ($exists) {
            return redirect()->route('user.orders.track', ['resi' => $data['resi']])->with('error', 'Rating untuk item ini sudah pernah dikirim.');
        }

        $reviewText = trim((string) $data['review']);
        if ($request->hasFile('review_media')) {
            $file = $request->file('review_media');
            $name = 'review_' . $sale->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/reviews'), $name);
            $reviewText .= ' [media:' . $name . ']';
        }

        DB::table('product_reviews')->insert([
            'sale_id' => $sale->id,
            'user_id' => Auth::id(),
            'product_id' => $sale->product_id,
            'rating' => (int) $data['rating'],
            'review' => $reviewText,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        return redirect()->route('user.orders.track', ['resi' => $data['resi']])->with('success', 'Terima kasih, rating dan ulasan berhasil dikirim.');
    }

    private function generateResi(): string
    {
        return 'MF' . now()->format('Ymd') . strtoupper(substr(md5(uniqid((string) random_int(1000, 9999), true)), 0, 8));
    }

    private function extractSelectedCartIds(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
