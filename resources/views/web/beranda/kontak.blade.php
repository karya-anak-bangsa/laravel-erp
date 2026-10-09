@use('App\Support\TautanKontak')

@php
    $peta = config('perusahaan.peta');
    $tautanPeta = "https://www.openstreetmap.org/?mlat={$peta['lat']}&mlon={$peta['lng']}#map=17/{$peta['lat']}/{$peta['lng']}";
@endphp

<section id="kontak" class="seksi" aria-labelledby="judul-kontak" data-sembunyikan-wa>
    <div class="wadah">
        <x-web.kepala-seksi id="judul-kontak" :nomor="$nomorSeksi('kontak')" label="Kontak" judul="Mari diskusikan kebutuhan Anda" rata="tengah">
            Kirim pesan melalui formulir di bawah ini, atau hubungi kami langsung lewat WhatsApp dan email.
        </x-web.kepala-seksi>

        {{-- Susunan berbeda per tema lewat grid-template-areas; urutan DOM tetap. --}}
        <div class="tata-kontak">
            <ul class="info-kontak">
                <li>
                    <div class="tautan-kontak">
                        <span class="ikon-kontak" data-jenis="alamat" aria-hidden="true"><x-web.ikon nama="lokasi" :tebal="1.75" /></span>
                        <span class="isi-kontak">
                            <span class="label-kontak">Alamat</span>
                            <address class="nilai-kontak not-italic">{{ $identitas->alamat }}</address>
                        </span>
                    </div>
                </li>
                <li>
                    <a href="mailto:{{ $identitas->email }}" class="tautan-kontak">
                        <span class="ikon-kontak" data-jenis="email" aria-hidden="true"><x-web.ikon nama="email" :tebal="1.75" /></span>
                        <span class="isi-kontak">
                            <span class="label-kontak">Email</span>
                            <span class="nilai-kontak">{{ TautanKontak::emailBisaPatah($identitas->email) }}</span>
                        </span>
                        <x-web.ikon nama="panah-keluar" class="panah-kontak" />
                    </a>
                </li>
                <li>
                    <a href="{{ TautanKontak::telepon($identitas->telepon) }}" class="tautan-kontak">
                        <span class="ikon-kontak" data-jenis="telepon" aria-hidden="true"><x-web.ikon nama="telepon" :tebal="1.75" /></span>
                        <span class="isi-kontak">
                            <span class="label-kontak">Telepon</span>
                            <span class="nilai-kontak">{{ $identitas->telepon }}</span>
                        </span>
                        <x-web.ikon nama="panah-keluar" class="panah-kontak" />
                    </a>
                </li>
                @if (filled($identitas->link_whatsapp))
                    <li>
                        <a href="{{ $identitas->link_whatsapp }}" target="_blank" rel="noopener" class="tautan-kontak">
                            <span class="ikon-kontak" data-jenis="whatsapp" aria-hidden="true"><x-web.ikon nama="pesan" :tebal="1.75" /></span>
                            <span class="isi-kontak">
                                <span class="label-kontak">WhatsApp</span>
                                <span class="nilai-kontak">{{ TautanKontak::tampilanWhatsapp($identitas->link_whatsapp) }}</span>
                                <span class="sr-only">(membuka tab baru)</span>
                            </span>
                            <x-web.ikon nama="panah-keluar" class="panah-kontak" />
                        </a>
                    </li>
                @endif
            </ul>

            {{-- Pesan masuk ke kotak masuk admin (Company Profile › Kontak Kami). --}}
            <div id="kirim-pesan" class="kartu-formulir">
                <h3 class="judul-formulir">Kirim pesan</h3>
                <p class="catatan-formulir">Isian bertanda <span class="tanda-wajib font-semibold">*</span> wajib diisi.</p>

                <form id="form-kontak" action="{{ route('kontak-kami.store') }}" method="post" class="form-kontak" novalidate>
                    @csrf

                    @error('formulir')
                        <div class="kotak-galat" role="alert">
                            <x-web.ikon nama="peringatan" class="mt-px size-5 shrink-0" />
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-web.isian id="kontak-nama" nama="nama" label="Nama" :wajib="true" maks="100" autocomplete="name" placeholder="Nama lengkap Anda" />
                        <x-web.isian id="kontak-email" nama="email" label="Email" jenis="email" :wajib="true" maks="150" autocomplete="email" placeholder="nama@email.com" />
                        <x-web.isian id="kontak-subjek" nama="subjek" label="Subjek" :wajib="true" maks="200" placeholder="Contoh: Website company profile" class="sm:col-span-2" />
                    </div>
                    <x-web.isian id="kontak-pesan" nama="pesan" label="Pesan" jenis="textarea" :wajib="true" maks="5000" placeholder="Ceritakan kebutuhan Anda secara singkat" class="isian-pesan mt-5" />

                    {{-- Honeypot anti-spam: tersembunyi dari pengunjung & pembaca layar; pesan tidak disimpan bila terisi. --}}
                    <div class="absolute -left-[9999px] size-px overflow-hidden" aria-hidden="true">
                        <label for="kontak-website">Jangan isi kolom ini</label>
                        <input type="text" id="kontak-website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="aksi-formulir">
                        <button type="submit" class="btn btn-besar">
                            Kirim Pesan
                            <x-web.ikon nama="kirim" class="size-[18px]" />
                        </button>
                        {{-- Wilayah role="status" selalu ada agar pesan sukses diumumkan pembaca layar. --}}
                        <div role="status" class="sm:order-1">
                            @if (session('kontak_terkirim'))
                                <div id="kotak-sukses" class="kotak-sukses">
                                    <x-web.ikon nama="centang" class="mt-px size-5 shrink-0" />
                                    <span>{{ session('kontak_terkirim') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Peta Leaflet dimuat malas (resources/js/web/peta.js); ilustrasi cadangan tampil sebelum/bila gagal. --}}
            <div id="wadah-peta" class="wadah-peta">
                <svg class="absolute inset-0 size-full mono:hidden" viewBox="0 0 400 320" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="400" height="320" fill="#eef2f7"/>
                    <path d="M-20 250 C 70 215 140 280 230 240 S 360 185 430 210 L 430 340 L -20 340 Z" fill="#dbeafe"/>
                    <rect x="22" y="26" width="96" height="64" rx="12" fill="#dcfce7"/>
                    <rect x="292" y="34" width="88" height="74" rx="12" fill="#dcfce7"/>
                    <rect x="214" y="120" width="64" height="40" rx="8" fill="#e2e8f0"/>
                    <rect x="96" y="128" width="56" height="44" rx="8" fill="#e2e8f0"/>
                    <rect x="300" y="132" width="70" height="38" rx="8" fill="#e2e8f0"/>
                    <g fill="none" stroke-linecap="round">
                        <g stroke="#dde3ec" stroke-width="17"><path d="M-20 116 L 420 84"/><path d="M196 -20 L 176 340"/></g>
                        <g stroke="#ffffff" stroke-width="12"><path d="M-20 116 L 420 84"/><path d="M196 -20 L 176 340"/></g>
                        <g stroke="#ffffff" stroke-width="5"><path d="M-20 192 L 420 162"/><path d="M70 -20 L 54 340"/><path d="M330 -20 L 312 340"/><path d="M110 100 L 128 330"/><path d="M196 214 L 420 260"/></g>
                    </g>
                </svg>
                <div class="pola-kisi pola-pudar-tengah absolute inset-0 hidden mono:block" aria-hidden="true"></div>
                <div class="peta-cadangan">
                    <span class="pin-peta" aria-hidden="true"><x-web.ikon nama="lokasi" :tebal="2.25" class="size-5" /></span>
                    <div class="kartu-pin">
                        <p class="nama-pin">{{ $identitas->nama_perusahaan }}</p>
                        <x-web.tautan-luar :href="$tautanPeta" class="btn btn-sedang" data-variant="outline">
                            Buka peta
                            <x-web.ikon nama="panah-keluar" class="size-4" />
                        </x-web.tautan-luar>
                    </div>
                </div>
                <div id="peta-lokasi" class="absolute inset-0" hidden role="region" aria-label="Peta lokasi kantor {{ $identitas->nama_perusahaan }}"
                     data-lat="{{ $peta['lat'] }}" data-lng="{{ $peta['lng'] }}" data-zoom="{{ $peta['zoom'] }}"
                     data-nama="{{ $identitas->nama_perusahaan }}" data-alamat="{{ $identitas->alamat }}"></div>
            </div>
        </div>
    </div>
</section>
