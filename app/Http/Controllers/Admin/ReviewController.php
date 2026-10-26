<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');

        $reviews = DB::table('product_reviews')
            ->join('users', 'users.id', '=', 'product_reviews.user_id')
            ->join('products', 'products.id', '=', 'product_reviews.product_id')
            ->leftJoin('sales', 'sales.id', '=', 'product_reviews.sale_id')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('users.username', 'like', '%' . $search . '%')
                        ->orWhere('products.nama', 'like', '%' . $search . '%')
                        ->orWhere('sales.resi', 'like', '%' . $search . '%')
                        ->orWhere('product_reviews.review', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('product_reviews.created_at')
            ->select([
                'product_reviews.id',
                'product_reviews.rating',
                'product_reviews.review',
                'product_reviews.created_at',
                'users.username',
                'products.nama as nama_produk',
                'sales.resi',
            ])
            ->paginate(20)
            ->withQueryString();

        $reviews->getCollection()->transform(function ($review) {
            preg_match('/\[media:([^\]]+)\]/i', (string) $review->review, $mediaMatch);
            $mediaFile = isset($mediaMatch[1]) ? trim((string) $mediaMatch[1]) : null;
            $ext = strtolower((string) pathinfo((string) $mediaFile, PATHINFO_EXTENSION));

            $review->clean_review = trim((string) preg_replace('/\s*\[media:[^\]]+\]/i', '', (string) $review->review));
            $review->media_file = $mediaFile !== '' ? $mediaFile : null;
            $review->media_is_video = in_array($ext, ['mp4', 'mov', 'avi', 'webm'], true);
            return $review;
        });

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'search' => $search,
        ]);
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        DB::table('product_reviews')->where('id', $id)->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Ulasan berhasil dihapus.');
    }
}
