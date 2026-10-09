<?php

namespace App\Http\Controllers\Web\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CompanyProfile\StoreKontakKamiRequest;
use App\Models\CompanyProfile\KontakKami;
use Illuminate\Http\RedirectResponse;

class KontakKamiController extends Controller
{
    public const PESAN_TERKIRIM = 'Terima kasih, pesan Anda sudah kami terima. Kami akan segera menghubungi Anda.';

    public const PESAN_DIBATASI = 'Terlalu banyak pesan dalam waktu singkat. Silakan coba lagi sekitar satu menit lagi.';

    public const PESAN_SESI_HABIS = 'Halaman sudah terlalu lama dibuka sehingga sesi berakhir. Silakan kirim ulang pesan Anda.';

    /**
     * Tujuan kembali setelah mengirim (sukses, galat, batas kiriman, sesi habis): kartu formulir di beranda,
     * agar pesan sukses/galat langsung terlihat tanpa menggulir.
     */
    public static function urlFormulir(): string
    {
        return route('beranda').'#kirim-pesan';
    }

    /**
     * Pesan dari formulir kontak beranda masuk ke kotak masuk admin (Company Profile › Kontak Kami).
     */
    public function store(StoreKontakKamiRequest $request): RedirectResponse
    {
        // Bot tetap mendapat balasan sukses agar tidak tahu honeypot-nya terdeteksi.
        if (! $request->dariBot()) {
            KontakKami::create([
                ...$request->safe()->except('website'),
                'tanggal' => now(),
                'status_baca' => false,
            ]);
        }

        return redirect()->to(self::urlFormulir())->with('kontak_terkirim', self::PESAN_TERKIRIM);
    }
}
