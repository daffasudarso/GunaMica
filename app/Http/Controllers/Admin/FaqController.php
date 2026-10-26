<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsistenFaq;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');

        $faqs = AsistenFaq::query()
            ->when($search !== '', fn ($q) => $q->where('question', 'like', '%' . $search . '%'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.asisten-faq.index', [
            'faqs' => $faqs,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        AsistenFaq::query()->create([
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_active' => (int) ($data['is_active'] ?? 1),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        return redirect()->route('admin.faq.index')->with('success', 'FAQ ditambahkan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $faq = AsistenFaq::query()->findOrFail($id);
        $faq->question = $data['question'];
        $faq->answer = $data['answer'];
        $faq->is_active = (int) ($data['is_active'] ?? 0);
        $faq->updated_at = Carbon::now()->toDateTimeString();
        $faq->save();

        return redirect()->route('admin.faq.index')->with('success', 'FAQ diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        AsistenFaq::query()->where('id', $id)->delete();

        return redirect()->route('admin.faq.index')->with('success', 'FAQ dihapus.');
    }
}

