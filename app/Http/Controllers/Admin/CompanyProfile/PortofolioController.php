<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StorePortofolioRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdatePortofolioRequest;
use App\Models\CompanyProfile\KategoriPortofolio;
use App\Models\CompanyProfile\Portofolio;
use App\Services\CompanyProfile\PortofolioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortofolioController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $kategori = $request->integer('kategori');

        $portofolio = Portofolio::query()
            ->with('kategoriPortofolio')
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('judul', 'like', "%{$q}%")
                ->orWhere('deskripsi', 'like', "%{$q}%")))
            ->when($kategori > 0, fn (Builder $query) => $query->where('id_kategori_portofolio', $kategori))
            // Jumlah gambar tambahan untuk modal Lihat.
            ->withCount('gambarPortofolio')
            // Sama dengan urutan website, termasuk pemecah created_at kembar (data seeder dibuat dalam detik yang sama).
            ->terbaru()
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.portofolio.index', [
            'portofolio' => $portofolio,
            'opsiKategori' => KategoriPortofolio::opsi(),
        ]);
    }

    public function create(): View
    {
        return view('admin.company-profile.portofolio.create', [
            'portofolio' => new Portofolio,
            'opsiKategori' => KategoriPortofolio::opsi(),
        ]);
    }

    public function store(StorePortofolioRequest $request, PortofolioService $portofolioService): RedirectResponse
    {
        $portofolioService->simpan($request->validated());

        return redirect()->route('admin.portofolio.index')->with('success', 'Portofolio berhasil ditambahkan.');
    }

    public function edit(Portofolio $portofolio): View
    {
        return view('admin.company-profile.portofolio.edit', [
            'portofolio' => $portofolio->load('gambarPortofolio'),
            'opsiKategori' => KategoriPortofolio::opsi(),
        ]);
    }

    public function update(UpdatePortofolioRequest $request, Portofolio $portofolio, PortofolioService $portofolioService): RedirectResponse
    {
        $portofolioService->perbarui($portofolio, $request->validated());

        return redirect()->route('admin.portofolio.index')->with('success', 'Portofolio berhasil diperbarui.');
    }

    public function destroy(Portofolio $portofolio): RedirectResponse
    {
        $portofolio->delete();

        return redirect()->route('admin.portofolio.index')->with('success', 'Portofolio berhasil dihapus.');
    }
}
