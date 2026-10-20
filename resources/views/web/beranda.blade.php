@extends('layouts.web')

@use('App\Enums\CompanyProfile\JenisModul')
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
        // Seksi modul aktif seurut Manajemen Modul; seksi tanpa data tidak dirender (koleksi modul nonaktif sudah kosong
        // dari controller). Nama seksi = nama partial web/beranda/* dan id anchor-nya.
        $isiSeksi = ['layanan' => $layanan, 'portofolio' => $portofolio, 'artikel' => $artikel, 'faq' => $faq];
        $seksi = collect($modul->urutanTampil())
            // Ajakan tanpa data, jadi selalu dirender selama aktif.
            ->filter(fn (JenisModul $jenis): bool => $jenis->punyaSeksi()
                && (! isset($isiSeksi[$jenis->value]) || $isiSeksi[$jenis->value]->isNotEmpty()))
            ->map(fn (JenisModul $jenis): string => $jenis->value)
            ->values()
            ->all();
        // Latar putih/redup selang-seling dihitung dari seksi yang tampil agar dua seksi berlatar sama tidak
        // bersebelahan; band ajakan tidak ikut dihitung.
        $nomorLatar = 0;
    @endphp

    @include('web.beranda.hero')
    @foreach ($seksi as $nama)
        @include('web.beranda.'.$nama, ['redup' => $nama !== 'ajakan' && $nomorLatar++ % 2 === 1])
    @endforeach
@endsection
