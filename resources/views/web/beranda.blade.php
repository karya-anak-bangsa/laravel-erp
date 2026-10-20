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
    @include('web.beranda.hero')
    @includeWhen($layanan->isNotEmpty(), 'web.beranda.layanan')
    @includeWhen($portofolio->isNotEmpty(), 'web.beranda.portofolio')
    @include('web.beranda.ajakan')
    @includeWhen($artikel->isNotEmpty(), 'web.beranda.artikel')
    @includeWhen($faq->isNotEmpty(), 'web.beranda.faq')
@endsection
