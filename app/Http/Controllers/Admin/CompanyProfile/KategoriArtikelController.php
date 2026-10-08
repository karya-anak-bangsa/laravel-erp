<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreKategoriArtikelRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateKategoriArtikelRequest;
use App\Models\CompanyProfile\KategoriArtikel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriArtikelController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();

        $kategoriArtikel = KategoriArtikel::query()
            ->when($q !== '', fn ($query) => $query->where('nama_kategori', 'like', "%{$q}%"))
            ->withCount('artikel')
            // Data master diurutkan abjad.
            ->orderBy('nama_kategori')
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.kategori-artikel.index', compact('kategoriArtikel'));
    }

    public function create(): View
    {
        return view('admin.company-profile.kategori-artikel.create', ['kategoriArtikel' => new KategoriArtikel]);
    }

    public function store(StoreKategoriArtikelRequest $request): RedirectResponse
    {
        KategoriArtikel::create($request->validated());

        return redirect()->route('admin.kategori-artikel.index')->with('success', 'Kategori artikel berhasil ditambahkan.');
    }

    public function edit(KategoriArtikel $kategoriArtikel): View
    {
        return view('admin.company-profile.kategori-artikel.edit', compact('kategoriArtikel'));
    }

    public function update(UpdateKategoriArtikelRequest $request, KategoriArtikel $kategoriArtikel): RedirectResponse
    {
        $kategoriArtikel->update($request->validated());

        return redirect()->route('admin.kategori-artikel.index')->with('success', 'Kategori artikel berhasil diperbarui.');
    }

    public function destroy(KategoriArtikel $kategoriArtikel): RedirectResponse
    {
        // restrictOnDelete di database tidak berlaku untuk soft delete, jadi dicegah di sini
        // agar artikel tidak kehilangan kategorinya.
        if ($kategoriArtikel->artikel()->exists()) {
            return redirect()->route('admin.kategori-artikel.index')->with('error',
                "Kategori “{$kategoriArtikel->nama_kategori}” masih dipakai artikel. Pindahkan atau hapus artikelnya lebih dulu.");
        }

        $kategoriArtikel->delete();

        return redirect()->route('admin.kategori-artikel.index')->with('success', 'Kategori artikel berhasil dihapus.');
    }
}
