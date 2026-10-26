<?php

namespace App\Http\Controllers;

use App\Models\AsistenFaq;
use App\Models\AsistenLog;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AsistenController extends Controller
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

        // Rules for Orders, Custom, and Shipping have been delegated to Gemini AI using RAG Context.

        // 5. Order Tracking
        if ($this->looksLikeTrackingQuery($lower)) {
            $response = $this->trackOrderReply($message);
            $this->logMessage($message, $response->getData()->reply, 'ai');
            return $response;
        }

        // 6. Product/Category Info
        $categories = Product::select('kategori')->whereNotNull('kategori')->where('kategori', '!=', '')->distinct()->pluck('kategori')->toArray();
        $genericSynonyms = ['produk akrilik', 'produk', 'daftar produk', 'kategori', 'kategori produk', 'jenis', 'jenis produk', 'ada produk apa saja', 'produknya apa saja'];
        $isAskingAboutGenericProduct = false;
        foreach ($genericSynonyms as $syn) {
            if ($lower === $syn || str_starts_with($lower, 'apa saja ' . $syn) || str_starts_with($lower, 'tanya ' . $syn) || $lower === 'lihat ' . $syn) {
                $isAskingAboutGenericProduct = true;
                break;
            }
        }
        
        // If they specifically type "produk akrilik", we treat it as generic inquiry, not specific "Akrilik" category yet.
        if ($isAskingAboutGenericProduct) {
            $reply = "Kami menyediakan berbagai macam produk akrilik custom dengan kategori sebagai berikut:\n\n"
                   . "🔸 " . implode("\n🔸 ", $categories) . "\n\n"
                   . "Silakan pilih salah satu kategori di bawah ini untuk melihat detail produknya ya Kak! 👇";

            $this->logMessage($message, $reply, 'ai');
            return response()->json([
                'success' => true, 
                'reply' => $reply, 
                'source' => 'ai',
                'options' => $categories
            ]);
        }

        // Sort categories by string length descending to prioritize longer matches (e.g. 'Kotak Tisu' before 'Akrilik')
        $sortedCategories = $categories;
        usort($sortedCategories, function($a, $b) {
            return mb_strlen($b) - mb_strlen($a);
        });

        $categoryMatch = null;
        $isCustomInquiry = str_contains($lower, 'custom') || str_contains($lower, 'kustom') || str_contains($lower, 'request') || str_contains($lower, 'bikin') || str_contains($lower, 'buat') || str_contains($lower, 'desain') || str_contains($lower, 'neon box') || str_contains($lower, 'huruf timbul');
        
        if (!$isCustomInquiry) {
            foreach ($sortedCategories as $cat) {
                // Also accept exact match of category
                if ($cat && (str_contains($lower, mb_strtolower($cat)) || $lower === mb_strtolower($cat))) {
                    $categoryMatch = $cat;
                    break;
                }
            }
        }

        if ($categoryMatch) {
            $products = Product::where('kategori', $categoryMatch)->orderBy('harga', 'asc')->get();
            if ($products->count() > 0) {
                $selectedProducts = collect();
                
                if ($products->count() <= 3) {
                    $selectedProducts = $products;
                } else {
                    $termurah = $products->first();
                    $termahal = $products->last();
                    $menengah = $products->get(floor($products->count() / 2));
                    
                    // Add to collection
                    $selectedProducts->push($termurah);
                    if ($menengah->id !== $termurah->id && $menengah->id !== $termahal->id) {
                        $selectedProducts->push($menengah);
                    }
                    if ($termahal->id !== $termurah->id) {
                        $selectedProducts->push($termahal);
                    }
                }
                
                if ($selectedProducts->count() === 3) {
                    $reply = "Untuk kategori *" . $categoryMatch . "*, ini dia rekomendasi produk kami dari yang termurah, menengah, hingga termahal! 😍\n\nKira-kira Kakak tertarik dengan produk yang mana? Langsung aja buka menu Produk ya Kak!";
                } else {
                    $reply = "Untuk kategori *" . $categoryMatch . "*, berikut adalah produk yang kami miliki! 😍\n\nKira-kira Kakak tertarik dengan produk yang mana? Langsung aja buka menu Produk ya Kak!";
                }
                
                $productsData = [];
                foreach ($selectedProducts as $prod) {
                    $imagePath = $prod->gambar;
                    if (!empty($imagePath) && is_string($imagePath) && str_starts_with(trim($imagePath), '[')) {
                        $decoded = json_decode($imagePath, true);
                        if (is_array($decoded) && count($decoded) > 0) {
                            $imagePath = trim($decoded[0]);
                        }
                    }

                    $productsData[] = [
                        'name' => $prod->nama,
                        'price' => number_format((float)$prod->harga, 0, ',', '.'),
                        'image' => $imagePath ? asset('images/' . ltrim($imagePath, '/')) : null
                    ];
                }

                $this->logMessage($message, $reply, 'ai');
                return response()->json([
                    'success' => true, 
                    'reply' => $reply, 
                    'source' => 'ai',
                    'products' => $productsData
                ]);
            }
        }

        $isAskingAboutProduct2 = str_contains($lower, 'produk') || str_contains($lower, 'kategori') || str_contains($lower, 'jenis') || str_contains($lower, 'akrilik');
        
        if ($isAskingAboutProduct2) {
            $words = explode(' ', $lower);
            $hasUnknownSpecific = false;
            foreach ($words as $word) {
                if (!in_array($word, ['produk', 'kategori', 'jenis', 'akrilik', 'yang', 'ada', 'dong', 'mau', 'tanya', 'seputar', 'apa', 'saja', 'daftar', 'harga', 'stok'])) {
                    $hasUnknownSpecific = true;
                }
            }

            if (!$hasUnknownSpecific) {
                $reply = "Kami menyediakan berbagai macam produk akrilik custom dengan kategori sebagai berikut:\n\n"
                       . "🔸 " . implode("\n🔸 ", $categories) . "\n\n"
                       . "Silakan pilih salah satu kategori di bawah ini untuk melihat detail produknya ya Kak! 👇";

                $this->logMessage($message, $reply, 'ai');
                return response()->json([
                    'success' => true, 
                    'reply' => $reply, 
                    'source' => 'ai',
                    'options' => $categories
                ]);
            }
        }

        // Fallback to Groq AI (Llama 3) with RAG Context
        $groqApiKey = env('GROQ_API_KEY');
        if (empty($groqApiKey)) {
            return response()->json([
                'success' => true,
                'reply' => '⚠️ [Sistem] Kunci API Groq (GROQ_API_KEY) tidak ditemukan di file .env server Anda. Silakan tambahkan kunci tersebut agar AI bisa bekerja.',
                'source' => 'fallback',
            ]);
        }
        
        try {
            $shopInfo = "INFORMASI TOKO:\n"
                      . "- Ongkos Kirim (Cek Ongkir): Pelanggan bisa mengecek ongkir secara otomatis di halaman Checkout karena sistem sudah terintegrasi tarif live dari RajaOngkir.\n"
                      . "- Cara Pemesanan / Order: Pelanggan tinggal pilih produk akrilik, klik 'Tambah ke Keranjang', masuk ke Checkout, lalu isi data pengiriman.\n"
                      . "- Metode Pembayaran: HANYA menerima pembayaran melalui MIDTRANS saja (tidak ada sistem COD atau transfer manual di luar web).\n"
                      . "- Custom Produk Akrilik: Jika pelanggan bertanya tentang produk custom, jawab bahwa toko bisa membuatkannya. NAMUN, arahkan mereka untuk LANGSUNG MENGHUBUNGI WHATSAPP toko di nomor +62 821-6180-7523 untuk berdiskusi soal desain, ukuran, dan harga. JANGAN tanyakan detail ukuran atau harga di dalam chat ini.\n"
                      . "- Pelacakan Pesanan: Pelanggan dapat memasukkan nomor resi (contoh: MF2026051117C58BD1) langsung di chat ini untuk melihat status pengiriman, atau melalui menu 'Lacak Pesanan'.";

            $context = "Anda adalah Asisten Virtual Customer Service untuk Guna Mica, sebuah toko yang memproduksi dan menjual produk custom akrilik berkualitas tinggi (plakat, mendali, display, kotak tisu). "
                     . "Jawab pertanyaan customer dengan ramah, hangat, dan profesional. Gunakan sapaan 'Kak' untuk customer, dan berikan jawaban yang komunikatif dan solutif. Gunakan emoticon yang sesuai. "
                     . "PENTING: Selalu gunakan referensi INFORMASI TOKO berikut untuk menjawab pertanyaan yang relevan:\n\n" . $shopInfo . "\n\n"
                     . "Jika ditanya hal di luar konteks toko akrilik Guna Mica, arahkan kembali dengan sopan.";
            
            $response = \Illuminate\Support\Facades\Http::withToken($groqApiKey)
                ->timeout(15)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        ['role' => 'system', 'content' => $context],
                        ['role' => 'user', 'content' => $message],
                    ],
                ]);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['choices'][0]['message']['content'])) {
                    $reply = trim($json['choices'][0]['message']['content']);
                    $this->logMessage($message, $reply, 'ai');
                    return response()->json([
                        'success' => true,
                        'reply' => $reply,
                        'source' => 'ai',
                    ]);
                }
            } else {
                return response()->json([
                    'success' => true,
                    'reply' => '⚠️ [Sistem] API Groq menolak permintaan. Status: ' . $response->status() . ' - ' . $response->body(),
                    'source' => 'fallback',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'reply' => '⚠️ [Sistem] Server InfinityFree memblokir koneksi ke Groq, atau koneksi terputus (Timeout). Detail error: ' . $e->getMessage(),
                'source' => 'fallback',
            ]);
        }

        // Final Fallback if JSON parsing fails
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
        AsistenLog::query()->create([
            'user_id' => Auth::id() ?? 0,
            'user_message' => $msg,
            'bot_reply' => $reply,
            'source' => $source,
            'created_at' => now(),
        ]);
    }

    private function faqReply(string $message): ?string
    {
        $faq = AsistenFaq::query()
            ->where('is_active', 1)
            ->where('question', 'like', '%' . $message . '%')
            ->orderByDesc('id')
            ->first();

        return $faq?->answer;
    }

    private function looksLikeTrackingQuery(string $lower): bool
    {
        $trackingSynonyms = ['lacak', 'resi', 'order', 'status pesanan', 'cek pesanan', 'cek order'];
        foreach ($trackingSynonyms as $syn) {
            if (str_contains($lower, $syn)) {
                return true;
            }
        }
        return false;
    }

    private function trackOrderReply(string $message): JsonResponse
    {
        preg_match('/\b(MF\d{8}[A-Z0-9]{8,40})\b/i', $message, $resiMatch);
        $resi = isset($resiMatch[1]) ? strtoupper((string) $resiMatch[1]) : '';

        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'reply' => 'Boleh login dulu ya Kak, biar saya bisa bantu carikan detail pesanan Kakak dengan aman. 😊',
                'source' => 'ai',
            ]);
        }

        if ($resi === '') {
            return response()->json([
                'success' => true,
                'reply' => 'Boleh minta nomor resinya Kak? Contohnya: "Lacak MF2026051117C58BD1". Nanti langsung saya cekkan statusnya.',
                'source' => 'ai',
            ]);
        }

        $order = Sale::query()
            ->where('user_id', Auth::id())
            ->where('resi', $resi)
            ->orderByDesc('id')
            ->first();

        if (!$order) {
            return response()->json(['success' => true, 'reply' => 'Hmm, sepertinya nomor resi tersebut tidak terdaftar di akun Kakak. Coba dicek lagi ya Kak.', 'source' => 'ai']);
        }

        $reply = "Ini dia status pesanan Kakak:\n\n"
            . "📦 Resi: " . (string) $order->resi . "\n"
            . "💰 Pembayaran: " . strtoupper((string) $order->status) . "\n"
            . "🚚 Status Kirim: " . strtoupper((string) ($order->delivery_status ?? 'Diproses')) . "\n\n"
            . "Semoga barangnya cepat sampai ya Kak! Ada lagi yang mau ditanyakan?";

        return response()->json(['success' => true, 'reply' => $reply, 'source' => 'ai']);
    }
}

