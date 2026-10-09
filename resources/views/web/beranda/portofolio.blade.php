@use('App\Support\AksenWarna')
@use('Illuminate\Support\Str')

@php
    // Tab hanya dari kategori item yang tampil, agar tidak ada tab kosong.
    $kategoriTab = $portofolio->pluck('kategori')->unique(fn (string $kategori) => Str::slug($kategori))->values();
@endphp

<section id="portofolio" class="seksi seksi-redup" aria-labelledby="judul-portofolio">
    <div class="wadah">
        <x-web.kepala-seksi id="judul-portofolio" :nomor="$nomorSeksi('portofolio')" label="Portofolio" judul="Hasil karya kami" rata="tengah">
            Beberapa proyek dan program yang telah kami kerjakan bersama klien dan mitra.
        </x-web.kepala-seksi>

        {{-- Tabs Basecoat: semua tab mengendalikan satu panel; resources/js/web/portofolio.js menyaring kartu. --}}
        <div class="tabs filter-portofolio" id="filter-portofolio">
            @if ($kategoriTab->count() > 1)
                <nav role="tablist" aria-orientation="horizontal" aria-label="Filter kategori portofolio">
                    <button type="button" role="tab" id="tab-portofolio-semua" aria-controls="panel-portofolio" aria-selected="true" tabindex="0" data-filter="semua">Semua</button>
                    @foreach ($kategoriTab as $kategori)
                        <button type="button" role="tab" id="tab-portofolio-{{ Str::slug($kategori) }}" aria-controls="panel-portofolio" aria-selected="false" tabindex="-1" data-filter="{{ Str::slug($kategori) }}">{{ $kategori }}</button>
                    @endforeach
                </nav>
            @endif

            <div role="tabpanel" id="panel-portofolio" @if ($kategoriTab->count() > 1) aria-labelledby="tab-portofolio-semua" tabindex="0" @else aria-labelledby="judul-portofolio" @endif>
                <ul class="daftar-portofolio" data-jumlah="{{ $portofolio->count() }}">
                    @foreach ($portofolio as $item)
                        <li class="item-portofolio {{ AksenWarna::untuk($item->kategori) }}" data-kategori="{{ Str::slug($item->kategori) }}">
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
                    <p id="petunjuk-geser" class="petunjuk-geser" aria-hidden="true">
                        Geser untuk melihat lainnya
                        <x-web.ikon nama="panah-kanan" class="size-3.5" />
                    </p>
                @endif
                <p id="info-filter" class="sr-only" aria-live="polite"></p>
            </div>
        </div>
    </div>
</section>
