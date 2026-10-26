<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Support\LegacyImage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(function (Product $product) {
                $product->image_path = LegacyImage::first($product->gambar);
                return $product;
            });

        $topProducts = Sale::query()
            ->select('products.nama', 'products.gambar', DB::raw('SUM(sales.quantity) as total_terjual'))
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->where('sales.status', 'terbayar')
            ->groupBy('products.id', 'products.nama', 'products.gambar')
            ->orderByDesc('total_terjual')
            ->limit(4)
            ->get()
            ->map(function ($item) {
                $item->image_path = LegacyImage::first((string) $item->gambar);
                return $item;
            });

        return view('user.dashboard', [
            'products' => $products,
            'topProducts' => $topProducts,
        ]);
    }
}
