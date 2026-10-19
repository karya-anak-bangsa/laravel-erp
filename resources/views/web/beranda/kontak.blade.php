<section id="kontak" class="seksi" aria-labelledby="judul-kontak">
    <div class="wadah">
        <x-web.kepala-seksi id="judul-kontak" label="Kontak" judul="Mari diskusikan kebutuhan Anda" rata="tengah">
            Kirim pesan melalui formulir di bawah ini, atau hubungi kami langsung lewat WhatsApp dan email.
        </x-web.kepala-seksi>

        {{-- Formulir selebar konten tanpa daftar kontak & peta (pilihan pemilik 2026-10-19): alamat, email, dan WhatsApp
             sudah ada di footer. Pesan masuk ke kotak masuk admin (Company Profile › Kontak Kami). --}}
        <div id="kirim-pesan" class="kartu-formulir">
            <h3 class="judul-formulir">Kirim pesan</h3>
            <p class="catatan-formulir">Isian bertanda <span class="tanda-wajib font-semibold">*</span> wajib diisi.</p>

            <form id="form-kontak" action="{{ route('kontak-kami.store') }}" method="post" class="form-kontak" novalidate>
                @csrf

                @error('formulir')
                    <div class="kotak-galat" role="alert" tabindex="-1">
                        <x-web.ikon nama="peringatan" class="mt-px size-5 shrink-0" />
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-web.isian id="kontak-nama" nama="nama" label="Nama" :wajib="true" maks="100" autocomplete="name" placeholder="Nama lengkap Anda" />
                    <x-web.isian id="kontak-email" nama="email" label="Email" jenis="email" :wajib="true" maks="150" autocomplete="email" placeholder="nama@email.com" />
                    <x-web.isian id="kontak-subjek" nama="subjek" label="Subjek" :wajib="true" maks="200" placeholder="Contoh: Website company profile" class="sm:col-span-2" />
                </div>
                <x-web.isian id="kontak-pesan" nama="pesan" label="Pesan" jenis="textarea" :wajib="true" maks="5000" placeholder="Ceritakan kebutuhan Anda secara singkat" class="mt-5" />

                {{-- Honeypot anti-spam: tersembunyi dari pengunjung & pembaca layar; pesan tidak disimpan bila terisi. --}}
                <div class="absolute -left-[9999px] size-px overflow-hidden" aria-hidden="true">
                    <label for="kontak-website">Jangan isi kolom ini</label>
                    <input type="text" id="kontak-website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="aksi-formulir">
                    <button type="submit" class="btn btn-besar">Kirim Pesan</button>
                    {{-- Wilayah role="status" selalu ada agar pesan sukses diumumkan pembaca layar. --}}
                    <div role="status" class="sm:order-1">
                        @if (session('kontak_terkirim'))
                            <div id="kotak-sukses" class="kotak-sukses" tabindex="-1">
                                <x-web.ikon nama="centang" class="mt-px size-5 shrink-0" />
                                <span>{{ session('kontak_terkirim') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
