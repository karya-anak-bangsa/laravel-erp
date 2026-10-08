<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreHeroRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateHeroRequest;
use App\Models\CompanyProfile\Hero;
use App\Services\CompanyProfile\HeroService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeroController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $status = $request->string('status')->value();

        $hero = Hero::query()
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('judul', 'like', "%{$q}%")
                ->orWhere('deskripsi', 'like', "%{$q}%")))
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn (Builder $query) => $query->where('status_aktif', $status === 'aktif'))
            // Hero yang sedang tampil di beranda selalu di baris pertama.
            ->orderByDesc('status_aktif')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.hero.index', compact('hero'));
    }

    public function create(): View
    {
        return view('admin.company-profile.hero.create', [
            // Hero pertama langsung aktif; hero berikutnya nonaktif agar beranda tidak berubah tanpa sengaja.
            'hero' => new Hero([
                'keyword' => [],
                'cta' => [],
                'status_aktif' => Hero::query()->where('status_aktif', true)->doesntExist(),
            ]),
        ]);
    }

    public function store(StoreHeroRequest $request, HeroService $heroService): RedirectResponse
    {
        $heroService->simpan($request->validated());

        return redirect()->route('admin.hero.index')->with('success', 'Hero berhasil ditambahkan.');
    }

    public function edit(Hero $hero): View
    {
        return view('admin.company-profile.hero.edit', compact('hero'));
    }

    public function update(UpdateHeroRequest $request, Hero $hero, HeroService $heroService): RedirectResponse
    {
        $heroService->perbarui($hero, $request->validated());

        return redirect()->route('admin.hero.index')->with('success', 'Hero berhasil diperbarui.');
    }

    public function destroy(Hero $hero): RedirectResponse
    {
        $hero->delete();

        return redirect()->route('admin.hero.index')->with('success', 'Hero berhasil dihapus.');
    }
}
