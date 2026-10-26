<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFaq;
use App\Models\ChatbotLog;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    public function message(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = trim($data['message']);
        $lower = mb_strtolower($message);

        // 1. Check FAQ
        $faqReply = $this->faqReply($message);
        if ($faqReply !== null) {
            $this->logMessage($message, $faqReply, 'faq');
            return response()->json(['success' => true, 'reply' => $faqReply, 'source' => 'faq']);
        }

        // 2. Order Process
        if (str_contains($lower, 'cara order') || str_contains($lower, 'cara pesan')) {
            $reply = "Cara pesannya gampang banget kok Kak! 😊\n\n1. Kakak pilih dulu produk akrilik yang disukai.\n2. Klik 'Tambah ke Keranjang'.\n3. Masuk ke menu Checkout dan lengkapi data pengiriman Kakak.\n4. Selesaikan pembayaran via Midtrans (bisa pakai QRIS, Transfer Bank, dll).\n5. Setelah itu, Kakak tinggal duduk manis dan pantau statusnya di menu 'Lacak Pesanan'.\n\nAda bagian yang kurang jelas Kak?";
            $this->logMessage($message, $reply, 'ai');
            return response()->json([
                'success' => true,
                'reply' => $reply,
                'source' => 'rule',
            ]);
        }

        // 3. Custom Order
        if (str_contains($lower, 'custom')) {
            $reply = "Tentu bisa Kak! Kami spesialis custom akrilik. 😍\n\nBoleh diinfokan Kakak butuh desain seperti apa? Jangan lupa sertakan ukuran (panjang x lebar x tebal) dan jumlahnya ya, biar tim kami bisa bantu hitungkan estimasi harganya segera.";
            $this->logMessage($message, $reply, 'ai');
            return response()->json([
                'success' => true,
                'reply' => $reply,
                'source' => 'rule',
            ]);
        }

        // 4. Shipping Info
        if (str_contains($lower, 'ongkir')) {
            $reply = "Untuk cek ongkir, Kakak bisa langsung coba di halaman Checkout ya. Masukkan alamat tujuan, nanti sistem kami akan otomatis menampilkan pilihan kurir dan tarif live dari RajaOngkir. Jadi Kakak bisa pilih yang paling pas di kantong! 🚚✨";
            $this->logMessage($message, $reply, 'ai');
            return response()->json([
                'success' => true,
                'reply' => $reply,
                'source' => 'rule',
            ]);
        }

        // 5. Order Tracking
        if ($this->looksLikeTrackingQuery($lower)) {
            $response = $this->trackOrderReply($message);
            $this->logMessage($message, $response->getData()->reply, 'ai');
            return $response;
        }

        // 6. Product/Price Search (using top 3 best sellers by rating)
        if (str_contains($lower, 'produk') || str_contains($lower, 'harga') || str_contains($lower, 'stok') || str_contains($lower, 'akrilik')) {
            // ... (keep the logic here, just capture $reply)
            $ratings = DB::table('product_reviews')
                ->select('product_id')
                ->selectRaw('COUNT(*) as total_reviews')
                ->groupBy('product_id');

            $products = Sale::query()
                ->join('products', 'products.id', '=', 'sales.product_id')
                ->leftJoinSub($ratings, 'ratings', function ($join) {
                    $join->on('ratings.product_id', '=', 'products.id');
                })
                ->select('products.nama', 'products.harga', 'products.stock')
                ->selectRaw('SUM(sales.quantity) as total_terjual')
                ->selectRaw('COALESCE(ratings.total_reviews, 0) as total_reviews')
                ->where('sales.status', 'terbayar')
                ->where('sales.created_at', '>=', now()->subDays(30))
                ->groupBy('products.id', 'products.nama', 'products.harga', 'products.stock', 'ratings.total_reviews')
                ->orderByDesc('total_reviews')
                ->orderByDesc('total_terjual')
                ->limit(3)
                ->get();

            if ($products->isEmpty()) {
                $products = Product::query()->orderByDesc('id')->limit(3)->get();
            }

            $lines = ["Tentu Kak! Untuk saat ini harga akrilik yang tersedia adalah :"];
            foreach ($products as $product) {
                $lines[] = '• ' . $product->nama . ' — Rp' . number_format((float) $product->harga, 0, ',', '.') . ' (Stok: ' . (int) $product->stock . ')';
            }
            $lines[] = "\nProduk di atas adalah yang paling favorit di toko kami lho Kak. Kakak tertarik dengan salah satunya?";
            $reply = implode("\n", $lines);

            $this->logMessage($message, $reply, 'ai');
            return response()->json(['success' => true, 'reply' => $reply, 'source' => 'rule']);
        }

        // Fallback
        $reply = "Maaf Kak, saya belum menangkap maksudnya. 😅\n\nKakak bisa tanya seputar detail produk, cara order, cek ongkir, atau lacak pesanan. Atau mungkin ada hal lain yang bisa saya bantu?";
        $this->logMessage($message, $reply, 'fallback');
        return response()->json([
            'success' => true,
            'reply' => $reply,
            'source' => 'fallback',
        ]);
    }

    private function logMessage(string $msg, string $reply, string $source): void
    {
        ChatbotLog::query()->create([
            'user_id' => Auth::id() ?? 0,
            'user_message' => $msg,
            'bot_reply' => $reply,
            'source' => $source,
            'created_at' => now(),
        ]);
    }

    private function faqReply(string $message): ?string
    {
        $faq = ChatbotFaq::query()
            ->where('is_active', 1)
            ->where('question', 'like', '%' . $message . '%')
            ->orderByDesc('id')
            ->first();

        return $faq?->answer;
    }

    private function looksLikeTrackingQuery(string $lower): bool
    {
        return str_contains($lower, 'lacak') || str_contains($lower, 'resi') || str_contains($lower, 'order');
    }

    private function trackOrderReply(string $message): JsonResponse
    {
        preg_match('/\b(MF\d{8}[A-Z0-9]{8,40})\b/i', $message, $resiMatch);
        $resi = isset($resiMatch[1]) ? strtoupper((string) $resiMatch[1]) : '';

        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'reply' => 'Boleh login dulu ya Kak, biar saya bisa bantu carikan detail pesanan Kakak dengan aman. 😊',
                'source' => 'rule',
            ]);
        }

        if ($resi === '') {
            return response()->json([
                'success' => true,
                'reply' => 'Boleh minta nomor resinya Kak? Contohnya: "Lacak MF2026051117C58BD1". Nanti langsung saya cekkan statusnya.',
                'source' => 'rule',
            ]);
        }

        $order = Sale::query()
            ->where('user_id', Auth::id())
            ->where('resi', $resi)
            ->orderByDesc('id')
            ->first();

        if (!$order) {
            return response()->json(['success' => true, 'reply' => 'Hmm, sepertinya nomor resi tersebut tidak terdaftar di akun Kakak. Coba dicek lagi ya Kak.', 'source' => 'rule']);
        }

        $reply = "Ini dia status pesanan Kakak:\n\n"
            . "📦 Resi: " . (string) $order->resi . "\n"
            . "💰 Pembayaran: " . strtoupper((string) $order->status) . "\n"
            . "🚚 Status Kirim: " . strtoupper((string) ($order->delivery_status ?? 'Diproses')) . "\n\n"
            . "Semoga barangnya cepat sampai ya Kak! Ada lagi yang mau ditanyakan?";

        return response()->json(['success' => true, 'reply' => $reply, 'source' => 'rule']);
    }
}
