{{--
    Kartu Konsultasi gratis, kotak pertama kolom samping halaman artikel & portofolio. Slot promosi sementara yang hanya
    tampil mulai 1024px, karena di bawahnya langsung disusul band ajakan yang sama tujuannya.
    Pakai: @include('web.samping.konsultasi') di dalam <aside class="kolom-samping">.
--}}
<div class="ajakan-samping dark">
    <div class="ajakan-blob -top-20 -left-16 size-56 bg-(--warna-aksen)/40 mono:hidden" aria-hidden="true"></div>
    <div class="ajakan-blob -right-20 -bottom-24 size-64 bg-sky-500/25 mono:hidden" aria-hidden="true"></div>
    <div class="ajakan-titik mono:hidden" aria-hidden="true"></div>

    <h2 class="judul-ajakan-samping">Konsultasi gratis</h2>
    <p class="deskripsi-ajakan-samping">Diskusikan kebutuhan website, aplikasi, atau pelatihan IT tim Anda bersama kami, tanpa biaya.</p>
    {{-- Sama dengan band ajakan: WhatsApp, atau formulir di beranda bila link WhatsApp di Identitas kosong. --}}
    @if (filled($identitas->link_whatsapp))
        <x-web.tautan-luar :href="$identitas->link_whatsapp" class="btn btn-besar">
            <x-web.ikon nama="whatsapp" class="size-5" />
            Chat via WhatsApp
        </x-web.tautan-luar>
    @else
        <a href="{{ route('beranda') }}#kontak" class="btn btn-besar">Kirim Pesan</a>
    @endif
</div>
