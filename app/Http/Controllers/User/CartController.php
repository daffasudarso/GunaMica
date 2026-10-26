<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\LegacyImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        if (!Auth::check()) {
            $guestCart = collect(session('guest_cart', []));
            $productIds = $guestCart->keys()->map(fn ($id) => (int) $id)->filter()->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $items = $guestCart->map(function ($entry, $productId) use ($products) {
                $product = $products->get((int) $productId);
                if (!$product) {
                    return null;
                }

                $item = new \stdClass();
                $item->id = (int) $product->id;
                $item->product_id = (int) $product->id;
                $item->nama = (string) $product->nama;
                $item->harga = (float) $product->harga;
                $item->gambar = (string) $product->gambar;
                $item->stock = (int) $product->stock;
                $item->quantity = (int) ($entry['quantity'] ?? 1);
                $item->color = (string) ($entry['color'] ?? '');
                $item->image_path = LegacyImage::first((string) $product->gambar);
                $item->subtotal = (float) $item->harga * (int) $item->quantity;

                return $item;
            })->filter()->values();

            $grandTotal = (float) $items->sum('subtotal');

            return view('user.cart.index', [
                'items' => $items,
                'grandTotal' => $grandTotal,
            ]);
        }

        $items = CartItem::query()
            ->where('user_id', Auth::id())
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->select('cart_items.*', 'products.nama', 'products.harga', 'products.gambar', 'products.stock')
            ->get()
            ->map(function ($item) {
                $item->image_path = LegacyImage::first((string) $item->gambar);
                $item->subtotal = (float) $item->harga * (int) $item->quantity;
                return $item;
            });

        $grandTotal = $items->sum('subtotal');

        return view('user.cart.index', [
            'items' => $items,
            'grandTotal' => $grandTotal,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'color' => ['nullable', 'string', 'max:100'],
        ]);

        $product = Product::query()->findOrFail((int) $data['product_id']);
        $qty = min((int) $data['quantity'], max(1, (int) $product->stock));

        if (!Auth::check()) {
            $cart = session('guest_cart', []);
            $productKey = (string) $product->id;
            $currentQty = (int) (($cart[$productKey]['quantity'] ?? 0));
            $cart[$productKey] = [
                'quantity' => min($currentQty + $qty, max(1, (int) $product->stock)),
                'color' => (string) ($data['color'] ?? ($cart[$productKey]['color'] ?? '')),
            ];
            session(['guest_cart' => $cart]);

            return redirect()->route('cart.index')->with('success', 'Produk ditambahkan ke keranjang.');
        }

        $cartItem = CartItem::query()->firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);

        $cartItem->quantity = min(((int) $cartItem->quantity ?: 0) + $qty, max(1, (int) $product->stock));
        $cartItem->color = $data['color'] ?? $cartItem->color;
        $cartItem->save();

        return redirect()->route('cart.index')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'color' => ['nullable', 'string', 'max:100'],
        ]);

        if (!Auth::check()) {
            $cart = session('guest_cart', []);
            $productKey = (string) $id;
            if (!array_key_exists($productKey, $cart)) {
                return redirect()->route('cart.index')->with('error', 'Item keranjang tidak ditemukan.');
            }

            $product = Product::query()->findOrFail((int) $id);
            $cart[$productKey]['quantity'] = min((int) $data['quantity'], max(1, (int) $product->stock));
            $cart[$productKey]['color'] = (string) ($data['color'] ?? '');
            session(['guest_cart' => $cart]);

            return redirect()->route('cart.index')->with('success', 'Keranjang diperbarui.');
        }

        $item = CartItem::query()->where('user_id', Auth::id())->findOrFail($id);
        $product = Product::query()->findOrFail($item->product_id);

        $item->quantity = min((int) $data['quantity'], max(1, (int) $product->stock));
        $item->color = $data['color'] ?? $item->color;
        $item->save();

        return redirect()->route('cart.index')->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        if (!Auth::check()) {
            $cart = session('guest_cart', []);
            $productKey = (string) $id;
            unset($cart[$productKey]);
            session(['guest_cart' => $cart]);

            return redirect()->route('cart.index')->with('success', 'Item dihapus dari keranjang.');
        }

        CartItem::query()
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->delete();

        return redirect()->route('cart.index')->with('success', 'Item dihapus dari keranjang.');
    }
}
