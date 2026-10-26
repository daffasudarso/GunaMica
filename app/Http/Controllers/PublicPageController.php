<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Sale;
use App\Support\LegacyImage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        $ratings = DB::table('product_reviews')
            ->select('product_id')
            ->selectRaw('AVG(rating) as avg_rating')
            ->selectRaw('COUNT(*) as total_reviews')
            ->groupBy('product_id');

        $featured = Product::query()
            ->leftJoinSub($ratings, 'ratings', function ($join) {
                $join->on('ratings.product_id', '=', 'products.id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(ratings.avg_rating, 0) as avg_rating')
            ->selectRaw('COALESCE(ratings.total_reviews, 0) as total_reviews')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(function (Product $product) {
                $product->image_path = LegacyImage::first((string) $product->gambar);
                return $product;
            });

        $topProducts = Sale::query()
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->leftJoinSub($ratings, 'ratings', function ($join) {
                $join->on('ratings.product_id', '=', 'products.id');
            })
            ->select('products.id', 'products.nama', 'products.gambar', 'products.harga')
            ->selectRaw('SUM(sales.quantity) as total_terjual')
            ->selectRaw('COALESCE(ratings.avg_rating, 0) as avg_rating')
            ->selectRaw('COALESCE(ratings.total_reviews, 0) as total_reviews')
            ->where('sales.status', 'terbayar')
            ->where('sales.created_at', '>=', now()->subDays(30))
            ->groupBy('products.id', 'products.nama', 'products.gambar', 'products.harga', 'ratings.avg_rating', 'ratings.total_reviews')
            ->orderByDesc('total_reviews')
            ->orderByDesc('total_terjual')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                $item->image_path = LegacyImage::first((string) $item->gambar);
                return $item;
            });

        return view('public.beranda', [
            'featured' => $featured,
            'topProducts' => $topProducts,
        ]);
    }

    public function galeri(): View
    {
        $portfolio = Portfolio::query()
            ->orderByDesc('id')
            ->limit(18)
            ->get()
            ->flatMap(function (Portfolio $item) {
                $mediaList = $this->extractMediaList((string) $item->gambar);
                if (empty($mediaList)) {
                    $mediaList = ['logoB.png'];
                }

                return collect($mediaList)->map(function (string $media) use ($item) {
                    $ext = strtolower((string) pathinfo($media, PATHINFO_EXTENSION));
                    return (object) [
                        'id' => (int) $item->id,
                        'judul' => (string) $item->judul,
                        'kategori' => (string) ($item->kategori ?: 'Umum'),
                        'deskripsi' => (string) ($item->deskripsi ?? ''),
                        'media_path' => $media,
                        'is_video' => in_array($ext, ['mp4', 'mov', 'avi', 'webm'], true),
                    ];
                });
            })
            ->values();

        $byCategory = $portfolio->groupBy(fn ($item) => (string) ($item->kategori ?: 'Umum'));

        return view('public.galeri', [
            'portfolio' => $portfolio,
            'byCategory' => $byCategory,
        ]);
    }

    private function extractMediaList(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        if (str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded, fn ($m) => is_string($m) && trim($m) !== ''));
            }
        }

        return [LegacyImage::first($raw)];
    }

    public function about(): View
    {
        return view('public.about');
    }
}
