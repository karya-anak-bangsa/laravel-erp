@use('App\Support\TautanKontak')
@use('App\Support\TeksHtml')

<footer class="kaki-situs dark" data-sembunyikan-wa>
    <div class="garis-merek mono:hidden" aria-hidden="true"></div>
    <div class="wadah kaki-isi">
        <div class="kaki-grid">
            <div class="kaki-merek">
                <a href="{{ route('beranda') }}#beranda" class="inline-flex min-h-11 items-center rounded-lg warna:rounded-tombol" aria-label="{{ $identitas->nama_perusahaan }} — ke beranda">
                    <img src="{{ $identitas->logo_url }}" alt="" width="107" height="40" loading="lazy" decoding="async" class="logo-putih">
                </a>
                <p class="kaki-nama">{{ $identitas->nama_perusahaan }}</p>
                @php($ringkasanSitus = filled($identitas->meta_deskripsi) ? $identitas->meta_deskripsi : TeksHtml::polos(($hero ?? null)?->deskripsi))
                @if (filled($ringkasanSitus))
                    <p class="kaki-deskripsi">{{ $ringkasanSitus }}</p>
                @endif
            </div>

            <nav aria-labelledby="judul-kaki-tautan" class="lg:col-span-2">
                <h2 id="judul-kaki-tautan" class="judul-kaki">Tautan Cepat</h2>
                <ul class="mt-3 grid">
                    @foreach (['beranda' => 'Beranda', 'layanan' => 'Layanan', 'portofolio' => 'Portofolio', 'artikel' => 'Artikel', 'faq' => 'FAQ', 'kontak' => 'Kontak'] as $anchor => $label)
                        <li><a href="{{ route('beranda') }}#{{ $anchor }}" class="tautan-kaki">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>

            @if ($layanan->isNotEmpty())
                <nav aria-labelledby="judul-kaki-layanan" class="lg:col-span-3">
                    <h2 id="judul-kaki-layanan" class="judul-kaki">Layanan</h2>
                    <ul class="mt-3 grid">
                        @foreach ($layanan as $item)
                            <li><a href="{{ route('beranda') }}#layanan-{{ $item->id_layanan }}" class="tautan-kaki">{{ $item->judul }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            <div class="col-span-2 md:col-span-1 lg:col-span-3">
                <h2 class="judul-kaki">Kontak</h2>
                <ul class="kontak-kaki mt-4 grid gap-y-2 text-[15px]">
                    <li class="flex gap-x-3 pb-1">
                        <x-web.ikon nama="lokasi" :tebal="1.75" class="mt-0.5" />
                        <address class="leading-relaxed not-italic">{{ $identitas->alamat }}</address>
                    </li>
                    <li class="flex items-center gap-x-3">
                        <x-web.ikon nama="email" :tebal="1.75" />
                        <a href="mailto:{{ $identitas->email }}" class="tautan-kaki">{{ TautanKontak::emailBisaPatah($identitas->email) }}</a>
                    </li>
                    <li class="flex items-center gap-x-3">
                        <x-web.ikon nama="telepon" :tebal="1.75" />
                        <a href="{{ TautanKontak::telepon($identitas->telepon) }}" class="tautan-kaki">{{ $identitas->telepon }}</a>
                    </li>
                    @if (filled($identitas->link_whatsapp))
                        <li class="flex items-center gap-x-3">
                            <x-web.ikon nama="pesan" :tebal="1.75" />
                            <x-web.tautan-luar :href="$identitas->link_whatsapp" class="tautan-kaki">WhatsApp</x-web.tautan-luar>
                        </li>
                    @endif
                    @if (filled($identitas->link_instagram))
                        <li class="flex items-center gap-x-3">
                            <x-web.ikon nama="instagram" :tebal="1.75" />
                            <x-web.tautan-luar :href="$identitas->link_instagram" class="tautan-kaki">Instagram</x-web.tautan-luar>
                        </li>
                    @endif
                    @if (filled($identitas->link_youtube))
                        <li class="flex items-center gap-x-3">
                            <x-web.ikon nama="youtube" :tebal="1.75" />
                            <x-web.tautan-luar :href="$identitas->link_youtube" class="tautan-kaki">YouTube</x-web.tautan-luar>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="kaki-bawah">
            <p class="pe-16 sm:pe-0">© {{ now()->year }} {{ $identitas->nama_perusahaan }}. Hak cipta dilindungi.</p>
            <a href="{{ route('beranda') }}#beranda" class="tautan-kaki w-fit gap-x-2 font-semibold">
                Kembali ke atas
                <x-web.ikon nama="panah-atas" class="size-4" />
            </a>
        </div>
    </div>
</footer>
