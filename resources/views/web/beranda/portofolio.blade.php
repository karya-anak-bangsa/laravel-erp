@use('App\Support\AksenWarna')

{{-- Tanpa tab filter kategori (keputusan pemilik 2026-10-11): portofolio nyata masih didominasi website & mobile apps. --}}
<section id="portofolio" class="seksi seksi-redup" aria-labelledby="judul-portofolio">
    <div class="wadah">
        <x-web.kepala-seksi id="judul-portofolio" :nomor="$nomorSeksi('portofolio')" label="Portofolio" judul="Hasil karya kami" rata="tengah">
            Beberapa proyek dan program yang telah kami kerjakan bersama klien dan mitra.
        </x-web.kepala-seksi>

        <ul class="daftar-portofolio mt-10 sm:mt-14" data-jumlah="{{ $portofolio->count() }}">
            @foreach ($portofolio as $item)
                <li class="item-portofolio {{ AksenWarna::untuk($item->kategori) }}">
                    <article class="kartu-portofolio">
                        <div class="kartu-portofolio-gambar"><img src="{{ $item->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="{{ $item->judul }}" class="gambar-konten"></div>
                        <div class="kartu-portofolio-isi">
                            <span class="lencana-kategori"><span class="titik" aria-hidden="true"></span>{{ $item->kategori }}</span>
                            <h3 class="judul-portofolio">{{ $item->judul }}</h3>
                            <div class="deskripsi-portofolio konten-html">{!! $item->deskripsi !!}</div>
                        </div>
                    </article>
                </li>
            @endforeach
        </ul>
        @if ($portofolio->count() > 1)
            <p class="petunjuk-geser" aria-hidden="true">
                Geser untuk melihat lainnya
                <x-web.ikon nama="panah-kanan" class="size-3.5" />
            </p>
        @endif
    </div>
</section>
