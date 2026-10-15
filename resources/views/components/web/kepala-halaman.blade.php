{{--
    Kepala halaman publik selain beranda: remah roti, slot meta (opsional, mis. kategori & tanggal artikel), judul h1,
    dan deskripsi (slot). Latar meniru hero; Full Color rata tengah, Monochrome rata kiri, sama dengan kepala seksi.
    Pakai: <x-web.kepala-halaman judul="…" :jejak="['Beranda' => route('beranda'), 'Artikel' => null]">Deskripsi</x-web.kepala-halaman>
--}}
@props(['judul', 'jejak', 'idJudul' => null, 'meta' => null])

<header {{ $attributes->class('kepala-halaman') }}>
    <div class="pola-kisi pola-pudar pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>
    <div class="wadah">
        <div class="kepala-halaman-isi">
            <x-web.remah-roti :jejak="$jejak" />
            {{ $meta }}
            <h1 @if ($idJudul) id="{{ $idJudul }}" @endif class="judul-seksi">{{ $judul }}</h1>
            @if ($slot->isNotEmpty())
                <p class="deskripsi-seksi">{{ $slot }}</p>
            @endif
        </div>
    </div>
</header>
