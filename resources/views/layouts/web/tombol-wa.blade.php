@if (filled($identitas->link_whatsapp))
    {{-- Selalu tampil di pojok kanan bawah (pilihan pemilik 2026-10-11); footer diberi ruang bawah di komponen.css. --}}
    <a id="tombol-wa" href="{{ $identitas->link_whatsapp }}" target="_blank" rel="noopener" class="tombol-wa" aria-label="Chat WhatsApp dengan {{ $identitas->nama_perusahaan }} (membuka tab baru)">
        <x-web.ikon nama="whatsapp" class="ikon-wa" />
        <span class="label-wa" aria-hidden="true">WhatsApp</span>
    </a>
@endif
