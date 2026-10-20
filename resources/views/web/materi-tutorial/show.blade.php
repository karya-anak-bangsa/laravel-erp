@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\Seo')
@use('App\Support\TeksHtml')

{{-- Judul materi saja terlalu umum di hasil pencarian (mis. "Menghapus Data dari Basis Data"), jadi diikuti judul tutorialnya. --}}
@section('judul', $materiTutorial->judul.' — '.$tutorial->judul)
@section('deskripsi', TeksHtml::potongDiAkhirKata(TeksHtml::ringkasan($materiTutorial->deskripsi), 160))
{{-- Dari slug tersimpan, bukan alamat yang diketik pengunjung. --}}
@section('kanonis', Seo::alamat(route('materi-tutorial.show', [$tutorial, $materiTutorial], false)))
{{-- Materi tanpa gambar sendiri: tautan yang dibagikan memakai sampul tutorialnya. --}}
@section('gambar-bagikan', $tutorial->gambar_url)

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::materiTutorial($tutorial, $materiTutorial)" />
@endpush

@section('konten')
    <article aria-labelledby="judul-materi">
        <x-web.kepala-halaman class="kepala-halaman-artikel" id-judul="judul-materi" :judul="$materiTutorial->judul"
            :jejak="['Beranda' => route('beranda'), 'Tutorial' => route('tutorial.index'), $tutorial->judul => route('tutorial.show', $tutorial)]">
            <x-slot:meta>
                <p class="lencana-artikel urutan-materi">Materi {{ $indeks + 1 }} dari {{ $daftarMateri->count() }}</p>
            </x-slot:meta>
        </x-web.kepala-halaman>

        <div class="isi-halaman">
            <div class="wadah tata-samping">
                <div>
                    <div class="isi-artikel isi-materi konten-html">{!! $materiTutorial->deskripsi !!}</div>

                    @if ($sebelumnya || $berikutnya)
                        {{-- Materi berikutnya tetap di kanan walau tanpa materi sebelumnya (materi pertama). --}}
                        <nav class="navigasi-materi" aria-label="Materi sebelumnya dan berikutnya">
                            @if ($sebelumnya)
                                <a href="{{ route('materi-tutorial.show', [$tutorial, $sebelumnya]) }}" rel="prev" class="tautan-navigasi-materi">
                                    <span class="label-navigasi-materi">Materi sebelumnya</span>
                                    <span class="judul-navigasi-materi">{{ $sebelumnya->judul }}</span>
                                </a>
                            @endif
                            @if ($berikutnya)
                                <a href="{{ route('materi-tutorial.show', [$tutorial, $berikutnya]) }}" rel="next" class="tautan-navigasi-materi tautan-materi-berikutnya">
                                    <span class="label-navigasi-materi">Materi berikutnya</span>
                                    <span class="judul-navigasi-materi">{{ $berikutnya->judul }}</span>
                                </a>
                            @endif
                        </nav>
                    @endif
                </div>

                @include('web.tutorial.samping', ['materiAktif' => $materiTutorial])
            </div>
        </div>
    </article>

    @include('web.beranda.ajakan')
@endsection
