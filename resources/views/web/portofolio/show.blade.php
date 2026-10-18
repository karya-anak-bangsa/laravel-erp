@extends('layouts.web')

@use('App\Support\AksenWarna')
@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')
@use('App\Support\TeksHtml')

@section('judul', $portofolio->judul)
@section('deskripsi', TeksHtml::potongDiAkhirKata(TeksHtml::polos($portofolio->deskripsi), 160))
{{-- Dari slug tersimpan, bukan alamat yang diketik pengunjung (slug berhuruf besar pun menemukan portofolio ini). --}}
@section('kanonis', Seo::alamat(route('portofolio.show', $portofolio, false)))
@section('gambar-bagikan', $portofolio->gambar_url)

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::portofolio($portofolio)" />
@endpush

@section('konten')
    {{-- Susunan sama dengan detail artikel, termasuk kelas kepala, bingkai gambar, dan isinya; kategori berupa lencana
         seperti di kartu portofolio, di atas judul. Tanpa tanggal dan tombol bagikan. --}}
    <article aria-labelledby="judul-portofolio">
        <x-web.kepala-halaman class="kepala-halaman-artikel" id-judul="judul-portofolio" :judul="$portofolio->judul"
            :jejak="['Beranda' => route('beranda'), 'Portofolio' => route('portofolio.index')]">
            <x-slot:meta>
                <span class="lencana-kategori {{ AksenWarna::untuk($portofolio->kategori) }}"><span class="titik" aria-hidden="true"></span>{{ $portofolio->kategori }}</span>
            </x-slot:meta>
        </x-web.kepala-halaman>

        <div class="isi-halaman">
            <div class="wadah tata-samping">
                <div>
                    {{-- Gambar utuh (rasio asli) dalam bingkai; di atas lipatan, jadi tidak lazy. --}}
                    <div class="bingkai-artikel">
                        <img src="{{ $portofolio->gambar_url }}" width="800" height="600" fetchpriority="high" decoding="async" alt="{{ $portofolio->judul }}">
                    </div>
                    <div class="isi-artikel konten-html">{!! $portofolio->deskripsi !!}</div>
                </div>

                @include('web.portofolio.samping', ['portofolioLainnya' => $portofolioLainnya])
            </div>
        </div>
    </article>

    @include('web.beranda.ajakan')
@endsection
