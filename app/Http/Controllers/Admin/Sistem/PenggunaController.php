<?php

namespace App\Http\Controllers\Admin\Sistem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sistem\StorePenggunaRequest;
use App\Http\Requests\Admin\Sistem\UpdatePenggunaRequest;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();

        $pengguna = Pengguna::query()
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('nama', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")))
            // Data master diurutkan abjad.
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        return view('admin.sistem.pengguna.index', compact('pengguna'));
    }

    public function create(): View
    {
        return view('admin.sistem.pengguna.create', ['pengguna' => new Pengguna]);
    }

    public function store(StorePenggunaRequest $request): RedirectResponse
    {
        Pengguna::create($request->validated());

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(Request $request, Pengguna $pengguna): View|RedirectResponse
    {
        // Akun sendiri diubah lewat Profil Saya yang tidak memuat isian peran, agar peran sendiri tidak bisa diubah.
        if ($pengguna->is($request->user())) {
            return redirect()->route('admin.profil.edit');
        }

        return view('admin.sistem.pengguna.edit', compact('pengguna'));
    }

    public function update(UpdatePenggunaRequest $request, Pengguna $pengguna): RedirectResponse
    {
        if ($pengguna->is($request->user())) {
            return redirect()->route('admin.profil.edit');
        }

        $pengguna->update($request->validated());

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, Pengguna $pengguna): RedirectResponse
    {
        // Akun yang sedang dipakai tidak boleh dihapus agar selalu ada pengguna yang bisa login.
        // Akun lain yang dihapus otomatis keluar: soft delete membuat sesinya tidak lagi menemukan akunnya.
        if ($pengguna->is($request->user())) {
            return redirect()->route('admin.pengguna.index')->with('error', 'Akun yang sedang Anda pakai tidak bisa dihapus.');
        }

        $pengguna->delete();

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
