<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreArtikelRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateArtikelRequest;
use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Services\CompanyProfile\ArtikelService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtikelController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $kategori = $request->integer('kategori');
        $status = StatusPublikasi::tryFrom($request->string('status')->value());

        $artikel = Artikel::query()
            ->with('kategoriArtikel')
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('judul', 'like', "%{$q}%")
                ->orWhere('deskripsi', 'like', "%{$q}%")))
            ->when($kategori > 0, fn (Builder $query) => $query->where('id_kategori_artikel', $kategori))
            ->when($status !== null, fn (Builder $query) => $query->where('status_publikasi', $status))
            // Artikel terbaru di atas, sama dengan urutan tampil di website.
            ->orderByDesc('tanggal')
            ->orderByDesc('id_artikel')
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.artikel.index', [
            'artikel' => $artikel,
            'opsiKategori' => $this->opsiKategori(),
        ]);
    }

    public function create(): View
    {
        return view('admin.company-profile.artikel.create', [
            'artikel' => new Artikel(['tanggal' => today(), 'status_publikasi' => StatusPublikasi::Draf]),
            'opsiKategori' => $this->opsiKategori(),
        ]);
    }

    public function store(StoreArtikelRequest $request, ArtikelService $artikelService): RedirectResponse
    {
        $artikelService->simpan($request->validated());

        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Artikel $artikel): View
    {
        return view('admin.company-profile.artikel.edit', [
            'artikel' => $artikel,
            'opsiKategori' => $this->opsiKategori(),
        ]);
    }

    public function update(UpdateArtikelRequest $request, Artikel $artikel, ArtikelService $artikelService): RedirectResponse
    {
        $artikelService->perbarui($artikel, $request->validated());

        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Artikel $artikel): RedirectResponse
    {
        $artikel->delete();

        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil dihapus.');
    }

    /**
     * @return array<int, string> [id_kategori_artikel => nama_kategori], urut abjad
     */
    private function opsiKategori(): array
    {
        return KategoriArtikel::query()->orderBy('nama_kategori')->pluck('nama_kategori', 'id_kategori_artikel')->all();
    }
}
