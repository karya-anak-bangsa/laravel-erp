@use('App\Enums\CompanyProfile\JenisModul')
@use('App\Support\TautanKontak')
@use('App\Support\TeksHtml')

<footer class="kaki-situs dark">
    <div class="garis-merek mono:hidden" aria-hidden="true"></div>
    <div class="wadah kaki-isi">
        <div class="kaki-grid">
            <div class="kaki-merek">
                <a href="{{ route('beranda') }}#beranda" class="inline-flex min-h-11 items-center rounded-tombol" aria-label="{{ $identitas->nama_perusahaan }} — ke beranda">
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
                    {{-- $menuUtama dari SitusComposer, sama dengan menu navbar. --}}
                    @foreach ($menuUtama as $label => $url)
                        <li><a href="{{ $url }}" class="tautan-kaki">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>

            @if ($layanan->isNotEmpty())
                {{-- Tautan ke kartu layanan di beranda; selama seksi Layanan nonaktif (Manajemen Modul) kartunya tidak ada,
                     jadi daftarnya tampil sebagai teks biasa. --}}
                @php($tautanLayanan = $modul->tampil(JenisModul::Layanan))
                <{{ $tautanLayanan ? 'nav' : 'div' }} aria-labelledby="judul-kaki-layanan" class="lg:col-span-3">
                    <h2 id="judul-kaki-layanan" class="judul-kaki">Layanan</h2>
                    <ul class="mt-3 grid">
                        @foreach ($layanan as $item)
                            <li>@if ($tautanLayanan)<a href="{{ route('beranda') }}#layanan-{{ $item->id_layanan }}" class="tautan-kaki">{{ $item->judul }}</a>@else<span class="tautan-kaki">{{ $item->judul }}</span>@endif</li>
                        @endforeach
                    </ul>
                </{{ $tautanLayanan ? 'nav' : 'div' }}>
            @endif

            <div class="col-span-2 md:col-span-1 lg:col-span-3">
                <h2 class="judul-kaki">Kontak</h2>
                <ul class="kontak-kaki mt-3 grid gap-y-2 text-sm">
                    {{-- Alamat 14px seperti teks footer lain. Jarak atas meniru tautan kolom lain (setinggi min-h-11/min-h-9,
                         teks di tengah) agar baris pertama ketiga kolom sejajar; ikon di tengah baris pertama. --}}
                    <li class="flex gap-x-3 pt-2.5 pb-1 sm:pt-1.5">
                        <x-web.ikon nama="lokasi" :tebal="1.75" class="mt-1" />
                        <address class="leading-6 text-pretty not-italic">{{ $identitas->alamat }}</address>
                    </li>
                    @foreach ($identitas->email as $email)
                        <li class="flex items-center gap-x-3">
                            <x-web.ikon nama="email" :tebal="1.75" />
                            <a href="mailto:{{ $email }}" class="tautan-kaki">{{ TautanKontak::emailBisaPatah($email) }}</a>
                        </li>
                    @endforeach
                    {{-- Nomor cukup satu: logo WhatsApp + nomor WhatsApp (pilihan pemilik 2026-10-11); semua nomor telepon
                         hanya cadangan bila link WhatsApp kosong. --}}
                    @if (filled($identitas->link_whatsapp))
                        <li class="flex items-center gap-x-3">
                            <x-web.ikon nama="whatsapp" class="logo-wa" />
                            <x-web.tautan-luar :href="$identitas->link_whatsapp" class="tautan-kaki"><span class="sr-only">WhatsApp </span>{{ TautanKontak::tampilanWhatsapp($identitas->link_whatsapp) }}</x-web.tautan-luar>
                        </li>
                    @else
                        @foreach ($identitas->telepon as $telepon)
                            <li class="flex items-center gap-x-3">
                                <x-web.ikon nama="telepon" :tebal="1.75" />
                                <a href="{{ TautanKontak::telepon($telepon) }}" class="tautan-kaki">{{ $telepon }}</a>
                            </li>
                        @endforeach
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
            {{-- Bila tidak muat satu baris, patah sebelum "Hak cipta" agar "dilindungi." tidak tertinggal sendirian. --}}
            <p>© {{ now()->year }} {{ $identitas->nama_perusahaan }}. <span class="whitespace-nowrap">Hak cipta dilindungi.</span></p>
            <a href="{{ route('beranda') }}#beranda" class="tautan-kaki w-fit gap-x-2 font-semibold">
                Kembali ke atas
                <x-web.ikon nama="panah-atas" class="size-4" />
            </a>
        </div>
    </div>
</footer>
