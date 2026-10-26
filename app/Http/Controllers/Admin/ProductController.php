<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\LegacyImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');

        $products = Product::query()
            ->when($search !== '', fn ($q) => $q->where('nama', 'like', '%' . $search . '%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->transform(function (Product $product) {
            $product->image_path = LegacyImage::first($product->gambar);
            return $product;
        });

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'warna_options' => ['nullable', 'array'],
            'warna_options.*' => ['string', 'max:50'],
            'ketebalan' => ['nullable', 'string', 'max:100'],
            'ukuran' => ['nullable', 'string', 'max:100'],
            'weight_gram' => ['nullable', 'integer', 'min:1'],
            'media_files' => ['nullable', 'array'],
            'media_files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi'],
        ]);

        $media = $this->uploadMediaFiles($request->file('media_files', []));
        if (empty($media)) {
            $media = ['logoB.png'];
        }

        $checkedColors = collect($data['warna_options'] ?? [])->filter()->values()->all();
        $warna = implode(', ', array_values(array_unique($checkedColors)));

        Product::query()->create([
            'nama' => $data['nama'],
            'kategori' => $data['kategori'],
            'harga' => $data['harga'],
            'stock' => $data['stock'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'warna' => $warna !== '' ? $warna : null,
            'ketebalan' => $data['ketebalan'] ?? null,
            'ukuran' => $data['ukuran'] ?? null,
            'weight_gram' => $data['weight_gram'] ?? 1000,
            'gambar' => json_encode($media),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produk ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = Product::query()->findOrFail($id);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'warna_options' => ['nullable', 'array'],
            'warna_options.*' => ['string', 'max:50'],
            'ketebalan' => ['nullable', 'string', 'max:100'],
            'ukuran' => ['nullable', 'string', 'max:100'],
            'weight_gram' => ['nullable', 'integer', 'min:1'],
            'media_files' => ['nullable', 'array'],
            'media_files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi'],
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['string'],
        ]);

        $existingMedia = $this->extractMediaList((string) $product->gambar);
        $removeMedia = collect($data['remove_media'] ?? [])->filter()->values()->all();
        if (!empty($removeMedia)) {
            $existingMedia = array_values(array_filter($existingMedia, fn ($name) => !in_array($name, $removeMedia, true)));
        }

        $newMedia = $this->uploadMediaFiles($request->file('media_files', []));
        $mergedMedia = array_values(array_unique(array_merge($existingMedia, $newMedia)));
        if (empty($mergedMedia)) {
            $mergedMedia = ['logoB.png'];
        }

        $checkedColors = collect($data['warna_options'] ?? [])->filter()->values()->all();
        $warna = implode(', ', array_values(array_unique($checkedColors)));

        $product->fill($data);
        $product->warna = $warna !== '' ? $warna : null;
        $product->gambar = json_encode($mergedMedia);
        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Produk diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Product::query()->where('id', $id)->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk dihapus.');
    }

    private function uploadMediaFiles(array $files): array
    {
        $names = [];

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $name = 'prd_' . uniqid('', true) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $name);
            $names[] = $name;
        }

        return $names;
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
}
