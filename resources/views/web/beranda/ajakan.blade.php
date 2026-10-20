{{-- Band ajakan di beranda dan halaman artikel, portofolio, & tutorial; disembunyikan bila modul Ajakan nonaktif. --}}
@if ($modul->tampil(App\Enums\CompanyProfile\JenisModul::Ajakan))
    <section id="ajakan" class="seksi-ajakan dark" aria-labelledby="judul-ajakan">
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
                {{-- Cukup satu ajakan WhatsApp (pilihan pemilik 2026-10-11); disembunyikan bila link WhatsApp di Identitas kosong. --}}
                @if (filled($identitas->link_whatsapp))
                    <div class="aksi-ajakan">
                        <x-web.tautan-luar :href="$identitas->link_whatsapp" class="btn btn-besar">
                            <x-web.ikon nama="whatsapp" class="size-5" />
                            Chat via WhatsApp
                        </x-web.tautan-luar>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
