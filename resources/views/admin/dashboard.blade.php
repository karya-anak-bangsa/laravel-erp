@extends('layouts.admin')

@section('content')
    <x-admin.page-header title="Dashboard" />

    {{-- Ringkasan Company Profile dari tabel yang sudah ada (App\Services\CompanyProfile\RingkasanService). --}}
    <div class="row col-3">
        <x-admin.statistik label="Artikel terbit" :nilai="$statistik['artikel']['terbit']" ikon="newspaper" warna="teal"
            :keterangan="$statistik['artikel']['draf'].' draf · '.$statistik['artikel']['kategori'].' kategori'"
            :href="route('admin.artikel.index')" />
        <x-admin.statistik label="Portofolio" :nilai="$statistik['portofolio']['jumlah']" ikon="briefcase" warna="blue"
            :keterangan="$statistik['portofolio']['kategori'].' kategori'"
            :href="route('admin.portofolio.index')" />
        <x-admin.statistik label="Tutorial terbit" :nilai="$statistik['tutorial']['terbit']" ikon="graduation-cap" warna="purple"
            :keterangan="$statistik['tutorial']['draf'].' draf · '.$statistik['tutorial']['materi'].' materi'"
            :href="route('admin.tutorial.index')" />
        <x-admin.statistik label="Layanan" :nilai="$statistik['layanan']" ikon="handshake" warna="green"
            keterangan="Tampil di beranda" :href="route('admin.layanan.index')" />
        <x-admin.statistik label="FAQ" :nilai="$statistik['faq']" ikon="circle-question" warna="yellow"
            keterangan="Tampil di beranda" :href="route('admin.faq.index')" />
        <x-admin.statistik label="Hero" :nilai="$statistik['hero']['jumlah']" ikon="panorama" warna="red"
            :keterangan="$statistik['hero']['aktif'].' aktif di beranda'" :href="route('admin.hero.index')" />
    </div>

    <x-admin.card title="Terakhir diperbarui" subtitle="Konten yang terakhir disimpan, terbaru lebih dulu." :flush="true">
        @if ($terakhirDiperbarui->isEmpty())
            <x-admin.empty-state title="Belum ada konten" description="Konten yang ditambahkan atau diubah akan tampil di sini." />
        @else
            <ul class="daftar-ringkas">
                @foreach ($terakhirDiperbarui as $item)
                    <li>
                        <div class="isi-ringkas">
                            <a href="{{ $item['url'] }}" class="judul-ringkas" title="{{ $item['judul'] }}">{{ Str::limit($item['judul'], 100) }}</a>
                            <div class="keterangan-ringkas">{{ $item['modul'] }} · {{ $item['diperbarui']->translatedFormat('d F Y, H:i') }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-admin.card>
@endsection
