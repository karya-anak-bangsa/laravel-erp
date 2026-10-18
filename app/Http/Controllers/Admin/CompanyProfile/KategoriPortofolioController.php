<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreKategoriPortofolioRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateKategoriPortofolioRequest;
use App\Models\CompanyProfile\KategoriPortofolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriPortofolioController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();

        $kategoriPortofolio = KategoriPortofolio::query()
            ->when($q !== '', fn ($query) => $query->where('nama_kategori', 'like', "%{$q}%"))
            ->withCount('portofolio')
            // Data master diurutkan abjad.
            ->orderBy('nama_kategori')
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.kategori-portofolio.index', compact('kategoriPortofolio'));
    }

    public function create(): View
    {
        return view('admin.company-profile.kategori-portofolio.create', ['kategoriPortofolio' => new KategoriPortofolio]);
    }

    public function store(StoreKategoriPortofolioRequest $request): RedirectResponse
    {
        KategoriPortofolio::create($request->validated());

        return redirect()->route('admin.kategori-portofolio.index')->with('success', 'Kategori portofolio berhasil ditambahkan.');
    }

    public function edit(KategoriPortofolio $kategoriPortofolio): View
    {
        return view('admin.company-profile.kategori-portofolio.edit', compact('kategoriPortofolio'));
    }

    public function update(UpdateKategoriPortofolioRequest $request, KategoriPortofolio $kategoriPortofolio): RedirectResponse
    {
        $kategoriPortofolio->update($request->validated());

        return redirect()->route('admin.kategori-portofolio.index')->with('success', 'Kategori portofolio berhasil diperbarui.');
    }

    public function destroy(KategoriPortofolio $kategoriPortofolio): RedirectResponse
    {
        // restrictOnDelete di database tidak berlaku untuk soft delete, jadi dicegah di sini
        // agar portofolio tidak kehilangan kategorinya.
        if ($kategoriPortofolio->portofolio()->exists()) {
            return redirect()->route('admin.kategori-portofolio.index')->with('error',
                "Kategori “{$kategoriPortofolio->nama_kategori}” masih dipakai portofolio. Pindahkan atau hapus portofolionya lebih dulu.");
        }

        $kategoriPortofolio->delete();

        return redirect()->route('admin.kategori-portofolio.index')->with('success', 'Kategori portofolio berhasil dihapus.');
    }
}
