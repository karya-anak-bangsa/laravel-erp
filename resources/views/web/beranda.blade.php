@extends('layouts.web')

@use('App\Support\TeksHtml')
@use('Illuminate\Support\Str')

@section('deskripsi', filled($identitas->meta_deskripsi) ? $identitas->meta_deskripsi : Str::limit(TeksHtml::polos($hero?->deskripsi), 160))

@php
    // Nomor label seksi (Monochrome: "01 / Layanan") mengikuti seksi yang benar-benar tampil; seksi tanpa data disembunyikan.
    $seksiTampil = array_keys(array_filter([
        'layanan' => $layanan->isNotEmpty(),
        'portofolio' => $portofolio->isNotEmpty(),
        'artikel' => $artikel->isNotEmpty(),
        'faq' => $faq->isNotEmpty(),
        'kontak' => true,
    ]));
    $nomorSeksi = fn (string $seksi): string => sprintf('%02d', (int) array_search($seksi, $seksiTampil, true) + 1);
@endphp

@section('konten')
    @include('web.beranda.hero')
    @includeWhen($layanan->isNotEmpty(), 'web.beranda.layanan')
    @includeWhen($portofolio->isNotEmpty(), 'web.beranda.portofolio')
    @include('web.beranda.ajakan')
    @includeWhen($artikel->isNotEmpty(), 'web.beranda.artikel')
    @includeWhen($faq->isNotEmpty(), 'web.beranda.faq')
    @include('web.beranda.kontak')
@endsection
