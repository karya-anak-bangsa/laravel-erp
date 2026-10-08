<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\UpdateStatusBacaKontakKamiRequest;
use App\Models\CompanyProfile\KontakKami;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KontakKamiController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $status = $request->string('status')->value();

        $kontakKami = KontakKami::query()
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('nama', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('subjek', 'like', "%{$q}%")
                ->orWhere('pesan', 'like', "%{$q}%")))
            ->when(in_array($status, ['belum-dibaca', 'dibaca'], true), fn (Builder $query) => $query->where('status_baca', $status === 'dibaca'))
            // Seperti kotak masuk email: pesan terbaru di atas.
            ->orderByDesc('tanggal')
            ->orderByDesc('id_kontak_kami')
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.kontak-kami.index', compact('kontakKami'));
    }

    /**
     * Dipanggil tombol tandai (form biasa) maupun admin.js saat modal detail dibuka (JSON).
     */
    public function statusBaca(UpdateStatusBacaKontakKamiRequest $request, KontakKami $kontakKami): RedirectResponse|JsonResponse
    {
        $kontakKami->update(['status_baca' => $request->boolean('status_baca')]);

        if ($request->expectsJson()) {
            return response()->json([
                'status_baca' => $kontakKami->status_baca,
                'belum_dibaca' => KontakKami::query()->belumDibaca()->count(),
            ]);
        }

        // Kembali ke halaman asal agar pencarian, filter, dan nomor halaman tidak hilang.
        return redirect()->back(fallback: route('admin.kontak-kami.index'))->with('success', $kontakKami->status_baca
            ? 'Pesan ditandai sudah dibaca.'
            : 'Pesan ditandai belum dibaca.');
    }

    public function destroy(KontakKami $kontakKami): RedirectResponse
    {
        $kontakKami->delete();

        return redirect()->route('admin.kontak-kami.index')->with('success', 'Pesan berhasil dihapus.');
    }
}
