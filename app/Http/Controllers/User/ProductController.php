<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\LegacyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $ratings = DB::table('product_reviews')
            ->select('product_id')
            ->selectRaw('AVG(rating) as avg_rating')
            ->selectRaw('COUNT(*) as total_reviews')
            ->groupBy('product_id');

        $query = Product::query()
            ->leftJoinSub($ratings, 'ratings', function ($join) {
                $join->on('ratings.product_id', '=', 'products.id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(ratings.avg_rating, 0) as avg_rating')
            ->selectRaw('COALESCE(ratings.total_reviews, 0) as total_reviews');

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->string('search') . '%');
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->string('kategori'));
        }

        $products = $query->orderByDesc('id')->paginate(12)->withQueryString();
        $products->getCollection()->transform(function (Product $product) {
            $product->image_path = LegacyImage::first($product->gambar);
            return $product;
        });

        $categories = Product::query()->select('kategori')->distinct()->pluck('kategori');

        return view('user.products.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => (string) $request->string('search'),
            'kategori' => (string) $request->string('kategori'),
        ]);
    }

    public function show(int $id): View
    {
        $ratings = DB::table('product_reviews')
            ->select('product_id')
            ->selectRaw('AVG(rating) as avg_rating')
            ->selectRaw('COUNT(*) as total_reviews')
            ->groupBy('product_id');

        $product = Product::query()
            ->leftJoinSub($ratings, 'ratings', function ($join) {
                $join->on('ratings.product_id', '=', 'products.id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(ratings.avg_rating, 0) as avg_rating')
            ->selectRaw('COALESCE(ratings.total_reviews, 0) as total_reviews')
            ->findOrFail($id);

        $product->image_path = LegacyImage::first($product->gambar);
        $product->gallery_images = $this->extractGalleryImages($product->gambar, $product->gambar2, $product->gambar3);

        $related = Product::query()
            ->where('kategori', $product->kategori)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get()
            ->map(function (Product $item) {
                $item->image_path = LegacyImage::first($item->gambar);
                return $item;
            });

        $reviews = DB::table('product_reviews')
            ->join('users', 'users.id', '=', 'product_reviews.user_id')
            ->where('product_reviews.product_id', $product->id)
            ->orderByDesc('product_reviews.created_at')
            ->limit(12)
            ->get([
                'product_reviews.rating',
                'product_reviews.review',
                'product_reviews.created_at',
                'users.username',
                'users.profile_image',
            ])
            ->map(function ($review) {
                preg_match('/\[media:([^\]]+)\]/i', (string) $review->review, $mediaMatch);
                $mediaFile = isset($mediaMatch[1]) ? trim((string) $mediaMatch[1]) : null;
                $ext = strtolower((string) pathinfo((string) $mediaFile, PATHINFO_EXTENSION));
                $review->clean_review = trim((string) preg_replace('/\s*\[media:[^\]]+\]/i', '', (string) $review->review));
                $review->media_file = $mediaFile !== '' ? $mediaFile : null;
                $review->media_is_video = in_array($ext, ['mp4', 'mov', 'avi', 'webm'], true);
                return $review;
            });

        return view('user.products.show', [
            'product' => $product,
            'related' => $related,
            'reviews' => $reviews,
        ]);
    }

    private function extractGalleryImages(?string $gambar, ?string $gambar2, ?string $gambar3): array
    {
        $images = [];

        if (!empty($gambar)) {
            $raw = trim((string) $gambar);
            if ($raw !== '' && str_starts_with($raw, '[')) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $img) {
                        if (is_string($img) && trim($img) !== '') {
                            $images[] = trim($img, " \t\n\r\0\x0B\"'");
                        }
                    }
                }
            }

            if (empty($images)) {
                $images[] = LegacyImage::first($gambar);
            }
        }

        foreach ([$gambar2, $gambar3] as $extra) {
            if (is_string($extra) && trim($extra) !== '') {
                $images[] = trim($extra);
            }
        }

        $images = array_values(array_unique(array_filter($images, fn ($img) => is_string($img) && $img !== '')));

        if (empty($images)) {
            $images[] = 'logoB.png';
        }

        return $images;
    }
}
