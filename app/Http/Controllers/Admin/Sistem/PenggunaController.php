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
            // Akun terhapus tetap tercantum sebagai Nonaktif (pilihan pemilik).
            ->withTrashed()
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
        if ($pengguna->is($request->user())) {
            return $this->tolakAkunSendiri();
        }

        return view('admin.sistem.pengguna.edit', compact('pengguna'));
    }

    public function update(UpdatePenggunaRequest $request, Pengguna $pengguna): RedirectResponse
    {
        if ($pengguna->is($request->user())) {
            return $this->tolakAkunSendiri();
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

        // Barisnya tetap tampil di daftar, jadi toast menyebut statusnya.
        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil dihapus dan kini berstatus Nonaktif.');
    }

    // Email & kata sandi lama tetap berlaku: email akun terhapus memang tidak pernah bisa dipakai akun lain.
    public function aktifkan(Pengguna $pengguna): RedirectResponse
    {
        $pengguna->restore();

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil diaktifkan kembali.');
    }

    // Akun sendiri hanya diubah lewat menu pengguna › Profil Saya (pilihan pemilik), yang tidak memuat isian peran
    // sehingga peran sendiri tidak bisa diubah.
    private function tolakAkunSendiri(): RedirectResponse
    {
        return redirect()->route('admin.pengguna.index')->with('error', 'Akun Anda diubah lewat menu pengguna › Profil Saya.');
    }
}
