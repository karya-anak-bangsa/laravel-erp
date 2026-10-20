<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreKategoriTutorialRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateKategoriTutorialRequest;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Services\CompanyProfile\KategoriTutorialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriTutorialController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();

        $kategoriTutorial = KategoriTutorial::query()
            ->when($q !== '', fn ($query) => $query->where('nama_kategori', 'like', "%{$q}%"))
            // tutorial_tayang_count menentukan tombol Buka halaman di modal Lihat.
            ->withCount(['tutorial', 'tutorialTayang'])
            // Data master diurutkan abjad.
            ->orderBy('nama_kategori')
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.kategori-tutorial.index', compact('kategoriTutorial'));
    }

    public function create(): View
    {
        return view('admin.company-profile.kategori-tutorial.create', ['kategoriTutorial' => new KategoriTutorial]);
    }

    public function store(StoreKategoriTutorialRequest $request, KategoriTutorialService $kategoriTutorialService): RedirectResponse
    {
        $kategoriTutorialService->simpan($request->validated());

        return redirect()->route('admin.kategori-tutorial.index')->with('success', 'Kategori tutorial berhasil ditambahkan.');
    }

    public function edit(KategoriTutorial $kategoriTutorial): View
    {
        return view('admin.company-profile.kategori-tutorial.edit', compact('kategoriTutorial'));
    }

    public function update(UpdateKategoriTutorialRequest $request, KategoriTutorial $kategoriTutorial, KategoriTutorialService $kategoriTutorialService): RedirectResponse
    {
        $kategoriTutorialService->perbarui($kategoriTutorial, $request->validated());

        return redirect()->route('admin.kategori-tutorial.index')->with('success', 'Kategori tutorial berhasil diperbarui.');
    }

    public function destroy(KategoriTutorial $kategoriTutorial): RedirectResponse
    {
        // restrictOnDelete di database tidak berlaku untuk soft delete, jadi dicegah di sini
        // agar tutorial tidak kehilangan kategorinya.
        if ($kategoriTutorial->tutorial()->exists()) {
            return redirect()->route('admin.kategori-tutorial.index')->with('error',
                "Kategori “{$kategoriTutorial->nama_kategori}” masih dipakai tutorial. Pindahkan atau hapus tutorialnya lebih dulu.");
        }

        $kategoriTutorial->delete();

        return redirect()->route('admin.kategori-tutorial.index')->with('success', 'Kategori tutorial berhasil dihapus.');
    }
}
