@if (filled($identitas->link_whatsapp))
    {{-- Disembunyikan resources/js/web/tombol-wa.js saat ajakan kontak lain terlihat. --}}
    <a id="tombol-wa" href="{{ $identitas->link_whatsapp }}" target="_blank" rel="noopener" class="tombol-wa" aria-label="Chat WhatsApp dengan {{ $identitas->nama_perusahaan }} (membuka tab baru)">
        <x-web.ikon nama="whatsapp" class="ikon-wa" />
        <span class="label-wa" aria-hidden="true">WhatsApp</span>
    </a>
@endif
