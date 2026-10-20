@extends('layouts.web')

@use('App\Support\DataTerstruktur')
@use('App\Support\TeksHtml')
@use('Illuminate\Support\Str')

{{-- Tanpa section judul: judul tab beranda = Judul Website. Gambar pratinjau tautan = hero aktif (SeoComposer). --}}
@section('deskripsi', filled($identitas->meta_deskripsi) ? $identitas->meta_deskripsi : Str::limit(TeksHtml::polos($hero?->deskripsi), 160))

@push('kepala')
    <x-web.data-terstruktur :data="DataTerstruktur::beranda($identitas)" />
@endpush

@section('konten')
    @php
        // Seksi berdata saja (koleksi modul nonaktif sudah kosong dari controller). Latar putih/redup selang-seling
        // dihitung dari seksi yang tampil agar dua seksi berlatar sama tidak bersebelahan; band ajakan tidak dihitung.
        $seksi = array_keys(array_filter([
            'layanan' => $layanan->isNotEmpty(),
            'portofolio' => $portofolio->isNotEmpty(),
            'artikel' => $artikel->isNotEmpty(),
            'faq' => $faq->isNotEmpty(),
        ]));
        $redup = fn (string $nama): bool => array_search($nama, $seksi, true) % 2 === 1;
    @endphp

    @include('web.beranda.hero')
    @includeWhen(in_array('layanan', $seksi, true), 'web.beranda.layanan', ['redup' => $redup('layanan')])
    @includeWhen(in_array('portofolio', $seksi, true), 'web.beranda.portofolio', ['redup' => $redup('portofolio')])
    @include('web.beranda.ajakan')
    @includeWhen(in_array('artikel', $seksi, true), 'web.beranda.artikel', ['redup' => $redup('artikel')])
    @includeWhen(in_array('faq', $seksi, true), 'web.beranda.faq', ['redup' => $redup('faq')])
@endsection
