{{--
    Tombol bagikan di akhir isi artikel (pilihan pemilik 2026-10-15): WhatsApp, Facebook, LinkedIn, dan salin tautan.
    Tombol ikon-saja (aturan ikon frontend) dengan nama untuk pembaca layar; tanpa paket. Tombol salin ditampilkan
    resources/js/web/bagikan.js karena tanpa JavaScript tidak berfungsi.
    Pakai: <x-web.bagikan :alamat="$alamatKanonis" :judul="$artikel->judul" />
--}}
@props(['alamat', 'judul'])

@php
    $tujuan = [
        'whatsapp' => ['WhatsApp', 'https://wa.me/?text='.rawurlencode($judul."\n".$alamat)],
        'facebook' => ['Facebook', 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($alamat)],
        'linkedin' => ['LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($alamat)],
    ];
@endphp

<div {{ $attributes->class('bagikan') }}>
    <p id="label-bagikan" class="label-bagikan">Bagikan artikel ini</p>
    <ul class="daftar-bagikan" aria-labelledby="label-bagikan">
        @foreach ($tujuan as $kode => [$nama, $href])
            <li>
                <x-web.tautan-luar :href="$href" class="btn tombol-bagikan" data-variant="outline" data-size="icon" data-tujuan="{{ $kode }}" title="Bagikan ke {{ $nama }}">
                    <x-web.ikon :nama="$kode" class="logo-merek" /><span class="sr-only">Bagikan ke {{ $nama }}</span>
                </x-web.tautan-luar>
            </li>
        @endforeach
        <li hidden>
            <button type="button" class="btn tombol-bagikan" data-variant="outline" data-size="icon" data-salin-tautan="{{ $alamat }}" title="Salin tautan">
                <x-web.ikon nama="tautan" class="ikon-salin" />
                <x-web.ikon nama="cek" class="ikon-tersalin" />
                <span class="sr-only">Salin tautan</span>
            </button>
        </li>
    </ul>
    {{-- Diisi bagikan.js setelah menyalin; role status agar diumumkan pembaca layar. --}}
    <p class="status-salin" role="status" data-status-salin></p>
</div>
