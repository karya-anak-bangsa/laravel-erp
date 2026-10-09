@use('App\Support\TeksHtml')

<section id="layanan" class="seksi" aria-labelledby="judul-layanan">
    <div class="wadah">
        <x-web.kepala-seksi id="judul-layanan" :nomor="$nomorSeksi('layanan')" label="Layanan" judul="Solusi lengkap untuk kebutuhan digital Anda" rata="tengah">
            Kami mendampingi pengembangan produk digital bisnis Anda sekaligus penyiapan talenta IT, dalam satu tempat.
        </x-web.kepala-seksi>

        {{-- Full Color: keterangan tampil di modal (tombol Selengkapnya); Monochrome: keterangan langsung di kartu. --}}
        <div class="grid-layanan aksen-berputar mt-10 sm:mt-14">
            @foreach ($layanan as $item)
                <article id="layanan-{{ $item->id_layanan }}" class="kartu-layanan">
                    <div class="isi-layanan">
                        <div class="isi-layanan-gambar"><img src="{{ $item->gambar_url }}" width="800" height="600" loading="lazy" decoding="async" alt="Ilustrasi layanan {{ $item->judul }}" class="gambar-konten"></div>
                        <div class="isi-layanan-kepala"><span class="nomor-layanan">{{ sprintf('%02d', $loop->iteration) }}</span><h3 class="judul-layanan">{{ $item->judul }}</h3></div>
                        <div class="isi-layanan-deskripsi konten-html">{!! $item->deskripsi !!}</div>
                        @if (filled(TeksHtml::polos($item->keterangan)))
                            <div class="isi-layanan-rinci konten-html">{!! $item->keterangan !!}</div>
                            <div class="isi-layanan-aksi">
                                <button type="button" class="tombol-selengkapnya" data-buka-layanan aria-haspopup="dialog">
                                    Selengkapnya<span class="sr-only"> tentang {{ $item->judul }}</span>
                                    <x-web.ikon nama="panah-kanan" class="size-4" />
                                </button>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

@if ($layanan->contains(fn ($item) => filled(TeksHtml::polos($item->keterangan))))
    @push('akhir-body')
        @include('web.beranda.dialog-layanan')
    @endpush
@endif
