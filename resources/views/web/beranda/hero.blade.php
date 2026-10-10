@php
    // Tanpa hero aktif halaman tetap utuh: judul website + ajakan ke formulir kontak.
    $ctaHero = $hero?->cta ?: [['label' => 'Hubungi Kami', 'url' => '#kontak', 'gaya' => 'primary']];
@endphp

<section id="beranda" class="hero relative isolate overflow-hidden" aria-labelledby="judul-hero" data-sembunyikan-wa>
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="pola-kisi pola-pudar absolute inset-0"></div>
        <div class="absolute inset-x-0 top-0 mx-auto h-full max-w-(--lebar-konten) mono:hidden">
            <div class="hero-blob -top-32 -left-24 size-72 bg-(--warna-aksen)/15 sm:size-[28rem]"></div>
            <div class="hero-blob top-10 -right-24 size-80 bg-sky-400/20 sm:size-[34rem]"></div>
            <div class="hero-blob bottom-0 left-1/3 hidden size-[22rem] bg-violet-400/15 sm:block"></div>
        </div>
    </div>

    <div class="hero-tata">
        <div class="hero-teks">
            <p class="hero-eyebrow muncul"><span class="titik" aria-hidden="true"></span>{{ $identitas->nama_perusahaan }}</p>
            <h1 id="judul-hero" class="hero-judul muncul tunda-1">{{ $hero?->judul ?? $identitas->judul_website }}</h1>
            @if ($hero)
                <div class="hero-deskripsi konten-html muncul tunda-2">{!! $hero->deskripsi !!}</div>
            @endif

            <div class="hero-aksi muncul tunda-3">
                @foreach ($ctaHero as $cta)
                    @php($luar = str_starts_with($cta['url'], 'http://') || str_starts_with($cta['url'], 'https://'))
                    <a href="{{ $cta['url'] }}" @if ($luar) target="_blank" rel="noopener" @endif class="btn btn-besar" @if ($cta['gaya'] !== 'primary') data-variant="outline" @endif>
                        {{ $cta['label'] }}
                        @if ($luar)
                            <span class="sr-only">(membuka tab baru)</span>
                        @endif
                    </a>
                @endforeach
            </div>

            @if (filled($hero?->keyword))
                {{-- Warna lencana berputar otomatis (.aksen-berputar) di Full Color. --}}
                <ul class="hero-kata-kunci aksen-berputar muncul tunda-4" aria-label="Bidang layanan">
                    @foreach ($hero->keyword as $kata)
                        <li class="lencana-kata-kunci"><span class="titik" aria-hidden="true"></span>{{ $kata }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($hero)
            <div class="hero-media muncul tunda-2">
                <div class="hero-pendar mono:hidden" aria-hidden="true"></div>
                <div class="hero-titik mono:hidden" aria-hidden="true"></div>
                <figure class="hero-bingkai">
                    <img src="{{ $hero->gambar_url }}" width="1536" height="1024" fetchpriority="high" loading="eager" alt="" class="gambar-konten">
                </figure>
            </div>
        @endif
    </div>
</section>
