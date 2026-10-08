<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StorePortofolioRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdatePortofolioRequest;
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
        $kategori = $request->string('kategori')->value();

        $portofolio = Portofolio::query()
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('judul', 'like', "%{$q}%")
                ->orWhere('deskripsi', 'like', "%{$q}%")))
            ->when($kategori !== '', fn (Builder $query) => $query->where('kategori', $kategori))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.portofolio.index', [
            'portofolio' => $portofolio,
            'daftarKategori' => Portofolio::daftarKategori(),
        ]);
    }

    public function create(): View
    {
        return view('admin.company-profile.portofolio.create', [
            'portofolio' => new Portofolio,
            'daftarKategori' => Portofolio::daftarKategori(),
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
            'portofolio' => $portofolio,
            'daftarKategori' => Portofolio::daftarKategori(),
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
