@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')
@use('App\Support\TeksHtml')

@php
    // Gambar utama lalu gambar tambahan sesuai urutan unggah; carousel hanya bila gambarnya lebih dari satu.
    $daftarGambar = $portofolio->gambarPortofolio->map(fn ($gambar) => $gambar->gambar_url)->prepend($portofolio->gambar_url);
@endphp

@section('judul', $portofolio->judul)
@section('deskripsi', TeksHtml::potongDiAkhirKata(TeksHtml::polos($portofolio->deskripsi), 160))
{{-- Dari slug tersimpan, bukan alamat yang diketik pengunjung (slug berhuruf besar pun menemukan portofolio ini). --}}
@section('kanonis', Seo::alamat(route('portofolio.show', $portofolio, false)))
@section('gambar-bagikan', $portofolio->gambar_url)

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::portofolio($portofolio)" />
@endpush

@section('konten')
    {{-- Susunan sama dengan detail artikel, termasuk kelas kepala, bingkai gambar, dan isinya; kategori berupa pita di tepi
         kanan bingkai gambar seperti detail artikel (pilihan pemilik 2026-10-18). Tanpa tanggal dan tombol bagikan. --}}
    <article aria-labelledby="judul-portofolio">
        <x-web.kepala-halaman class="kepala-halaman-artikel" id-judul="judul-portofolio" :judul="$portofolio->judul"
            :jejak="['Beranda' => route('beranda'), 'Portofolio' => route('portofolio.index')]" />

        <div class="isi-halaman">
            <div class="wadah tata-samping">
                <div>
                    {{-- Di atas lipatan, jadi gambar pertama tidak lazy. --}}
                    <div class="bingkai-artikel">
                        <span class="pita-kategori">{{ $portofolio->kategori }}</span>
                        @if ($daftarGambar->count() > 1)
                            {{-- Carousel (resources/js/web/carousel.js): panah & titik di atas gambar, panah di slide pertama dan
                                 terakhir disembunyikan (varian V1, pilihan pemilik 2026-10-18). --}}
                            <div class="carousel" data-carousel role="region" aria-roledescription="carousel" aria-label="Gambar portofolio">
                                <div class="carousel-layar">
                                    <div class="carousel-jalur" data-carousel-jalur>
                                        @foreach ($daftarGambar as $url)
                                            <div class="carousel-slide" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} dari {{ $loop->count }}">
                                                <img src="{{ $url }}" width="800" height="600" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async" alt="{{ $portofolio->judul }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn carousel-panah carousel-sebelumnya" data-variant="outline" data-size="icon" data-carousel-sebelumnya aria-label="Gambar sebelumnya" hidden>
                                        <x-web.ikon nama="chevron-kiri" />
                                    </button>
                                    <button type="button" class="btn carousel-panah carousel-berikutnya" data-variant="outline" data-size="icon" data-carousel-berikutnya aria-label="Gambar berikutnya">
                                        <x-web.ikon nama="chevron-kanan" />
                                    </button>
                                    <div class="carousel-titik">
                                        @foreach ($daftarGambar as $url)
                                            <button type="button" data-carousel-titik aria-label="Gambar {{ $loop->iteration }}" @if ($loop->first) aria-current="true" @endif></button>
                                        @endforeach
                                    </div>
                                </div>
                                {{-- Miniatur menggantikan titik mulai 1024px (laptop & PC, pilihan pemilik 2026-10-18). Di tablet & ponsel
                                     tersembunyi, dan karena lazy gambarnya tidak ikut diunduh. --}}
                                <div class="carousel-miniatur">
                                    @foreach ($daftarGambar as $url)
                                        <button type="button" data-carousel-miniatur aria-label="Gambar {{ $loop->iteration }}" @if ($loop->first) aria-current="true" @endif>
                                            <img src="{{ $url }}" width="800" height="600" loading="lazy" decoding="async" alt="">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- Gambar utuh (rasio asli) dalam bingkai. --}}
                            <img src="{{ $portofolio->gambar_url }}" width="800" height="600" fetchpriority="high" decoding="async" alt="{{ $portofolio->judul }}">
                        @endif
                    </div>
                    <div class="isi-artikel konten-html">{!! $portofolio->deskripsi !!}</div>
                </div>

                @include('web.portofolio.samping', ['portofolioLainnya' => $portofolioLainnya])
            </div>
        </div>
    </article>

    @include('web.beranda.ajakan')
@endsection
