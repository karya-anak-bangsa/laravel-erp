<section id="ajakan" class="seksi-ajakan dark" aria-labelledby="judul-ajakan" data-sembunyikan-wa>
    <div class="pola-kisi pola-pudar pointer-events-none absolute inset-0 hidden opacity-70 mono:block" aria-hidden="true"></div>
    <div class="wadah">
        <div class="kotak-ajakan">
            <div class="ajakan-blob -top-28 -left-20 size-96 bg-(--warna-aksen)/40 mono:hidden" aria-hidden="true"></div>
            <div class="ajakan-blob -right-24 -bottom-40 size-[26rem] bg-sky-500/25 mono:hidden" aria-hidden="true"></div>
            <div class="ajakan-titik mono:hidden" aria-hidden="true"></div>

            <div class="max-w-2xl">
                <p class="label-ajakan">Mulai bersama kami</p>
                <h2 id="judul-ajakan" class="judul-ajakan">Punya ide proyek atau rencana pelatihan untuk tim Anda?</h2>
                <p class="deskripsi-ajakan">Ceritakan kebutuhan Anda, kami bantu menyusun solusi yang sesuai.</p>
            </div>
            <div class="aksi-ajakan">
                @if (filled($identitas->link_whatsapp))
                    <x-web.tautan-luar :href="$identitas->link_whatsapp" class="btn btn-besar">
                        <x-web.ikon nama="whatsapp" class="size-5" />
                        Chat via WhatsApp
                    </x-web.tautan-luar>
                @endif
                <a href="#kontak" class="btn btn-besar" data-variant="outline">Kirim Pesan</a>
            </div>
        </div>
    </div>
</section>
