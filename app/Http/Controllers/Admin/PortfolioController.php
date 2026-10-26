<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Support\LegacyImage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');

        $items = Portfolio::query()
            ->when($search !== '', fn ($q) => $q->where('judul', 'like', '%' . $search . '%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $items->getCollection()->transform(function (Portfolio $item) {
            $media = $this->extractMediaList((string) $item->gambar);
            $item->media_items = $media;
            $item->image_path = $media[0] ?? LegacyImage::first((string) $item->gambar);
            return $item;
        });

        return view('admin.portfolio.index', [
            'items' => $items,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'gambar_files' => ['nullable', 'array'],
            'gambar_files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi'],
        ]);

        $media = $this->uploadMediaFiles($request->file('gambar_files', []));

        Portfolio::query()->create([
            'judul' => $data['judul'],
            'kategori' => $data['kategori'] ?? 'Umum',
            'deskripsi' => $data['deskripsi'] ?? null,
            'gambar' => !empty($media) ? json_encode($media) : null,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = Portfolio::query()->findOrFail($id);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'gambar_files' => ['nullable', 'array'],
            'gambar_files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi'],
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['string'],
        ]);

        $item->judul = $data['judul'];
        $item->kategori = $data['kategori'] ?? 'Umum';
        $item->deskripsi = $data['deskripsi'] ?? null;

        $existingMedia = $this->extractMediaList((string) $item->gambar);
        $removeMedia = collect($data['remove_media'] ?? [])->filter()->values()->all();
        if (!empty($removeMedia)) {
            $existingMedia = array_values(array_filter($existingMedia, fn ($name) => !in_array($name, $removeMedia, true)));
        }

        $newMedia = $this->uploadMediaFiles($request->file('gambar_files', []));
        $mergedMedia = array_values(array_unique(array_merge($existingMedia, $newMedia)));
        $item->gambar = !empty($mergedMedia) ? json_encode($mergedMedia) : null;

        $item->save();

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Portfolio::query()->where('id', $id)->delete();

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio dihapus.');
    }

    private function uploadMediaFiles(array $files): array
    {
        $names = [];

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $name = 'porto_' . uniqid('', true) . '.' . $file->getClientOriginalExtension();
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
