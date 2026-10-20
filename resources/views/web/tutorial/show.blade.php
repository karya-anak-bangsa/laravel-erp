@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')
@use('App\Support\TeksHtml')

@section('judul', $tutorial->judul)
@section('deskripsi', TeksHtml::potongDiAkhirKata(TeksHtml::polos($tutorial->deskripsi), 160))
{{-- Dari slug tersimpan, bukan alamat yang diketik pengunjung (slug berhuruf besar pun menemukan tutorial ini). --}}
@section('kanonis', Seo::alamat(route('tutorial.show', $tutorial, false)))
@section('gambar-bagikan', $tutorial->gambar_url)

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::tutorial($tutorial)" />
@endpush

@section('konten')
    {{-- Susunan sama dengan detail portofolio (kelas detail artikel dipakai bersama): sampul berbingkai dengan pita
         kategori, pengantar, lalu daftar materi dan tombol ke materi pertama. --}}
    <article aria-labelledby="judul-tutorial">
        <x-web.kepala-halaman class="kepala-halaman-artikel" id-judul="judul-tutorial" :judul="$tutorial->judul"
            :jejak="['Beranda' => route('beranda'), 'Tutorial' => route('tutorial.index')]" />

        <div class="isi-halaman">
            <div class="wadah tata-samping">
                <div>
                    {{-- Di atas lipatan, jadi tidak lazy. --}}
                    <div class="bingkai-artikel">
                        <span class="pita-kategori">{{ $tutorial->kategoriTutorial->nama_kategori }}</span>
                        <img src="{{ $tutorial->gambar_url }}" width="800" height="600" fetchpriority="high" decoding="async" alt="{{ $tutorial->judul }}">
                    </div>
                    <div class="isi-artikel konten-html">{!! $tutorial->deskripsi !!}</div>

                    <section class="seksi-daftar-materi" aria-labelledby="judul-daftar-materi">
                        <h2 id="judul-daftar-materi" class="judul-daftar-materi">Daftar materi</h2>
                        @include('web.tutorial.daftar-materi', ['kelas' => 'daftar-materi'])
                        <a href="{{ route('materi-tutorial.show', [$tutorial, $daftarMateri->first()]) }}" class="btn btn-besar tombol-mulai">Mulai belajar</a>
                    </section>
                </div>

                @include('web.tutorial.samping')
            </div>
        </div>
    </article>

    @include('web.beranda.ajakan')
@endsection
