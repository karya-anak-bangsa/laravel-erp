@extends('layouts.web')

@use('App\Support\TeksHtml')

@section('judul', $artikel->judul.' — '.$identitas->judul_website)
@section('deskripsi', TeksHtml::potongDiAkhirKata(TeksHtml::ringkasan($artikel->deskripsi), 160))

@section('konten')
    <article aria-labelledby="judul-artikel">
        <x-web.kepala-halaman class="kepala-halaman-artikel" id-judul="judul-artikel" :judul="$artikel->judul"
            :jejak="['Beranda' => route('beranda'), 'Artikel' => route('artikel.index')]">
            <x-slot:meta>
                <div class="meta-artikel">
                    @if ($artikel->kategoriArtikel)
                        <span class="lencana-artikel">{{ $artikel->kategoriArtikel->nama_kategori }}</span>
                    @endif
                    <time class="tanggal-artikel" datetime="{{ $artikel->tanggal->toDateString() }}">Diterbitkan <span class="whitespace-nowrap">{{ $artikel->tanggal->translatedFormat('j F Y') }}</span></time>
                </div>
            </x-slot>
        </x-web.kepala-halaman>

        <div class="wadah badan-artikel">
            {{-- Gambar utuh (rasio asli) dalam bingkai seperti gambar hero; di atas lipatan, jadi tidak lazy. --}}
            <div class="bingkai-artikel">
                <img src="{{ $artikel->gambar_url }}" width="800" height="600" fetchpriority="high" decoding="async" alt="{{ $artikel->judul }}">
            </div>
            <div class="isi-artikel konten-html">{!! $artikel->deskripsi !!}</div>
        </div>
    </article>

    @if ($artikelLain->isNotEmpty())
        <section class="seksi seksi-redup" aria-labelledby="judul-artikel-lain">
            <div class="wadah">
                <div class="kepala-artikel">
                    <x-web.kepala-seksi id="judul-artikel-lain" label="Baca juga" judul="Artikel lainnya" rata="tengah" />
                    <a href="{{ route('artikel.index') }}" class="btn btn-sedang" data-variant="outline">Lihat semua artikel</a>
                </div>

                <div class="grid-artikel">
                    @foreach ($artikelLain as $item)
                        <x-web.kartu-artikel :artikel="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('web.beranda.ajakan')
@endsection
