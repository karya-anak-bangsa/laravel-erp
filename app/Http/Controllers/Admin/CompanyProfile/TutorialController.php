<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreTutorialRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateTutorialRequest;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\TutorialService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorialController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $kategori = $request->integer('kategori');
        $status = StatusPublikasi::tryFrom($request->string('status')->value());

        $tutorial = Tutorial::query()
            ->with('kategoriTutorial')
            ->withCount('materiTutorial')
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('judul', 'like', "%{$q}%")
                ->orWhere('deskripsi', 'like', "%{$q}%")))
            ->when($kategori > 0, fn (Builder $query) => $query->where('id_kategori_tutorial', $kategori))
            ->when($status !== null, fn (Builder $query) => $query->where('status_publikasi', $status))
            ->terbaru()
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.tutorial.index', [
            'tutorial' => $tutorial,
            'opsiKategori' => KategoriTutorial::opsi(),
        ]);
    }

    public function create(): View
    {
        return view('admin.company-profile.tutorial.create', [
            'tutorial' => new Tutorial(['status_publikasi' => StatusPublikasi::Draf]),
            'opsiKategori' => KategoriTutorial::opsi(),
        ]);
    }

    public function store(StoreTutorialRequest $request, TutorialService $tutorialService): RedirectResponse
    {
        $tutorialService->simpan($request->validated());

        return redirect()->route('admin.tutorial.index')->with('success', 'Tutorial berhasil ditambahkan.');
    }

    public function edit(Tutorial $tutorial): View
    {
        return view('admin.company-profile.tutorial.edit', [
            'tutorial' => $tutorial,
            'opsiKategori' => KategoriTutorial::opsi(),
        ]);
    }

    public function update(UpdateTutorialRequest $request, Tutorial $tutorial, TutorialService $tutorialService): RedirectResponse
    {
        $tutorialService->perbarui($tutorial, $request->validated());

        return redirect()->route('admin.tutorial.index')->with('success', 'Tutorial berhasil diperbarui.');
    }

    public function destroy(Tutorial $tutorial, TutorialService $tutorialService): RedirectResponse
    {
        $tutorialService->hapus($tutorial);

        return redirect()->route('admin.tutorial.index')->with('success', 'Tutorial beserta materinya berhasil dihapus.');
    }
}
