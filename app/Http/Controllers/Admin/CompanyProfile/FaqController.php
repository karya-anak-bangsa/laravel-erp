<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreFaqRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateFaqRequest;
use App\Models\CompanyProfile\Faq;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();

        $faq = Faq::query()
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('pertanyaan', 'like', "%{$q}%")
                ->orWhere('jawaban', 'like', "%{$q}%")))
            // Daftar mengikuti urutan tampil di website agar admin melihat susunan yang sama.
            ->berurutan()
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.faq.index', compact('faq'));
    }

    public function create(): View
    {
        return view('admin.company-profile.faq.create', [
            'faq' => new Faq(['urutan_ke' => Faq::urutanBerikutnya()]),
        ]);
    }

    public function store(StoreFaqRequest $request): RedirectResponse
    {
        Faq::create($request->validated());

        return redirect()->route('admin.faq.index')->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.company-profile.faq.edit', compact('faq'));
    }

    public function update(UpdateFaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->validated());

        return redirect()->route('admin.faq.index')->with('success', 'FAQ berhasil diperbarui.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faq.index')->with('success', 'FAQ berhasil dihapus.');
    }
}
